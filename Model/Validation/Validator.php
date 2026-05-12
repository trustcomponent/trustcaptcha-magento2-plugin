<?php
namespace TrustComponent\TrustCaptchaMagento2\Model\Validation;

use Psr\Log\LoggerInterface;
use TrustComponent\TrustCaptcha\TrustCaptcha;
use TrustComponent\TrustCaptcha\ApiKeyInvalidException;
use TrustComponent\TrustCaptcha\ServerUnreachableException;
use TrustComponent\TrustCaptcha\ClientReportedServerUnreachableException;
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

        $apiKey = $this->cfg->getSecretKey();
        if (!$apiKey) {
            $this->logger->warning('TrustCaptcha: API key not configured.');
            return ['ok' => false, 'botScore' => null, 'explicitFail' => true, 'message' => 'Server not configured'];
        }

        if (!class_exists(TrustCaptcha::class)) {
            $this->logger->error('TrustCaptcha PHP SDK not available. Install trustcomponent/trustcaptcha-php:^3.0.');
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'SDK missing'];
        }

        $threshold = (float) $this->cfg->getThreshold();
        $failoverEnabled = $this->cfg->isFailoverEnabled();

        try {
            $trustCaptcha = new TrustCaptcha($apiKey);
            $result = $trustCaptcha->getVerificationResult($token);

            $botScore     = isset($result->score) ? (float) $result->score : 0.0;
            $explicitPass = isset($result->verificationPassed) ? (bool) $result->verificationPassed : false;

            $ok = $explicitPass && ($botScore <= $threshold);

            if (!$ok) {
                $this->logger->info('TrustCaptcha validation not passed', [
                    'score' => $botScore,
                    'explicitPass' => $explicitPass,
                    'threshold' => $threshold,
                ]);
            }

            return [
                'ok' => $ok,
                'botScore' => $botScore,
                'explicitFail' => !$explicitPass,
                'message' => $ok ? 'ok' : 'We could not confirm you are human. Please try again later.',
            ];
        } catch (ServerUnreachableException $e) {
            if ($failoverEnabled) {
                $this->logger->warning('TrustCaptcha: API unreachable from server — allowed via failover. ' . $e->getMessage());
                return ['ok' => true, 'botScore' => null, 'explicitFail' => false, 'message' => 'failover'];
            }
            $this->logger->error('TrustCaptcha: API unreachable from server, failover disabled. ' . $e->getMessage());
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'CAPTCHA verification failed because our servers were not reachable. Please try again in a moment.'];
        } catch (ClientReportedServerUnreachableException $e) {
            // Always reject client-reported failover.
            $this->logger->warning('TrustCaptcha: Client reported failover but our API is reachable — blocked. ' . $e->getMessage());
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'CAPTCHA verification could not be confirmed. Please try again.'];
        } catch (ApiKeyInvalidException $e) {
            $this->logger->error('TrustCaptcha: API key invalid. ' . $e->getMessage());
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'Configuration error'];
        } catch (\Throwable $e) {
            $this->logger->error('TrustCaptcha validation failed', ['exception' => $e]);
            return ['ok' => false, 'botScore' => null, 'explicitFail' => null, 'message' => 'Validation error'];
        }
    }
}
