<?php

namespace Inpsyde\Lib\PayPal\Handler;

/**
 * Interface IPayPalHandler
 *
 * @package Inpsyde\Lib\PayPal\Handler
 */
interface IPayPalHandler
{
    /**
     *
     * @param \Paypal\Core\PayPalHttpConfig $httpConfig
     * @param string $request
     * @param mixed $options
     * @return mixed
     */
    public function handle($httpConfig, $request, $options);
}
