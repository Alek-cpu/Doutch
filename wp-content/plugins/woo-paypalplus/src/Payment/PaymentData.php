<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 16:42
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class PaymentData
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentData
{
    /**
     * The URL to return back to after finishing payment.
     *
     * @var string
     */
    private $return_url;

    /**
     *  The URL to return back to cancelling finishing payment.
     *
     * @var string
     */
    private $cancel_url;

    /**
     * The URL to use for IPN.
     *
     * @var string
     */
    private $notify_url;

    /**
     * The Web Profile ID to use during the payment process.
     *
     * @var string
     */
    private $web_profile_id;

    /**
     * The PayPal SDK ApiContext object.
     *
     * @var ApiContext
     */
    private $api_context;

    /**
     * PaymentData constructor.
     *
     * @param string $return_url The URL to return back to after finishing payment.
     * @param string $cancel_url The URL to return back to cancelling finishing payment.
     * @param string $notify_url The URL to use for IPN.
     * @param string $web_profile_id The Web Profile ID to use during the payment process.
     * @param ApiContext $api_context The PayPal SDK ApiContext object.
     */
    public function __construct(
        $return_url,
        $cancel_url,
        $notify_url,
        $web_profile_id,
        ApiContext $api_context
    ) {

        $this->return_url = $return_url;
        $this->cancel_url = $cancel_url;
        $this->notify_url = $notify_url;
        $this->web_profile_id = $web_profile_id;
        $this->api_context = $api_context;
    }

    /**
     * Returns the IPN URL.
     *
     * @return string
     */
    public function get_notify_url()
    {
        return $this->notify_url;
    }

    /**
     * Returns the cancel URL.
     *
     * @return string
     */
    public function get_cancel_url()
    {
        return $this->cancel_url;
    }

    /**
     * Returns the ...return URL.
     *
     * @return string
     */
    public function get_return_url()
    {
        return $this->return_url;
    }

    /**
     * Returns the web profile ID.
     *
     * @return string
     */
    public function get_web_profile_id()
    {
        return $this->web_profile_id;
    }

    /**
     * Returns the APIContext object.
     *
     * @return ApiContext
     */
    public function get_api_context()
    {
        return $this->api_context;
    }
}
