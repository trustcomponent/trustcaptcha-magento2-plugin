<?php
namespace TrustComponent\TrustCaptcha\Block;

use Magento\Framework\View\Element\Template;
use TrustComponent\TrustCaptcha\Model\Config as Cfg;

class Config extends Template
{
    public function __construct(
        Template\Context $context,
        private Cfg $cfg,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getJsonConfig(): string
    {
        $data = [
            'enabled'         => $this->cfg->isEnabled(),
            'integrationMode' => $this->cfg->getIntegrationMode(),
            'siteKey'         => $this->cfg->getSiteKey(),
            'secretKeySet'    => (bool)$this->cfg->getSecretKey(),
            'licenseKey'      => $this->cfg->getLicenseKey(),
            'threshold'       => (float)$this->cfg->getThreshold(),
            'forms'           => [
                'login'          => $this->cfg->isFormEnabled('login'),
                'register'       => $this->cfg->isFormEnabled('register'),
                'forgot'         => $this->cfg->isFormEnabled('forgot'),
                'contact'        => $this->cfg->isFormEnabled('contact'),
                'review'         => $this->cfg->isFormEnabled('review'),
                'sendfriend'     => $this->cfg->isFormEnabled('sendfriend'),
                'wishlist_share' => $this->cfg->isFormEnabled('wishlist_share'),
                'order_lookup'   => $this->cfg->isFormEnabled('order_lookup'),
            ],
            'width'              => $this->cfg->getWidth(),
            'language'           => $this->cfg->getLanguage(),
            'theme'              => $this->cfg->getTheme(),
            'autostart'          => $this->cfg->isAutostart(),
            'privacyUrl'         => $this->cfg->getPrivacyUrl(),
            'hideBranding'       => $this->cfg->isHideBranding(),
            'invisible'          => $this->cfg->isInvisible(),
            'invisibleHint'      => $this->cfg->getInvisibleHint(),
            'mode'               => $this->cfg->getMode(),
            'customTranslations' => $this->cfg->getCustomTranslations(),
            'customDesign'       => $this->cfg->getCustomDesign(),
        ];
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
