<?php

namespace Inpsyde\Lib\PayPal\Api;

use Inpsyde\Lib\PayPal\Common\PayPalModel;

/**
 * Class TemplateSettingsMetadata
 *
 * Settings Metadata per field in template
 *
 * @package Inpsyde\Lib\PayPal\Api
 *
 * @property bool hidden
 */
class TemplateSettingsMetadata extends PayPalModel
{
    /**
     * Indicates whether this field should be hidden. default is false
     *
     * @param bool $hidden
     * 
     * @return $this
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * Indicates whether this field should be hidden. default is false
     *
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

}
