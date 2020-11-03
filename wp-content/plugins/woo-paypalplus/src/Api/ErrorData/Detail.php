<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Class Detail
 * @package WCPayPalPlus\Api\ErrorData
 */
class Detail
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $issue;

    /**
     * Detail constructor.
     * @param string $field
     * @param string $issue
     */
    public function __construct($field, $issue)
    {
        $this->field = $field;
        $this->issue = $issue;
    }

    /**
     * @return string
     */
    public function field()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function issue()
    {
        return $this->issue;
    }
}
