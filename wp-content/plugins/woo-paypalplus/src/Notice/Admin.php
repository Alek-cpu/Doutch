<?php

namespace WCPayPalPlus\Notice;

/**
 * Class to display admin notices
 */
class Admin
{
    const ACTION_ADMIN_MESSAGES = 'ppplus_admin_messages';
    const SITE_TRANSIENT_MESSAGE_ID = 'ppplus_message_id';
    const SITE_TRANSIENT_MESSAGE_CONTENT = 'ppplus_message_content';
    const NOTICE_URL = 'http://paypalnotice.inpsyde.com/';

    /**
     * @var bool
     */
    private $shouldShow;

    /**
     * @var bool
     */
    private $hasBeenDisplayed;

    /**
     * @var string
     */
    private $id;

    /**
     * @var false|string
     */
    private $content;

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        $this->hasBeenDisplayed = false;

        $this->id = get_site_transient(self::SITE_TRANSIENT_MESSAGE_ID);
        $this->content = get_site_transient(self::SITE_TRANSIENT_MESSAGE_CONTENT);

        if ($this->id === false || $this->content === false) {
            $this->id = uniqid();

            $apiResponse = wp_remote_get(self::NOTICE_URL, ['timeout' => 3]);
            $this->content = wp_remote_retrieve_body($apiResponse);

            set_site_transient(
                self::SITE_TRANSIENT_MESSAGE_ID,
                $this->id
            );
            set_site_transient(
                self::SITE_TRANSIENT_MESSAGE_CONTENT,
                $this->content,
                DAY_IN_SECONDS
            );
        }
    }

    /**
     * Initialize
     */
    public function setupActions()
    {
        DismissibleNoticeOption::setupActions(
            false,
            $this->id
        );
    }

    /**
     * Display a notice.
     */
    public function display()
    {
        if (!$this->hasBeenDisplayed && !empty($this->content) && $this->shouldDisplay()) {
            ?>
            <div class="notice notice-info"
                 id="ppplus_dismiss_<?php echo esc_attr($this->id) ?>_notice"
            >
                <div class="inside">
                    <?php $this->markup() ?>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Should we display the notice?
     *
     * @return bool
     */
    private function shouldDisplay()
    {
        if (!is_bool($this->shouldShow)) {
            $option = new DismissibleNoticeOption(true);
            $this->shouldShow = !$option->isDismissed($this->id);
        }

        return $this->shouldShow;
    }

    /**
     * The markup for the admin page message.
     *
     * @return string
     */
    private function markup()
    {
        $dismissUrl = DismissibleNoticeOption::dismissActionUrl(
            $this->id,
            DismissibleNoticeOption::FOR_USER_FOR_GOOD_ACTION
        );
        ?>
        <p>
            <?php echo esc_html($this->content) ?>
        </p>
        <p>
            <a class="button"
               id="ppplus_dismiss_<?php echo esc_attr($this->id) ?>"
               href="<?php echo esc_url($dismissUrl) ?>"
            >
                <?php echo esc_html__('Don\'t show again', 'woo-paypalplus') ?>
            </a>
        </p>
        <script>
            (
                function ($) {
                    $('#ppplus_dismiss_<?php echo esc_js($this->id) ?>').on('click', function (e) {
                        e.preventDefault();
                        $.post($(this).attr('href'), { isAjax: 1 });
                        $('#ppplus_dismiss_<?php echo esc_js($this->id) ?>_notice').hide();
                    });
                }
            )(jQuery);
        </script>
        <?php
    }
}
