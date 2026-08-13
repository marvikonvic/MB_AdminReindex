<?php
/**
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace MB\AdminReindex\Api\Data;

interface ReindexResultInterface
{
    /**
     * @return string[]
     */
    public function getSuccessfulIds(): array;

    /**
     * @return string[]
     */
    public function getSkippedIds(): array;

    /**
     * @return string[]
     */
    public function getFailedIds(): array;
}
