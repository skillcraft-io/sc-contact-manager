<?php

namespace Skillcraft\ContactManager\Enums;

use Botble\Base\Supports\Enum;

use Html;

/**
 * @method static ContactDataTypeEnum CUSTOMER()
 * @method static ContactDataTypeEnum LEAD()
 */
class ContactTypeEnum extends Enum
{
    public const CUSTOMER   = 'customer';
    public const LEAD       = 'lead';

    /**
     * @var string
     */
    public static $langPath = 'plugins/contact-manager::enums.contact_type';

    /**
     * @return string
     */
    public function toHtml()
    {
        switch ($this->value) {
            default:
                return $this->value;
        }
    }
}
