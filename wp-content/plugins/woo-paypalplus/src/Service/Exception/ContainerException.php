<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Service\Exception;

/**
 * Exception base class for all exceptions thrown by the container.
 *
 * This is necessary to be able to catch all exceptions thrown in the module in one go.
 * Moreover, compliance with PSR-11 would be easier, with pretty much no code necessary.
 */
class ContainerException extends \Exception
{

}
