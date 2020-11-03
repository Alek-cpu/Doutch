<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Class PayPalErrorData
 * @package WCPayPalPlus\Api
 */
final class PayPalError implements Error
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $details;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $debugId;

    /**
     * PayPalErrorData constructor.
     * @param string $code
     * @param Detail[] $details
     * @param string $message
     * @param string $debugId
     */
    public function __construct($code, $details, $message, $debugId)
    {
        assert(is_string($code));
        assert(is_array($details));
        assert(is_string($message));
        assert(is_string($debugId));

        $this->code = $code;
        $this->details = $details;
        $this->message = $message;
        $this->debugId = $debugId;
    }

    /**
     * @inheritdoc
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function details()
    {
        return $this->details;
    }

    /**
     * @inheritdoc
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function debugId()
    {
        return $this->debugId;
    }
}
