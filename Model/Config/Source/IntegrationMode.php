<?php
namespace TrustComponent\TrustCaptcha\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IntegrationMode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'native', 'label' => __('Native CAPTCHA (recommended)')],
            ['value' => 'always', 'label' => __('Always show')],
        ];
    }
}
