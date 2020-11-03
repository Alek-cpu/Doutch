<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api;

/**
 * Class CredentialValidatorResponse
 * @package WCPayPalPlus\Api
 */
class CredentialValidationResponse
{
    /**
     * @var bool
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * CredentialValidatorResponse constructor.
     * @param bool $status
     * @param string $message
     */
    public function __construct($status, $message)
    {
        assert(is_bool($status));
        assert(is_string($message));

        $this->status = $status;
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isValidStatus()
    {
        return $this->status === true;
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
