<?php
namespace TrustComponent\TrustCaptcha\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Language implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'auto', 'label' => 'Auto Detection'],
            ['value' => 'sq',   'label' => 'Albanian'],
            ['value' => 'ar',   'label' => 'Arabic'],
            ['value' => 'be',   'label' => 'Belarusian'],
            ['value' => 'bs',   'label' => 'Bosnian'],
            ['value' => 'bg',   'label' => 'Bulgarian'],
            ['value' => 'ca',   'label' => 'Catalan'],
            ['value' => 'zh',   'label' => 'Chinese'],
            ['value' => 'hr',   'label' => 'Croatian'],
            ['value' => 'cs',   'label' => 'Czech'],
            ['value' => 'da',   'label' => 'Danish'],
            ['value' => 'nl',   'label' => 'Dutch'],
            ['value' => 'en',   'label' => 'English'],
            ['value' => 'et',   'label' => 'Estonian'],
            ['value' => 'fi',   'label' => 'Finnish'],
            ['value' => 'fr',   'label' => 'French'],
            ['value' => 'de',   'label' => 'German'],
            ['value' => 'el',   'label' => 'Greek'],
            ['value' => 'hi',   'label' => 'Hindi'],
            ['value' => 'hu',   'label' => 'Hungarian'],
            ['value' => 'it',   'label' => 'Italian'],
            ['value' => 'ko',   'label' => 'Korean'],
            ['value' => 'lv',   'label' => 'Latvian'],
            ['value' => 'lt',   'label' => 'Lithuanian'],
            ['value' => 'lb',   'label' => 'Luxembourgish'],
            ['value' => 'mk',   'label' => 'Macedonian'],
            ['value' => 'no',   'label' => 'Norwegian'],
            ['value' => 'pl',   'label' => 'Polish'],
            ['value' => 'pt',   'label' => 'Portuguese'],
            ['value' => 'ro',   'label' => 'Romanian'],
            ['value' => 'ru',   'label' => 'Russian'],
            ['value' => 'sr',   'label' => 'Serbian'],
            ['value' => 'sk',   'label' => 'Slovak'],
            ['value' => 'sl',   'label' => 'Slovenian'],
            ['value' => 'es',   'label' => 'Spanish'],
            ['value' => 'sv',   'label' => 'Swedish'],
            ['value' => 'tr',   'label' => 'Turkish'],
            ['value' => 'uk',   'label' => 'Ukrainian'],
        ];
    }
}
