<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Utils;

/**
 * Class NetworkState
 * @package WCPayPalPlus
 */
class NetworkState
{
    /**
     * @var int
     */
    private $siteId;

    /**
     * @var int[]
     */
    private $stack;

    /**
     * Returns a new instance for the global site ID and switched stack.
     *
     * @return static
     */
    public static function create()
    {
        $switchedStack = isset($GLOBALS['_wp_switched_stack']) ? $GLOBALS['_wp_switched_stack'] : [];

        $state = new static();
        $state->siteId = get_current_blog_id();
        $state->stack = (array)$switchedStack;

        return $state;
    }

    private function __construct()
    {
    }

    /**
     * Restores the stored site state.
     *
     * @return int
     */
    public function restore()
    {
        switch_to_blog($this->siteId);
        $GLOBALS['_wp_switched_stack'] = $this->stack;
        $GLOBALS['switched'] = (bool)$this->stack;

        return get_current_blog_id();
    }
}
