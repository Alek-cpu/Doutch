<?php


namespace WCPayPalPlus\Banner;

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\NonceInterface;
use WCPayPalPlus\Admin\Notice\Controller;

class BannerAjaxHandler
{
    const ACTION = 'enable_paypal_banner_feature';

    /**
     * @var Controller
     */
    private $controller;
    /**
     * @var NonceInterface
     */
    private $nonce;
    /**
     * @var NonceContextInterface
     */
    private $nonceContext;

    /**
     * BannerAjaxHandler constructor.
     *
     * @param Controller            $controller
     * @param NonceInterface        $nonce
     * @param NonceContextInterface $nonceContext
     */
    public function __construct(
        NonceInterface $nonce,
        NonceContextInterface $nonceContext,
        Controller $controller
    ) {
        $this->nonce = $nonce;
        $this->nonceContext = $nonceContext;
        $this->controller = $controller;
    }

    public function handle()
    {
        if (!$this->validateRequest() || !$this->userCan()) {
            return;
        }

        $this->enableBanner();
        $this->dismissBanner();
    }

    protected function validateRequest()
    {
        $isValid = false;

        if ($this->nonce->validate($this->nonceContext)) {
            $isValid = true;
        }

        return $isValid;
    }

    protected function userCan()
    {
        return current_user_can('manage_options');
    }

    protected function enableBanner()
    {
        update_option('banner_settings_enableBanner', 'yes');
    }

    protected function dismissBanner()
    {
        $this->controller->dismiss('WCPayPalPlus\Admin\Notice\BannerNotice');
    }
}
