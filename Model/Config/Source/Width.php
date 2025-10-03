<?php
namespace TrustComponent\TrustCaptcha\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Width implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'full',   'label' => __('Full')],
            ['value' => 'fixed',  'label' => __('Fixed')],
        ];
    }
}
