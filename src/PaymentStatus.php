<?php

namespace Cryptomus\Woocommerce;

final class PaymentStatus
{
    const PAYMENT_STATUS_PROCESS = 'process';
    const PAYMENT_STATUS_CHECK = 'check';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAIL = 'fail';
    const PAYMENT_STATUS_CANCEL = 'cancel';
    const PAYMENT_STATUS_WRONG_AMOUNT = 'wrong_amount';
    const PAYMENT_STATUS_PAID_OVER = 'paid_over';
    const PAYMENT_STATUS_SYSTEM_FAIL = 'system_fail';
    const PAYMENT_STATUS_REFUND_PROCESS = 'refund_process';
    const PAYMENT_STATUS_REFUND_FAIL = 'refund_fail';
    const PAYMENT_STATUS_REFUND_PAID = 'refund_paid';
    const PAYMENT_STATUS_WRONG_AMOUNT_WAITING = 'wrong_amount_waiting';
    const PAYMENT_STATUS_CONFIRM_CHECK = 'confirm_check';

    const WC_STATUS_REFUNDED = 'refunded';
    const WC_STATUS_PENDING = 'pending';
    const WC_STATUS_PROCESSING = 'processing';
    const WC_STATUS_COMPLETED = 'completed';
    const WC_STATUS_FAIL = 'failed';
    const WC_STATUS_CANCELED = 'cancelled';
    const WC_STATUS_HOLD = 'on-hold';
    const WC_STATUS_WRONG_AMOUNT = 'wrong-amount';

    /**
     * @param $status
     * @return string
     */
    public static function convertToWoocommerceStatus($status, $all_downloadable_or_virtual)
    {
        switch ($status) {
            case self::PAYMENT_STATUS_PROCESS:
            case self::PAYMENT_STATUS_CHECK:
            case self::PAYMENT_STATUS_WRONG_AMOUNT_WAITING:
            case self::PAYMENT_STATUS_CONFIRM_CHECK:
            case self::PAYMENT_STATUS_REFUND_PROCESS:
                $result = self::WC_STATUS_PENDING;
                break;

            case self::PAYMENT_STATUS_PAID:
            case self::PAYMENT_STATUS_PAID_OVER:
                $result = self::WC_STATUS_PROCESSING;
                break;

            case self::PAYMENT_STATUS_FAIL:
            case self::PAYMENT_STATUS_SYSTEM_FAIL:
            case self::PAYMENT_STATUS_REFUND_FAIL:
                $result = self::WC_STATUS_FAIL;
                break;

            case self::PAYMENT_STATUS_CANCEL:
                $result = self::WC_STATUS_CANCELED;
                break;

            case self::PAYMENT_STATUS_WRONG_AMOUNT:
                $result = self::WC_STATUS_WRONG_AMOUNT;
                break;

            case self::PAYMENT_STATUS_REFUND_PAID:
                $result = self::WC_STATUS_REFUNDED;
                break;

            default:
                $result = self::WC_STATUS_HOLD;
        }
        if ($all_downloadable_or_virtual && $result === self::WC_STATUS_PROCESSING) {
            $result = self::WC_STATUS_COMPLETED;
        }
        return $result;
    }

    public static function isNeedReturnStocks($status)
    {
        $status = self::convertToWoocommerceStatus($status);

        if ($status === self::WC_STATUS_CANCELED || $status === self::WC_STATUS_FAIL) {
            return true;
        }

        return false;
    }
}
