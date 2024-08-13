<?php
use secupay\Sdk\Service\TransactionService;

require_once __DIR__ . '/secupaySdkHelper.php';

$client = secupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionService($client);
$transaction = $service->read($spaceId, SdkRestApi::getParam('id'));

return secupaySdkHelper::convertData($transaction);
