<?php
namespace TrustComponent\TrustCaptcha\Plugin\Helper;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\ObjectManagerInterface;
use TrustComponent\TrustCaptcha\Model\Config;

class CaptchaDataPlugin
{
    private array $instances = [];

    public function __construct(
        private ObjectManagerInterface $objectManager,
        private Config $cfg
    ) {}

    public function aroundGetCaptcha(CaptchaHelper $subject, \Closure $proceed, string $formId)
    {
        if (!$this->cfg->isEnabled()) {
            return $proceed($formId);
        }
        if (!isset($this->instances[$formId])) {
            $this->instances[$formId] = $this->objectManager->create(
                \TrustComponent\TrustCaptcha\Model\Captcha\Trustcaptcha::class,
                ['formId' => $formId]
            );
        }
        return $this->instances[$formId];
    }
}
