<?php
namespace Secupay\Services;

use Plenty\Modules\Cron\Contracts\CronHandler;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;
use Secupay\Contracts\WebhookRepositoryContract;
use Secupay\Helper\PaymentHelper;
use Secupay\Models\Webhook;

class WebhookCronHandler extends CronHandler
{

    use Loggable;

    /**
     *
     * @var ConfigRepository
     */
    private $config;

    /**
     *
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     *
     * @var PaymentService
     */
    private $paymentService;

    /**
     *
     * @var SecupaySdkService
     */
    private $sdkService;

    /**
     *
     * @var WebhookRepositoryContract
     */
    private $webhookRepository;

    /**
     * PaymentController constructor.
     *
     * @param ConfigRepository $config
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param SecupaySdkService $sdkService
     * @param WebhookRepositoryContract $webhookRepository
     */
    public function __construct(ConfigRepository $config, PaymentHelper $paymentHelper, PaymentService $paymentService, SecupaySdkService $sdkService, WebhookRepositoryContract $webhookRepository)
    {
        $this->config = $config;
        $this->paymentHelper = $paymentHelper;
        $this->paymentService = $paymentService;
        $this->sdkService = $sdkService;
        $this->webhookRepository = $webhookRepository;
    }

    public function handle()
    {
        foreach ($this->webhookRepository->getWebhookList() as $webhook) {
            try {
                $this->getLogger(__METHOD__)->info('processWebhook', $webhook);
                $result = $this->processWebhook($webhook);
                if ($result) {
                    $this->webhookRepository->deleteWebhook($webhook->id);
                }
            } catch (\Exception $e) {
                $this->getLogger(__METHOD__)->error('The webhook processing failed.', $e);
            }
        }
    }

    protected function processWebhook(Webhook $webhook)
    {
        $this->getLogger(__METHOD__)->error('Debug Webhook. Step 1.', $webhook);
        if (strtolower($webhook->listenerEntityTechnicalName) == 'transaction') {
            $this->getLogger(__METHOD__)->error('Debug Webhook. Step 2.', $webhook);
            $transactionId = $webhook->entityId;
            $transaction = $this->sdkService->call('getTransaction', [
                'id' => $transactionId,
                'spaceId' => $webhook->spaceId
            ]);
            if (empty($transaction)) {
                $this->getLogger(__METHOD__)->error('The transaction was not found.', $transactionId);
                return true;
            }
            if (is_array($transaction) && isset($transaction['error'])) {
                throw new \Exception($transaction['error_msg']);
            }
            $this->getLogger(__METHOD__)->error('Debug Webhook. Step 3.', $webhook);
            return $this->paymentHelper->updatePlentyPayment($transaction);
        } elseif (strtolower($webhook->listenerEntityTechnicalName) == 'transactioninvoice') {
            $transactionInvoiceId = $webhook->entityId;
            $transactionInvoice = $this->sdkService->call('getTransactionInvoice', [
                'id' => $transactionInvoiceId,
                'spaceId' => $webhook->spaceId
            ]);
            if (empty($transactionInvoice)) {
                $this->getLogger(__METHOD__)->error('The transaction invoice was not found.', $transactionInvoiceId);
                return true;
            }
            if (is_array($transactionInvoice) && isset($transactionInvoice['error'])) {
                throw new \Exception($transactionInvoice['error_msg']);
            }
            return $this->paymentHelper->updateInvoice($transactionInvoice);
        } elseif (strtolower($webhook->listenerEntityTechnicalName) == 'refund') {
            $refundId = $webhook->entityId;
            $refund = $this->sdkService->call('getRefund', [
                'id' => $refundId,
                'spaceId' => $webhook->spaceId
            ]);
            if (empty($refund)) {
                $this->getLogger(__METHOD__)->error('The refund was not found.', $refundId);
                return true;
            }
            if (is_array($refund) && isset($refund['error'])) {
                throw new \Exception($refund['error_msg']);
            }
            return $this->paymentHelper->updateRefund($refund);
        }
    }
}
