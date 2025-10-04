<?php
namespace TrustComponent\TrustCaptchaMagento2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class InvisibleHint implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'inline',        'label' => __('Inline')],
            ['value' => 'right-border',  'label' => __('Right border')],
            ['value' => 'right-bottom',  'label' => __('Right bottom')],
            ['value' => 'hidden',        'label' => __('Hidden')],
        ];
    }
}
