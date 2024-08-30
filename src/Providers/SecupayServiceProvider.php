<?php
namespace Secupay\Providers;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Secupay\Helper\PaymentHelper;
use Secupay\Helper\SecupayServiceProviderHelper;
use Secupay\Services\PaymentService;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Secupay\Methods\CreditDebitCardPaymentMethod;
use Secupay\Methods\InvoicePaymentMethod;
use Secupay\Methods\OnlineBankingPaymentMethod;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Secupay\Methods\AlipayPaymentMethod;
use Secupay\Methods\BankTransferPaymentMethod;
use Secupay\Methods\CashuPaymentMethod;
use Secupay\Methods\DaoPayPaymentMethod;
use Secupay\Methods\DirectDebitSepaPaymentMethod;
use Secupay\Methods\DirectDebitUkPaymentMethod;
use Secupay\Methods\EpsPaymentMethod;
use Secupay\Methods\GiropayPaymentMethod;
use Secupay\Methods\IDealPaymentMethod;
use Secupay\Methods\MasterPassPaymentMethod;
use Secupay\Methods\PayboxPaymentMethod;
use Secupay\Methods\PaydirektPaymentMethod;
use Secupay\Methods\PaylibPaymentMethod;
use Secupay\Methods\PayPalPaymentMethod;
use Secupay\Methods\PaysafecardPaymentMethod;
use Secupay\Methods\PoliPaymentMethod;
use Secupay\Methods\Przelewy24PaymentMethod;
use Secupay\Methods\QiwiPaymentMethod;
use Secupay\Methods\SkrillPaymentMethod;
use Secupay\Methods\SofortBankingPaymentMethod;
use Secupay\Methods\TenpayPaymentMethod;
use Secupay\Methods\TrustlyPaymentMethod;
use Secupay\Methods\TwintPaymentMethod;
use Secupay\Procedures\RefundEventProcedure;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\Cron\Services\CronContainer;
use Secupay\Services\WebhookCronHandler;
use Secupay\Contracts\WebhookRepositoryContract;
use Secupay\Repositories\WebhookRepository;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;

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
        $eventProceduresService->registerProcedure('plentySecupay', ProcedureEntry::PROCEDURE_GROUP_ORDER, [
            'de' => 'Rückzahlung der Secupay-Zahlung',
            'en' => 'Refund the Secupay payment'
        ], 'Secupay\Procedures\RefundEventProcedure@run');

        $secupayServiceProviderHelper->addExecutePaymentContentEventListener();
        
        $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, \Wallee\Services\WebhookCronHandler::class);
    }

    private function registerPaymentMethod($payContainer, $id, $class)
    {
        $payContainer->register('secupay::' . $id, $class, [
            AfterBasketChanged::class,
            AfterBasketCreate::class
        ]);
    }
}
