<?php
namespace TrustComponent\TrustCaptcha\Model\Captcha;

use Magento\Captcha\Model\CaptchaInterface;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\ResourceModel\LogFactory as ResLogFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Session\SessionManagerInterface;
use TrustComponent\TrustCaptcha\Model\Validation\Validator;
use TrustComponent\TrustCaptcha\Model\Config;

class Trustcaptcha implements CaptchaInterface
{
    private string $formId = '';

    public function __construct(
        private CaptchaHelper $helper,
        private RequestInterface $request,
        private RemoteAddress $remoteAddress,
        private Validator $validator,
        private Config $cfg,
        private SessionManagerInterface $session,
        private ?ResLogFactory $resLogFactory = null,
        ?string $formId = null
    ) {
        if ($formId !== null) {
            $this->formId = (string) $formId;
        }
    }

    public function setFormId($formId)
    {
        $this->formId = (string) $formId;
        return $this;
    }

    public function getFormId()
    {
        return $this->formId;
    }

    public function getIdKey()
    {
        return $this->formId ?: 'trustcaptcha';
    }

    public function getBlockName()
    {
        return \TrustComponent\TrustCaptcha\Block\Captcha\Trust::class;
    }

    public function generate()
    {
        return $this;
    }

    public function isRequired($login = null)
    {
        if (!$this->cfg->isEnabled() || !$this->cfg->isNativeMode()) {
            return false;
        }
        try {
            return (bool) $this->helper->isRequired($this->formId);
        } catch (\Throwable) {
            return true;
        }
    }

    public function isCorrect($word)
    {
        $token = is_string($word) ? $word : '';
        if ($token === '') {
            $token = (string) (
            $this->request->getParam('tc-verification-token')
                ?: $this->request->getParam('verificationToken')
                ?: $this->request->getParam('verification_token')
            );
        }

        $missing = ($token === '');
        $ip = $this->remoteAddress->getRemoteAddress();
        $res = $this->validator->validate($token, $ip);
        $ok = (bool) $res['ok'];

        if (!$ok) {
            $this->session->setData('trustcaptcha_override', [
                'type' => $missing ? 'missing' : 'uncertain',
            ]);
        }

        return $ok;
    }

    public function isCaseSensitive(): bool
    {
        return false;
    }

    public function getHeight(): int
    {
        return 50;
    }

    public function getWidth(): int
    {
        return 150;
    }

    public function getImgSrc(): string
    {
        return '';
    }

    public function logAttempt($login): void
    {
        try {
            if ($this->resLogFactory) {
                $res = $this->resLogFactory->create();
                if (method_exists($res, 'logAttempt')) {
                    $res->logAttempt($this->getFormId(), (string) $login);
                }
            }
        } catch (\Throwable) {}
    }

    public function resetAfterSuccess($login): void
    {
        try {
            if ($this->resLogFactory) {
                $res = $this->resLogFactory->create();
                foreach (['deleteUserAttempts', 'deleteAttempts', 'deleteByUser'] as $m) {
                    if (method_exists($res, $m)) {
                        $res->{$m}($this->getFormId(), (string) $login);
                        break;
                    }
                }
            }
        } catch (\Throwable) {}
    }
}
