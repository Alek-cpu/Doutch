<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

/**
 * Interface PlusStorable
 * @package WCPayPalPlus\Setting
 */
interface PlusStorable extends Storable
{
    const OPTION_DISABLE_GATEWAY_OVERRIDE = 'disable_gateway_override';
    const OPTION_LEGAL_NOTE = 'legal_note';

    /**
     * @return bool
     */
    public function isDisableGatewayOverrideEnabled();

    /**
     * @return string
     */
    public function legalNotes();
}
