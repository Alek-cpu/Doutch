<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 18:17
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway;

/**
 * Class PaymentPatcher
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentPatcher
{
    const ACTION_AFTER_PAYMENT_PATCH = 'woopaypalplus.after_express_checkout_payment_patch';

    /**
     * Patch data object.
     *
     * @var PaymentPatchData
     */
    private $patchData;

    /**
     * PaymentPatcher constructor.
     *
     * @param PaymentPatchData $patch_data You guessed it: The Patch data.
     */
    public function __construct(PaymentPatchData $patch_data)
    {
        $this->patchData = $patch_data;
    }

    /**
     * Execute the PatchRequest
     *
     * @throws PayPalConnectionException
     */
    public function execute()
    {
        $patchRequest = $this->patchData->get_patch_request();

        $payment = $this->patchData->get_payment();
        $payment->update(
            $patchRequest,
            $this->patchData->get_api_context()
        );

        /**
         * Action After Payment Patch
         *
         * @param PaymentPatcher $paymentPatcher
         * @oparam bool $isSuccessPatched
         */
        do_action(self::ACTION_AFTER_PAYMENT_PATCH, $this, $this->patchData);
    }
}
