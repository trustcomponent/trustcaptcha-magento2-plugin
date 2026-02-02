<?php
namespace TrustComponent\TrustCaptchaMagento2\Model\Validation;

use Psr\Log\LoggerInterface;
use TrustComponent\TrustCaptchaMagento2\Model\Config;

class Validator
{
    public function __construct(
        private Config $cfg,
        private LoggerInterface $logger
    ) {}

    public function validate(?string $token, ?string $remoteIp = null): array
    {
        if (!$this->cfg->isEnabled()) {
            return ['ok' => true, 'botScore' => null, 'explicitFail' => null, 'message' => 'disabled'];
        }

        if (!$token) {
            return ['ok' => false, 'botScore' => null, 'explicitFail' => true, 'message' => 'Please complete the CAPTCHA.'];
        }

        $secret = $this->cfg->getSecretKey();
        if (!$secret) {
            $this->logger->warning('TrustCaptcha: Secret key not configured.');
            return ['ok' => false, 'botScore' => null, 'explicitFail' => true, 'message' => 'Server not configured'];
        }

        try {
            $threshold = (float) $this->cfg->getThreshold();

            if (class_exists('\\TrustComponent\\TrustCaptcha\\CaptchaManager')
                && method_exists('\\TrustComponent\\TrustCaptcha\\CaptchaManager', 'getVerificationResult')) {
                $result = \TrustComponent\TrustCaptcha\CaptchaManager::getVerificationResult($secret, $token);

                $botScore     = isset($result->score) ? (float) $result->score : 0.0;
                $explicitPass = isset($result->verificationPassed) ? (bool) $result->verificationPassed : false;
                $reason       = isset($result->reason) ? (string) $result->reason : '';

                $ok = $explicitPass && ($botScore <= $threshold);

                if (!$ok) {
                    $this->logger->info('TrustCaptcha validation not passed', [
                        'score' => $botScore,
                        'explicitPass' => $explicitPass,
                        'threshold' => $threshold,
                        'reason' => $reason,
                    ]);
                }

                return [
                    'ok' => $ok,
                    'botScore' => $botScore,
                    'explicitFail' => !$explicitPass,
                    'message' => $ok ? 'ok' : 'We could not confirm you are human. Please try again later.',
                ];
            }

            $this->logger->error('TrustCaptcha PHP SDK not available. Install trustcomponent/trustcaptcha-php.');
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'SDK missing'];
        } catch (\Throwable $e) {
            $this->logger->error('TrustCaptcha validation failed', ['exception' => $e]);
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'Validation error'];
        }
    }
}
