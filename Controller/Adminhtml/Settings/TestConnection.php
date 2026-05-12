<?php
namespace TrustComponent\TrustCaptchaMagento2\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use TrustComponent\TrustCaptcha\TrustCaptcha as TrustCaptchaClient;
use TrustComponent\TrustCaptcha\ServerUnreachableException;

class TestConnection extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'TrustComponent_TrustCaptchaMagento2::config';

    /**
     * Documented test fixture from the backend (see PublicVerificationsController.kt,
     * TEST_API_KEY_V2 constant). Hitting test UUID `...0000` via the library with this
     * key yields a deterministic 200 OK — confirms the full library code path, including
     * any proxy settings the plugin may add in the future.
     */
    private const TEST_VERIFICATION_TOKEN = 'eyJ2ZXJpZmljYXRpb25JZCI6IjAwMDAwMDAwLTAwMDAtMDAwMC0wMDAwLTAwMDAwMDAwMDAwMCJ9';
    private const TEST_API_KEY            = 'ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    public function __construct(
        Context $context,
        private JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        // Plug proxy options in here once the plugin gains proxy settings.
        $options = [];

        try {
            $trustCaptcha = new TrustCaptchaClient(self::TEST_API_KEY, $options);
            $trustCaptcha->getVerificationResult(self::TEST_VERIFICATION_TOKEN);

            return $result->setData([
                'ok' => true,
                'message' => 'Connection successful — TrustCaptcha API is reachable.',
            ]);
        } catch (ServerUnreachableException $e) {
            return $result->setData([
                'ok' => false,
                'message' => 'Cannot reach TrustCaptcha. This usually points to a network issue — check your internet connection, DNS, firewall, or proxy settings.',
            ]);
        } catch (\Throwable $e) {
            return $result->setData([
                'ok' => false,
                'message' => 'Reached TrustCaptcha but got an unexpected response: ' . $e->getMessage(),
            ]);
        }
    }
}
