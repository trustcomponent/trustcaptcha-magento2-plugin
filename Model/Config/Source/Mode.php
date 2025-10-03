<?php
namespace TrustComponent\TrustCaptcha\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'standard', 'label' => __('Standard')],
            ['value' => 'minimal',  'label' => __('Minimal')],
        ];
    }
}
