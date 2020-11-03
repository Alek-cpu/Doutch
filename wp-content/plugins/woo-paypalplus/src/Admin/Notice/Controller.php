<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Admin\Notice;

/**
 * Class Controller
 * @package WCPayPalPlus\Admin\Notice
 */
class Controller
{
    const OPTION = 'paypalplus_admin_notices';

    /**
     * @var NoticeRender
     */
    private $noticeRenderer;

    /**
     * Controller constructor.
     * @param NoticeRender $noticeRenderer
     */
    public function __construct(NoticeRender $noticeRenderer)
    {
        $this->noticeRenderer = $noticeRenderer;
    }

    /**
     * Render the Admin Notice if not Already Dismissed
     *
     * @param Noticeable $notice
     */
    public function maybeRender(Noticeable $notice)
    {
        $option = $this->option();
        in_array($notice->id(), $option, true) or $this->noticeRenderer->render($notice);
    }

    /**
     * Dismiss a notice by the Given Id
     *
     * @param $noticeId
     */
    public function dismiss($noticeId)
    {
        assert(is_string($noticeId));

        $option = $this->option();

        if (in_array($noticeId, $option, true)) {
            return;
        }

        $option[] = $noticeId;

        $this->persist($option);
    }

    /**
     * Retrieve Options
     *
     * @return array
     */
    private function option()
    {
        return array_filter((array)get_option(self::OPTION, []));
    }

    /**
     * Save Options
     *
     * @param array $option
     */
    private function persist(array $option)
    {
        update_option(self::OPTION, $option);
    }
}
