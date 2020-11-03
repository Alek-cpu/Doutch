<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Log;

use Inpsyde\Lib\PayPal\Core\PayPalConfigManager;
use Inpsyde\Lib\PayPal\Log\PayPalLogFactory;
use Inpsyde\Lib\Psr\Log\LoggerInterface;

/**
 * Class PayPalSdkLogFactory
 * @package WCPayPalPlus\Log
 */
class PayPalSdkLogFactory implements PayPalLogFactory
{

    /**
     * Returns logger instance implementing LoggerInterface.
     *
     * @param string $className
     *
     * @return LoggerInterface instance of logger object implementing LoggerInterface
     */
    public function getLogger($className)
    {
        $config = PayPalConfigManager::getInstance()->getConfigHashmap();
        $loggingLevel = isset($config['log.LogLevel']) ? \strtolower($config['log.LogLevel']) : \WC_Log_Levels::INFO;

        $logger = new WcPsrLoggerAdapter(\wc_get_logger(), $loggingLevel);
        $logger->setName($className);

        return $logger;
    }
}
