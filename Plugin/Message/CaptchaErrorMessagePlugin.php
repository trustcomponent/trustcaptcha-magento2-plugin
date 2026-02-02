<?php
namespace TrustComponent\TrustCaptchaMagento2\Plugin\Message;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\Session\SessionManagerInterface;

class CaptchaErrorMessagePlugin
{
    private const ACTIONS = [
        'customer_account_loginpost',
        'customer_account_createpost',
        'customer_account_forgotpasswordpost',
        'contact_index_post',
        'review_product_post',
        'sendfriend_product_sendmail',
        'wishlist_index_sharepost',
        'sales_guest_formpost',
    ];

    public function __construct(
        private RequestInterface $request,
        private SessionManagerInterface $session
    ) {}

    public function beforeAddErrorMessage(Manager $subject, $message): array
    {
        $flag = $this->session->getData('trustcaptcha_override');
        if (!$flag || !is_array($flag)) {
            return [$message];
        }

        $full = strtolower((string) $this->request->getFullActionName());
        if (!in_array($full, self::ACTIONS, true)) {
            return [$message];
        }

        $this->session->unsetData('trustcaptcha_override');

        $type = $flag['type'] ?? 'uncertain';
        if ($type === 'missing') {
            return [__('Please complete the CAPTCHA.')];
        }

        $raw = (string) ($flag['message'] ?? '');
        if ($raw !== '' && in_array($raw, ['Server not configured', 'SDK missing', 'Validation error'], true)) {
            return [__($raw)];
        }

        return [__('We could not confirm you are human. Please try again later.')];
    }
}
