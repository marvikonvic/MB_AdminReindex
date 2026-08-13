<?php
/**
 * @license OSL-3.0
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'MB_AdminReindex',
    __DIR__
);
