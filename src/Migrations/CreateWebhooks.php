<?php
namespace Secupay\Migrations;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Secupay\Services\PaymentService;

class CreateWebhooks
{
    /**
     *
     * @var PaymentService
     */
    private $paymentService;

    /**
     * Constructor.
     *
     * @param PaymentService $paymentService
     */
    public function __construct(
        PaymentService $paymentService
    ) {
        $this->paymentService = $paymentService;
    }

    /**
     * Creates the payment methods for the whitelabel plugin.
     */
    public function run()
    {
        $this->paymentService->createWebhook();
    }
}