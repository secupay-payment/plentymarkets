<?php
use secupay\Sdk\Service\RefundService;

require_once __DIR__ . '/secupaySdkHelper.php';

$client = secupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new RefundService($client);
$refund = $service->read($spaceId, SdkRestApi::getParam('id'));

return secupaySdkHelper::convertData($refund);
