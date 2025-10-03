<?php
namespace TrustComponent\TrustCaptcha\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const PATH = 'trustcaptcha/';

    public function __construct(
        private ScopeConfigInterface $scope,
        private EncryptorInterface $encryptor
    ) {}

    private function get(string $path, ?string $scopeCode = null): string
    {
        return (string) $this->scope->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    public function isEnabled(): bool { return (bool) $this->get(self::PATH . 'general/enabled'); }
    public function getIntegrationMode(): string { return $this->get(self::PATH . 'general/integration_mode') ?: 'native'; }
    public function isNativeMode(): bool { return $this->getIntegrationMode() === 'native'; }
    public function isAlwaysMode(): bool { return $this->getIntegrationMode() === 'always'; }
    public function getSiteKey(): string { return $this->get(self::PATH . 'general/site_key'); }

    public function getSecretKey(): string
    {
        $raw = $this->get(self::PATH . 'general/secret_key');
        if ($raw === '') {
            return '';
        }
        try {
            $dec = $this->encryptor->decrypt($raw);
            if ($dec !== '') {
                return $dec;
            }
        } catch (\Throwable) {}
        return $raw;
    }

    public function getLicenseKey(): string
    {
        $raw = $this->get(self::PATH . 'general/license_key');
        if ($raw === '') {
            return '';
        }
        try {
            $dec = $this->encryptor->decrypt($raw);
            if ($dec !== '') {
                return $dec;
            }
        } catch (\Throwable) {}
        return $raw;
    }

    public function getThreshold(): string { return $this->get(self::PATH . 'general/threshold') ?: '0.5'; }
    public function getWidth(): string { return $this->get(self::PATH . 'general/width') ?: 'full'; }
    public function getLanguage(): string { return $this->get(self::PATH . 'general/language') ?: 'auto'; }
    public function getTheme(): string { return $this->get(self::PATH . 'general/theme') ?: 'light'; }
    public function isAutostart(): bool { return (bool) $this->get(self::PATH . 'general/autostart'); }
    public function getPrivacyUrl(): string { return $this->get(self::PATH . 'general/privacy_url'); }
    public function isHideBranding(): bool { return (bool) $this->get(self::PATH . 'general/hide_branding'); }
    public function isInvisible(): bool { return (bool) $this->get(self::PATH . 'general/invisible'); }
    public function getInvisibleHint(): string { return $this->get(self::PATH . 'general/invisible_hint') ?: 'inline'; }
    public function getMode(): string { return $this->get(self::PATH . 'general/mode') ?: 'standard'; }

    public function getCustomTranslations(): ?array
    {
        $raw = trim((string) $this->get(self::PATH . 'general/custom_translations'));
        if ($raw === '') {
            return null;
        }
        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getCustomDesign(): ?array
    {
        $raw = trim((string) $this->get(self::PATH . 'general/custom_design'));
        if ($raw === '') {
            return null;
        }
        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function isFormEnabled(string $key): bool
    {
        return (bool) $this->get(self::PATH . 'forms/enable_' . $key);
    }
}
