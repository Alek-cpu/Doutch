<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Pui;

use WCPayPalPlus\Setting;

/**
 * Class PaymentInstructionRenderer
 *
 * @package WCPayPalPlus\Pui
 */
class Renderer
{
    /**
     * @var Setting\PlusStorable
     */
    private $settingRepository;

    /**
     * Renderer constructor.
     * @param Setting\PlusStorable $settingRepository
     */
    public function __construct(Setting\PlusStorable $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Gather needed data and then render the view, if possible
     */
    public function delegateThankyou($orderId)
    {
        $order = wc_get_order($orderId);
        $puiData = Factory::createData(
            $order,
            $this->settingRepository->legalNotes()
        );

        if (!$puiData->hasPaymentInstructions()) {
            return;
        }

        $puiView = Factory::createViewFromData($puiData);
        $puiView->thankyouPage();
    }

    /**
     * Gather needed data and then render the view, if possible
     *
     * @param \WC_Order $order WooCommerce order.
     * @param bool $sentToAdmin Will the eMail be sent to the site admin?.
     * @param bool $plain_text Should we render as plain text?.
     */
    public function delegateEmail(\WC_Order $order, $sentToAdmin, $plain_text = false)
    {
        $puiData = Factory::createData(
            $order,
            $this->settingRepository->legalNotes()
        );

        if (!$puiData->hasPaymentInstructions()) {
            return;
        }

        $puiView = Factory::createViewFromData($puiData);
        $puiView->emailInstructions($plain_text);
    }

    /**
     * Gather needed data and then render the view, if possible.
     *
     * @param int $orderId WooCommerce order ID.
     */
    public function delegateViewOrder($orderId)
    {
        $order = wc_get_order($orderId);
        $puiData = Factory::createData(
            $order,
            $this->settingRepository->legalNotes()
        );

        if (!$puiData->hasPaymentInstructions()) {
            return;
        }

        $puiView = Factory::createViewFromData($puiData);
        $puiView->thankyouPage();
    }
}
