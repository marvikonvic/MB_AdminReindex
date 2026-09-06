<?php
/**
 * @license GPL-3.0-only
 */
declare(strict_types=1);

namespace MB\AdminReindex\Model;

use Magento\Framework\Indexer\Config\DependencyInfoProviderInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\Processor\MakeSharedIndexValid;
use MB\AdminReindex\Api\Data\ReindexResultInterface;
use MB\AdminReindex\Api\ReindexerInterface;
use Psr\Log\LoggerInterface;

class Reindexer implements ReindexerInterface
{
    public function __construct(
        private readonly IndexerRegistry $indexerRegistry,
        private readonly ConfigInterface $indexerConfig,
        private readonly DependencyInfoProviderInterface $dependencyInfoProvider,
        private readonly MakeSharedIndexValid $makeSharedIndexValid,
        private readonly ReindexResultFactory $reindexResultFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(array $indexerIds): ReindexResultInterface
    {
        $successfulIds = [];
        $skippedIds = [];
        $failedIds = [];
        $completedSharedIndexes = [];

        foreach ($this->getIndexerIdsToRun($indexerIds) as $indexerId) {
            try {
                $indexer = $this->indexerRegistry->get($indexerId);
                if ($indexer->isWorking()) {
                    $skippedIds[] = $indexerId;
                    continue;
                }

                $sharedIndex = $this->getSharedIndex($indexerId);
                if ($sharedIndex === null || !in_array($sharedIndex, $completedSharedIndexes, true)) {
                    $indexer->reindexAll();
                    if ($sharedIndex !== null && $this->makeSharedIndexValid->execute($sharedIndex)) {
                        $completedSharedIndexes[] = $sharedIndex;
                    }
                }

                $successfulIds[] = $indexerId;
            } catch (\Throwable $exception) {
                $failedIds[] = $indexerId;
                $this->logger->critical(
                    'Admin reindex failed for indexer "{indexer_id}".',
                    [
                        'indexer_id' => $indexerId,
                        'exception' => $exception,
                    ]
                );
            }
        }

        return $this->reindexResultFactory->create([
            'successfulIds' => $successfulIds,
            'skippedIds' => $skippedIds,
            'failedIds' => $failedIds,
        ]);
    }

    /**
     * Return requested indexers, invalid prerequisites, and dependent indexers in configuration order.
     *
     * @param mixed[] $indexerIds
     * @return string[]
     */
    private function getIndexerIdsToRun(array $indexerIds): array
    {
        $requestedIds = $this->normalizeIds($indexerIds);
        $configuredIds = array_keys($this->indexerConfig->getIndexers());
        $configuredLookup = array_fill_keys($configuredIds, true);
        $requestedLookup = array_fill_keys($requestedIds, true);
        $knownRequestedIds = array_values(array_intersect($requestedIds, $configuredIds));

        if (array_diff($configuredIds, $knownRequestedIds) === []) {
            return array_merge($configuredIds, array_values(array_diff($requestedIds, $configuredIds)));
        }

        $idsToRun = array_intersect_key($requestedLookup, $configuredLookup);
        $prerequisiteIds = [];
        $dependentIds = [];
        $visitedPrerequisites = [];
        $visitedDependents = [];

        foreach ($knownRequestedIds as $indexerId) {
            foreach ($this->getPrerequisiteIds($indexerId, $visitedPrerequisites) as $prerequisiteId) {
                if ($this->isInvalid($prerequisiteId)) {
                    $prerequisiteIds[$prerequisiteId] = true;
                }
            }

            foreach ($this->getDependentIds($indexerId, $visitedDependents) as $dependentId) {
                $dependentIds[$dependentId] = true;
            }
        }

        $idsToRun += $prerequisiteIds + $dependentIds;
        $orderedIds = [];
        foreach ($configuredIds as $configuredId) {
            if (isset($idsToRun[$configuredId])) {
                $orderedIds[] = $configuredId;
                unset($idsToRun[$configuredId]);
            }
        }

        return array_merge(
            $orderedIds,
            array_keys($idsToRun),
            array_values(array_diff($requestedIds, $configuredIds))
        );
    }

    /**
     * @param array<string, bool> $visited
     * @return string[]
     */
    private function getPrerequisiteIds(string $indexerId, array &$visited): array
    {
        if (isset($visited[$indexerId])) {
            return [];
        }

        $visited[$indexerId] = true;
        $prerequisiteIds = [];
        foreach ($this->dependencyInfoProvider->getIndexerIdsToRunBefore($indexerId) as $prerequisiteId) {
            if (isset($visited[$prerequisiteId])) {
                continue;
            }

            $prerequisiteIds[] = $prerequisiteId;
            $prerequisiteIds = array_merge(
                $prerequisiteIds,
                $this->getPrerequisiteIds($prerequisiteId, $visited)
            );
        }

        return array_values(array_unique($prerequisiteIds));
    }

    /**
     * @param array<string, bool> $visited
     * @return string[]
     */
    private function getDependentIds(string $indexerId, array &$visited): array
    {
        if (isset($visited[$indexerId])) {
            return [];
        }

        $visited[$indexerId] = true;
        $dependentIds = [];
        foreach ($this->dependencyInfoProvider->getIndexerIdsToRunAfter($indexerId) as $dependentId) {
            if (isset($visited[$dependentId])) {
                continue;
            }

            $dependentIds[] = $dependentId;
            $dependentIds = array_merge(
                $dependentIds,
                $this->getDependentIds($dependentId, $visited)
            );
        }

        return array_values(array_unique($dependentIds));
    }

    private function isInvalid(string $indexerId): bool
    {
        try {
            return $this->indexerRegistry->get($indexerId)->isInvalid();
        } catch (\Throwable) {
            return true;
        }
    }

    private function getSharedIndex(string $indexerId): ?string
    {
        $sharedIndex = $this->indexerConfig->getIndexer($indexerId)['shared_index'] ?? null;

        return is_string($sharedIndex) && $sharedIndex !== '' ? $sharedIndex : null;
    }

    /**
     * @param mixed[] $indexerIds
     * @return string[]
     */
    private function normalizeIds(array $indexerIds): array
    {
        $normalized = [];
        foreach ($indexerIds as $indexerId) {
            if (!is_scalar($indexerId)) {
                continue;
            }

            $indexerId = trim((string) $indexerId);
            if ($indexerId !== '') {
                $normalized[$indexerId] = $indexerId;
            }
        }

        return array_values($normalized);
    }
}
