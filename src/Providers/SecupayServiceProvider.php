<?php
namespace Secupay\Providers;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\Cron\Services\CronContainer;
use Secupay\Contracts\WebhookRepositoryContract;
use Secupay\Helper\PaymentHelper;
use Secupay\Helper\SecupayServiceProviderHelper;
use Secupay\Methods\BankTransferPaymentMethod;
use Secupay\Methods\CreditDebitCardPaymentMethod;
use Secupay\Methods\DirectDebitSepaPaymentMethod;
use Secupay\Methods\InvoicePaymentMethod;
use Secupay\Procedures\RefundEventProcedure;
use Secupay\Repositories\WebhookRepository;
use Secupay\Services\PaymentService;
use Secupay\Services\WebhookCronHandler;
use IO\Services\BasketService;

class SecupayServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->getApplication()->register(SecupayRouteServiceProvider::class);
        $this->getApplication()->bind(WebhookRepositoryContract::class, WebhookRepository::class);
        $this->getApplication()->bind(RefundEventProcedure::class);
    }

    /**
     * Boot services of the Secupay plugin.
     *
     * @param PaymentMethodContainer $payContainer
     */
    public function boot(
        PaymentMethodContainer $payContainer,
        EventProceduresService $eventProceduresService,
        CronContainer $cronContainer,
        SecupayServiceProviderHelper $secupayServiceProviderHelper,
        PaymentService $paymentService
    ) {
        $this->registerPaymentMethod($payContainer, 1457546097602, BankTransferPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097597, CreditDebitCardPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097601, DirectDebitSepaPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097598, InvoicePaymentMethod::class);

        // Register Refund Event Procedure
        $eventProceduresService->registerProcedure('plentySecupay', ProcedureEntry::PROCEDURE_GROUP_ORDER, [
            'de' => 'Rückzahlung der Secupay-Zahlung',
            'en' => 'Refund the Secupay payment'
        ], 'Secupay\Procedures\RefundEventProcedure@run');

        $secupayServiceProviderHelper->addExecutePaymentContentEventListener();

        $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, WebhookCronHandler::class);
    }

    private function registerPaymentMethod($payContainer, $id, $class)
    {
        $payContainer->register('secupay::' . $id, $class, [
            AfterBasketChanged::class,
            AfterBasketCreate::class
        ]);
    }
}
