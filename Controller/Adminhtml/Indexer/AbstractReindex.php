<?php
/**
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace MB\AdminReindex\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use MB\AdminReindex\Api\ReindexerInterface;

abstract class AbstractReindex extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Indexer::index';

    public function __construct(
        Context $context,
        private readonly ReindexerInterface $reindexer
    ) {
        parent::__construct($context);
    }

    /**
     * @param string[] $indexerIds
     */
    protected function reindex(array $indexerIds): ResultInterface
    {
        $result = $this->reindexer->execute($indexerIds);
        $successfulIds = $result->getSuccessfulIds();
        $skippedIds = $result->getSkippedIds();
        $failedIds = $result->getFailedIds();

        if ($successfulIds !== []) {
            $this->messageManager->addSuccessMessage(
                __('%1 indexer(s) were reindexed successfully.', count($successfulIds))
            );
        }

        if ($skippedIds !== []) {
            $this->messageManager->addWarningMessage(
                __(
                    'The following indexer(s) are already running and were skipped: %1',
                    implode(', ', $skippedIds)
                )
            );
        }

        if ($failedIds !== []) {
            $this->messageManager->addErrorMessage(
                __(
                    'The following indexer(s) could not be reindexed: %1. Check the logs for details.',
                    implode(', ', $failedIds)
                )
            );
        }

        return $this->resultRedirectFactory->create()->setPath('indexer/indexer/list');
    }
}
