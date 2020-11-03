<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Admin\Notice;

/**
 * Class Notice
 *
 * @package WCPayPalPlus\Admin
 */
class Notice implements Noticeable
{
    /**
     * @var
     */
    private $type;
    /**
     * @var
     */
    private $message;
    /**
     * @var
     */
    private $isDismissable;
    /**
     * @var
     */
    private $id;

    /**
     * Notice constructor.
     *
     * @param $type
     * @param $message
     * @param $isDismissable
     * @param $id
     */
    public function __construct($type, $message, $isDismissable, $id)
    {
        assert(is_string($type));
        assert(is_string($message));
        assert(is_bool($isDismissable));
        assert(is_string($id));

        $this->type = $type;
        $this->message = $message;
        $this->isDismissable = $isDismissable;
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function isDismissable()
    {
        return $this->isDismissable;
    }

    /**
     * @inheritDoc
     */
    public function id()
    {
        return $this->id;
    }
}
