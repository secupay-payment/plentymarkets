<?php
namespace Secupay\Services;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

class SecupaySdkService
{

    const GATEWAY_BASE_PATH = 'https://app-wallee.com';

    /**
     *
     * @var LibraryCallContract
     */
    private $libCall;

    /**
     *
     * @var ConfigRepository
     */
    private $config;

    /**
     *
     * @param LibraryCallContract $libCall
     * @param ConfigRepository $config
     */
    public function __construct(LibraryCallContract $libCall, ConfigRepository $config)
    {
        $this->libCall = $libCall;
        $this->config = $config;
    }

    /**
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function call(string $method, array $parameters)
    {
        $parameters['gatewayBasePath'] = 'https://app-wallee.com';
        $parameters['apiUserId'] = $this->config->get('secupay.api_user_id');
        $parameters['apiUserKey'] = $this->config->get('secupay.api_user_key');
        if (!isset($parameters['spaceId']) || $parameters['spaceId'] == 0) {
            $parameters['spaceId'] = $this->config->get('secupay.space_id');
        }
        return $this->libCall->call('secupay::' . $method, $parameters);
    }
}
