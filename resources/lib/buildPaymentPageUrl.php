<?php
use Secupay\Sdk\Service\TransactionPaymentPageService;

require_once __DIR__ . '/SecupaySdkHelper.php';

$spaceId = SdkRestApi::getParam('spaceId');
$id = SdkRestApi::getParam('id');

$client = SecupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));
$service = new TransactionPaymentPageService($client);
return $service->paymentPageUrl($spaceId, $id);