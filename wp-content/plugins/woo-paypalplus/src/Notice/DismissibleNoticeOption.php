<?php

namespace WCPayPalPlus\Notice;

class DismissibleNoticeOption
{
    const OPTION_PREFIX = 'paypalplus_dinotopt_';
    const FOR_GOOD_ACTION = 'dismiss_admin_notice_for_good';
    const FOR_NOW_ACTION = 'dismiss_admin_notice_for_now';
    const FOR_USER_FOR_GOOD_ACTION = 'dismiss_admin_notice_for_good_user';
    const SKIP = 'skip_action';

    private static $setup = [
        'sitewide' => [],
        'blog' => [],
    ];

    private static $allActions = [
        self::FOR_GOOD_ACTION,
        self::FOR_NOW_ACTION,
        self::FOR_USER_FOR_GOOD_ACTION,
    ];

    /**
     * @var bool
     */
    private $sitewide;

    /**
     * @param bool $sitewide
     * @param string $noticeId
     * @param string $capability
     */
    public static function setupActions($sitewide, $noticeId, $capability = 'read')
    {
        if (!is_string($noticeId)) {
            return;
        }

        $sitewide = $sitewide && is_multisite();

        $key = $sitewide ? 'sitewide' : 'blog';

        if (array_key_exists($noticeId, self::$setup[$key])) {
            return;
        }

        if (self::$setup[$key] === []) {
            $option = new self($sitewide);
            add_action('admin_post_' . self::FOR_GOOD_ACTION, [$option, 'dismiss']);
            add_action('admin_post_' . self::FOR_NOW_ACTION, [$option, 'dismiss']);
            add_action('admin_post_' . self::FOR_USER_FOR_GOOD_ACTION, [$option, 'dismiss']);
        }

        self::$setup[$key][$noticeId] = $capability;
    }

    /**
     * @param string $noticeId
     * @param string $action
     *
     * @return string
     */
    public static function dismissActionUrl($noticeId, $action)
    {
        return add_query_arg(
            [
                'action' => $action,
                'notice' => $noticeId,
                'blog' => get_current_blog_id(),
                $action => wp_create_nonce($action),
            ],
            admin_url('admin-post.php')
        );
    }

    /**
     * @param bool $sitewide
     */
    public function __construct($sitewide = false)
    {
        $this->sitewide = $sitewide;
    }

    /**
     * Returns true when given notice is dismissed for good or temporarily for current user.
     *
     * @param $noticeId
     *
     * @return bool
     */
    public function isDismissed($noticeId)
    {
        $optionName = self::OPTION_PREFIX . $noticeId;

        // Dismissed for good?
        $option = $this->sitewide ? get_site_option($optionName) : get_option($optionName);
        if ($option) {
            return true;
        }

        // Dismissed for good for user?
        if (get_user_option($optionName)) {
            return true;
        }

        // Dismissed for now for user?
        $transientName = self::OPTION_PREFIX . $noticeId . get_current_user_id();
        $transient = $this->sitewide
            ? get_site_transient($transientName)
            : get_transient($transientName);

        return (bool)$transient;
    }

    /**
     * Action callback to dismiss an action for good.
     */
    public function dismiss()
    {
        list($action, $noticeId, $isAjax) = $this->assertAllowed();

        $endRequest = true;

        switch ($action) {
            case self::FOR_GOOD_ACTION:
                $this->dismissForGood($noticeId);
                break;
            case self::FOR_USER_FOR_GOOD_ACTION:
                $this->dismissisForUserForGood($noticeId);
                break;
            case self::FOR_NOW_ACTION:
                $this->dismissForNow($noticeId);
                break;
            case self::SKIP:
                $endRequest = false;
                break;
        }

        $endRequest and $this->endRequest($isAjax);
    }

    /**
     * Action callback to dismiss an action for good.
     *
     * @param string $noticeId
     */
    private function dismissForGood($noticeId)
    {
        $optionName = self::OPTION_PREFIX . $noticeId;

        $this->sitewide
            ? update_site_option($optionName, 1)
            : update_option($optionName, 1, false);
    }

    /**
     * Action callback to dismiss an action definitively for current user.
     *
     * @param string $noticeId
     */
    private function dismissisForUserForGood($noticeId)
    {
        update_user_option(
            get_current_user_id(),
            self::OPTION_PREFIX . $noticeId,
            1,
            $this->sitewide
        );
    }

    /**
     * Action callback to dismiss an action temporarily for current user.
     *
     * @param string $noticeId
     */
    private function dismissForNow($noticeId)
    {
        $transientName = self::OPTION_PREFIX . $noticeId . get_current_user_id();
        $expiration = 12 * HOUR_IN_SECONDS;

        $this->sitewide
            ? set_site_transient($transientName, 1, $expiration)
            : set_transient($transientName, 1, $expiration);
    }

    /**
     * Ends a request redirecting to referer page.
     *
     * @param bool $noRedirect
     */
    private function endRequest($noRedirect = false)
    {
        if ($noRedirect) {
            exit();
        }

        $referer = wp_get_raw_referer();
        if (!$referer) {
            $referer = $this->sitewide && is_super_admin() ? network_admin_url() : admin_url();
        }

        wp_safe_redirect($referer);
        exit();
    }

    /**
     * @return array
     */
    private function assertAllowed()
    {
        if (!is_admin()) {
            $this->endRequest();
        }

        $definition = [
            'action' => FILTER_SANITIZE_STRING,
            'notice' => FILTER_SANITIZE_STRING,
            'blog' => FILTER_SANITIZE_NUMBER_INT,
            'isAjax' => FILTER_VALIDATE_BOOLEAN,
        ];

        $data = array_merge(
            array_filter((array)filter_input_array(INPUT_GET, $definition)),
            array_filter((array)filter_input_array(INPUT_POST, $definition))
        );

        $isAjax = !empty($data['isAjax']);
        $action = empty($data['action']) ? '' : $data['action'];
        $notice = empty($data['notice']) ? '' : $data['notice'];

        if (!$action
            || !$notice
            || !is_string($notice)
            || !in_array($action, self::$allActions, true)
        ) {
            $this->endRequest($isAjax);
        }

        $key = $this->sitewide ? 'sitewide' : 'blog';
        $swapKey = $this->sitewide ? 'blog' : 'sitewide';
        $capability = empty(self::$setup[$key][$notice]) ? '' : self::$setup[$key][$notice];

        if (!$capability && !empty(self::$setup[$swapKey][$notice])) {
            return [self::SKIP, '', $isAjax];
        }

        if (!$capability || !current_user_can($capability)) {
            $this->endRequest($isAjax);
        }

        $nonce = filter_input(INPUT_POST, $action, FILTER_SANITIZE_STRING);
        $nonce or $nonce = filter_input(INPUT_GET, $action, FILTER_SANITIZE_STRING);

        if (!$nonce || !wp_verify_nonce($nonce, $action)) {
            $this->endRequest($isAjax);
        }

        if (!$this->sitewide
            && (empty($data['blog']) || get_current_blog_id() !== (int)$data['blog'])
        ) {
            $this->endRequest($isAjax);
        }

        return [
            $action,
            $notice,
            $isAjax,
        ];
    }
}
