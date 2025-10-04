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

            $pick = static function ($source, array $keys) {
                foreach ($keys as $k) {
                    if (is_array($source) && array_key_exists($k, $source)) {
                        return $source[$k];
                    }
                    if (is_object($source) && isset($source->$k)) {
                        return $source->$k;
                    }
                }
                return null;
            };

            if (class_exists('\\TrustComponent\\TrustCaptcha\\CaptchaManager')
                && method_exists('\\TrustComponent\\TrustCaptcha\\CaptchaManager', 'getVerificationResult')) {
                /** @var \TrustComponent\TrustCaptcha\CaptchaManager $cm */
                $cm = new \TrustComponent\TrustCaptcha\CaptchaManager();
                $result = $cm->getVerificationResult($secret, $token);

                $botScore     = (float) ($pick($result, ['score', 'botScore']) ?? 0.0);
                $explicitPass = (bool) ($pick($result, ['verificationPassed', 'explicitVerificationPass']) ?? true);
                $reason       = (string) ($pick($result, ['reason', 'message']) ?? '');

                $ok = $explicitPass && ($botScore < $threshold);

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

            if (class_exists('\\TrustComponent\\TrustCaptcha\\Client')) {
                /** @var \TrustComponent\TrustCaptcha\Client $client */
                $client = new \TrustComponent\TrustCaptcha\Client($secret);
                $result = $client->verify($token, [
                    'remoteIp' => $remoteIp,
                    'siteKey'  => $this->cfg->getSiteKey()
                ]);

                $botScore     = (float) ($pick($result, ['botScore', 'score']) ?? 0.0);
                $explicitPass = (bool) ($pick($result, ['explicitVerificationPass', 'verificationPassed']) ?? true);
                $reason       = (string) ($pick($result, ['reason', 'message']) ?? '');

                $ok = $explicitPass && ($botScore < $threshold);

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
                    'message' => $ok ? 'ok' : ($reason !== '' ? $reason : 'score too high or explicit check failed'),
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
