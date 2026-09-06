<?php
/**
 * @license GPL-3.0-only
 */
declare(strict_types=1);

namespace MB\AdminReindex\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Indexer\ConfigInterface;
use MB\AdminReindex\Api\ReindexerInterface;

class ReindexAll extends AbstractReindex implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        ReindexerInterface $reindexer,
        private readonly ConfigInterface $indexerConfig
    ) {
        parent::__construct($context, $reindexer);
    }

    public function execute(): ResultInterface
    {
        return $this->reindex(array_keys($this->indexerConfig->getIndexers()));
    }
}
