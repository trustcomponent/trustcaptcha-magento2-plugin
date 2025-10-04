<?php
namespace TrustComponent\TrustCaptchaMagento2\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use TrustComponent\TrustCaptchaMagento2\Model\Config;
use TrustComponent\TrustCaptchaMagento2\Model\Validation\Validator;

class ValidateAction implements ObserverInterface
{
    private const MAP = [
        'customer_account_loginpost'          => 'login',
        'customer_account_createpost'         => 'register',
        'customer_account_forgotpasswordpost' => 'forgot',
        'contact_index_post'                  => 'contact',
        'review_product_post'                 => 'review',
        'sendfriend_product_sendmail'         => 'sendfriend',
        'wishlist_index_sharepost'            => 'wishlist_share',
        'sales_guest_formpost'                => 'order_lookup',
    ];

    public function __construct(
        private RequestInterface $request,
        private ActionFlag $actionFlag,
        private RedirectInterface $redirect,
        private UrlInterface $url,
        private ManagerInterface $messages,
        private Config $cfg,
        private Validator $validator
    ) {}

    public function execute(Observer $observer)
    {
        if ($this->cfg->isNativeMode()) {
            return;
        }
        if (!$this->cfg->isEnabled()) {
            return;
        }

        $full = strtolower($this->request->getFullActionName());
        $key  = self::MAP[$full] ?? null;
        if (!$key || !$this->cfg->isFormEnabled($key)) {
            return;
        }

        $token = (string) (
        $this->request->getParam('tc-verification-token')
            ?: $this->request->getParam('trustcaptcha_token')
            ?: $this->request->getParam('verificationToken')
                ?: $this->request->getParam('verification_token')
        );

        $remoteIp = $this->request->getServer('REMOTE_ADDR');
        $res = $this->validator->validate($token, $remoteIp);
        if ($res['ok']) {
            return;
        }

        $this->messages->addErrorMessage(__('CAPTCHA verification failed. ' . $res['message']));

        $controller = $observer->getControllerAction();
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);

        $referer = (string) $this->request->getServer('HTTP_REFERER');
        $back = $referer ?: $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $this->redirect->redirect($controller->getResponse(), $back);
    }
}
