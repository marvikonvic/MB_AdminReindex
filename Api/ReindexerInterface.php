<?php
/**
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace MB\AdminReindex\Api;

use MB\AdminReindex\Api\Data\ReindexResultInterface;

interface ReindexerInterface
{
    /**
     * Run a full reindex for the supplied indexer IDs and their required related indexers.
     *
     * @param string[] $indexerIds
     */
    public function execute(array $indexerIds): ReindexResultInterface;
}
