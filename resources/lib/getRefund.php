<?php
use Secupay\Sdk\Service\RefundService;

require_once __DIR__ . '/SecupaySdkHelper.php';

$client = SecupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new RefundService($client);
$refund = $service->read($spaceId, SdkRestApi::getParam('id'));

return SecupaySdkHelper::convertData($refund);