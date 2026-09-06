<?php
/**
 * @license GPL-3.0-only
 */
declare(strict_types=1);

namespace MB\AdminReindex\Model\MassAction;

use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface;
use Magento\Framework\AuthorizationInterface;

class ReindexVisibility implements VisibilityCheckerInterface
{
    private const ACL_RESOURCE = 'Magento_Indexer::index';

    public function __construct(
        private readonly AuthorizationInterface $authorization
    ) {
    }

    public function isVisible(): bool
    {
        return $this->authorization->isAllowed(self::ACL_RESOURCE);
    }
}
