<?php
use secupay\Sdk\Service\TransactionPaymentPageService;

require_once __DIR__ . '/secupaySdkHelper.php';

$spaceId = SdkRestApi::getParam('spaceId');
$id = SdkRestApi::getParam('id');

$client = secupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));
$service = new TransactionPaymentPageService($client);
return $service->paymentPageUrl($spaceId, $id);
