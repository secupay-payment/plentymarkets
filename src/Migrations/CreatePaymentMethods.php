<?php
namespace Secupay\Migrations;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Secupay\Helper\PaymentHelper;

class CreatePaymentMethods
{

    /**
     *
     * @var PaymentMethodRepositoryContract
     */
    private $paymentMethodRepositoryContract;

    /**
     *
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * Constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepositoryContract
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepositoryContract, PaymentHelper $paymentHelper)
    {
        $this->paymentMethodRepositoryContract = $paymentMethodRepositoryContract;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Creates the payment methods for the Secupay plugin.
     */
    public function run()
    {
        $this->createPaymentMethod(1457546097602, 'Bank Transfer');
        $this->createPaymentMethod(1457546097597, 'Credit / Debit Card');
        $this->createPaymentMethod(1457546097601, 'Direct Debit (SEPA)');
        $this->createPaymentMethod(1457546097598, 'Invoice');
        $this->createPaymentMethod(1460954915005, 'Online Banking');
    }

    private function createPaymentMethod($id, $name)
    {
        if ($this->paymentHelper->getPaymentMopId($id) == 'no_paymentmethod_found') {
            $this->paymentMethodRepositoryContract->createPaymentMethod([
                'pluginKey' => 'secupay',
                'paymentKey' => (string) $id,
                'name' => $name
            ]);
        }
    }
}