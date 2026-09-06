<?php
/**
 * @license GPL-3.0-only
 */
declare(strict_types=1);

namespace MB\AdminReindex\Model;

use MB\AdminReindex\Api\Data\ReindexResultInterface;

class ReindexResult implements ReindexResultInterface
{
    /**
     * @param string[] $successfulIds
     * @param string[] $skippedIds
     * @param string[] $failedIds
     */
    public function __construct(
        private readonly array $successfulIds = [],
        private readonly array $skippedIds = [],
        private readonly array $failedIds = []
    ) {
    }

    public function getSuccessfulIds(): array
    {
        return $this->successfulIds;
    }

    public function getSkippedIds(): array
    {
        return $this->skippedIds;
    }

    public function getFailedIds(): array
    {
        return $this->failedIds;
    }
}
