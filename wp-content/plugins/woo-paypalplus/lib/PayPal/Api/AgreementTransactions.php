<?php

namespace Inpsyde\Lib\PayPal\Api;

use Inpsyde\Lib\PayPal\Common\PayPalModel;

/**
 * Class AgreementTransactions
 *
 * A resource representing agreement_transactions that is returned during a transaction search.
 *
 * @package Inpsyde\Lib\PayPal\Api
 *
 * @property \Inpsyde\Lib\PayPal\Api\AgreementTransaction[] agreement_transaction_list
 */
class AgreementTransactions extends PayPalModel
{
    /**
     * Array of agreement_transaction object.
     *
     * @param \Inpsyde\Lib\PayPal\Api\AgreementTransaction[] $agreement_transaction_list
     * 
     * @return $this
     */
    public function setAgreementTransactionList($agreement_transaction_list)
    {
        $this->agreement_transaction_list = $agreement_transaction_list;
        return $this;
    }

    /**
     * Array of agreement_transaction object.
     *
     * @return \Inpsyde\Lib\PayPal\Api\AgreementTransaction[]
     */
    public function getAgreementTransactionList()
    {
        return $this->agreement_transaction_list;
    }

    /**
     * Append AgreementTransactionList to the list.
     *
     * @param \Inpsyde\Lib\PayPal\Api\AgreementTransaction $agreementTransaction
     * @return $this
     */
    public function addAgreementTransactionList($agreementTransaction)
    {
        if (!$this->getAgreementTransactionList()) {
            return $this->setAgreementTransactionList(array($agreementTransaction));
        } else {
            return $this->setAgreementTransactionList(
                array_merge($this->getAgreementTransactionList(), array($agreementTransaction))
            );
        }
    }

    /**
     * Remove AgreementTransactionList from the list.
     *
     * @param \Inpsyde\Lib\PayPal\Api\AgreementTransaction $agreementTransaction
     * @return $this
     */
    public function removeAgreementTransactionList($agreementTransaction)
    {
        return $this->setAgreementTransactionList(
            array_diff($this->getAgreementTransactionList(), array($agreementTransaction))
        );
    }

}
