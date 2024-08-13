<?php
namespace secupay\Providers;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use secupay\Helper\PaymentHelper;
use secupay\Helper\secupayServiceProviderHelper;
use secupay\Services\PaymentService;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use secupay\Methods\CreditDebitCardPaymentMethod;
use secupay\Methods\InvoicePaymentMethod;
use secupay\Methods\OnlineBankingPaymentMethod;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use secupay\Methods\AlipayPaymentMethod;
use secupay\Methods\BankTransferPaymentMethod;
use secupay\Methods\CashuPaymentMethod;
use secupay\Methods\DaoPayPaymentMethod;
use secupay\Methods\DirectDebitSepaPaymentMethod;
use secupay\Methods\DirectDebitUkPaymentMethod;
use secupay\Methods\EpsPaymentMethod;
use secupay\Methods\GiropayPaymentMethod;
use secupay\Methods\IDealPaymentMethod;
use secupay\Methods\MasterPassPaymentMethod;
use secupay\Methods\PayboxPaymentMethod;
use secupay\Methods\PaydirektPaymentMethod;
use secupay\Methods\PaylibPaymentMethod;
use secupay\Methods\PayPalPaymentMethod;
use secupay\Methods\PaysafecardPaymentMethod;
use secupay\Methods\PoliPaymentMethod;
use secupay\Methods\Przelewy24PaymentMethod;
use secupay\Methods\QiwiPaymentMethod;
use secupay\Methods\SkrillPaymentMethod;
use secupay\Methods\SofortBankingPaymentMethod;
use secupay\Methods\TenpayPaymentMethod;
use secupay\Methods\TrustlyPaymentMethod;
use secupay\Methods\TwintPaymentMethod;
use secupay\Procedures\RefundEventProcedure;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\Cron\Services\CronContainer;
use secupay\Services\WebhookCronHandler;
use secupay\Contracts\WebhookRepositoryContract;
use secupay\Repositories\WebhookRepository;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;

class secupayServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->getApplication()->register(secupayRouteServiceProvider::class);
        $this->getApplication()->bind(WebhookRepositoryContract::class, WebhookRepository::class);
        $this->getApplication()->bind(RefundEventProcedure::class);
    }

    /**
     * Boot services of the secupay plugin.
     *
     * @param PaymentMethodContainer $payContainer
     */
    public function boot(
        PaymentMethodContainer $payContainer,
        EventProceduresService $eventProceduresService,
        CronContainer $cronContainer,
        secupayServiceProviderHelper $secupayServiceProviderHelper,
        PaymentService $paymentService
    ) {
        $this->registerPaymentMethod($payContainer, 1457546097615, AlipayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097602, BankTransferPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1477573906453, CashuPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097597, CreditDebitCardPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1477574926155, DaoPayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097601, DirectDebitSepaPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1464254757862, DirectDebitUkPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097609, EpsPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097610, GiropayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1461674005576, IDealPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097598, InvoicePaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097621, MasterPassPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1460954915005, OnlineBankingPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1484231986107, PayboxPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097640, PaydirektPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1476259715349, PaylibPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097613, PayPalPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097612, PaysafecardPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097618, PoliPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097617, Przelewy24PaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097616, QiwiPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097614, SkrillPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097603, SofortBankingPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1477574502344, TenpayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097619, TrustlyPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097639, TwintPaymentMethod::class);

        // Register Refund Event Procedure
        $eventProceduresService->registerProcedure('plentysecupay', ProcedureEntry::PROCEDURE_GROUP_ORDER, [
            'de' => 'Rückzahlung der secupay-Zahlung',
            'en' => 'Refund the secupay payment'
        ], 'secupay\Procedures\RefundEventProcedure@run');

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
