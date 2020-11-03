<?php

namespace Inpsyde\Lib\PayPal\Api;

use Inpsyde\Lib\PayPal\Common\PayPalModel;
use Inpsyde\Lib\PayPal\Converter\FormatConverter;
use Inpsyde\Lib\PayPal\Validation\NumericValidator;

/**
 * Class Tax
 *
 * Tax information.
 *
 * @package Inpsyde\Lib\PayPal\Api
 *
 * @property string id
 * @property string name
 * @property \Inpsyde\Lib\PayPal\Api\number percent
 * @property \Inpsyde\Lib\PayPal\Api\Currency amount
 */
class Tax extends PayPalModel
{
    /**
     * The resource ID.
     *
     * @param string $id
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * The resource ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The tax name. Maximum length is 20 characters.
     *
     * @param string $name
     * 
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The tax name. Maximum length is 20 characters.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The rate of the specified tax. Valid range is from 0.001 to 99.999.
     *
     * @param string|double $percent
     * 
     * @return $this
     */
    public function setPercent($percent)
    {
        NumericValidator::validate($percent, "Percent");
        $percent = FormatConverter::formatToPrice($percent);
        $this->percent = $percent;
        return $this;
    }

    /**
     * The rate of the specified tax. Valid range is from 0.001 to 99.999.
     *
     * @return string
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * The tax as a monetary amount. Cannot be specified in a request.
     *
     * @param \Inpsyde\Lib\PayPal\Api\Currency $amount
     * 
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * The tax as a monetary amount. Cannot be specified in a request.
     *
     * @return \Inpsyde\Lib\PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
