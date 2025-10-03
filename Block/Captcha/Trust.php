<?php
namespace TrustComponent\TrustCaptcha\Block\Captcha;

use Magento\Captcha\Block\Captcha\DefaultCaptcha as MagentoDefaultCaptcha;
use TrustComponent\TrustCaptcha\Model\Config;

class Trust extends MagentoDefaultCaptcha
{
    protected $_template = 'TrustComponent_TrustCaptcha::captcha/trustcaptcha.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Captcha\Helper\Data $captchaData,
        private Config $cfg,
        array $data = []
    ) {
        parent::__construct($context, $captchaData, $data);
    }

    public function cfg(): Config
    {
        return $this->cfg;
    }
}
