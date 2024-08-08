<?php
namespace Secupay\Methods;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;
use Plenty\Plugin\ConfigRepository;
use Secupay\Services\PaymentService;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;

abstract class AbstractPaymentMethod extends PaymentMethodService
{

    /**
     *
     * @var ConfigRepository
     */
    protected $configRepo;

    /**
     *
     * @var PaymentService
     */
    protected $paymentService;

    /**
     *
     * @var PaymentRepositoryContract
     */
    protected $paymentRepository;

    /**
     * Constructor.
     *
     * @param ConfigRepository $configRepo
     * @param PaymentService $paymentService
     * @param PaymentRepositoryContract $paymentRepository
     */
    public function __construct(ConfigRepository $configRepo, PaymentService $paymentService, PaymentRepositoryContract $paymentRepository)
    {
        $this->configRepo = $configRepo;
        $this->paymentService = $paymentService;
        $this->paymentRepository = $paymentRepository;
    }

    protected function getBaseIconPath()
    {
        switch ($this->configRepo->get('secupay.resource_version')) {
            case 'V1':
                return \Secupay\Services\SecupaySdkService::GATEWAY_BASE_PATH . '/s/' . $this->configRepo->get('secupay.space_id') . '/resource/icon/payment/method/';
            case 'V2':
                return \Secupay\Services\SecupaySdkService::GATEWAY_BASE_PATH . '/s/' . $this->configRepo->get('secupay.space_id') . '/resource/web/image/payment/method/';
            default:
                return \Secupay\Services\SecupaySdkService::GATEWAY_BASE_PATH . '/resource/web/image/payment/method/';
        }
    }

    protected function getImagePath($fileName)
    {
        return $this->getBaseIconPath() . $fileName . '?' . time();
    }

    public function isSwitchableTo($orderId)
    {
        return false;
    }

    public function isSwitchableFrom($orderId)
    {
        return false;
    }

}