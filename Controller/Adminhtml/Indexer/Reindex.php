<?php
/**
 * @license GPL-3.0-only
 */
declare(strict_types=1);

namespace MB\AdminReindex\Controller\Adminhtml\Indexer;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;

class Reindex extends AbstractReindex implements HttpPostActionInterface
{
    public function execute(): ResultInterface
    {
        $indexerIds = $this->getRequest()->getParam('indexer_ids', []);
        if (is_string($indexerIds)) {
            $indexerIds = explode(',', $indexerIds);
        }

        if (!is_array($indexerIds) || $indexerIds === []) {
            $this->messageManager->addErrorMessage(__('Please select indexers.'));
            return $this->resultRedirectFactory->create()->setPath('indexer/indexer/list');
        }

        return $this->reindex($indexerIds);
    }
}
