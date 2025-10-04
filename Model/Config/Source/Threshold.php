<?php
namespace TrustComponent\TrustCaptchaMagento2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Threshold implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $opts = [];
        for ($i = 0; $i <= 100; $i += 5) {
            $v = number_format($i / 100, 2, '.', '');
            $opts[] = ['value' => $v, 'label' => $v];
        }
        return $opts;
    }
}
