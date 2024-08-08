<?php
use Secupay\Sdk\Service\TransactionService;

require_once __DIR__ . '/SecupaySdkHelper.php';

$client = SecupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionService($client);
$invoiceDocument = $service->getPackingSlip($spaceId, SdkRestApi::getParam('id'));

return SecupaySdkHelper::convertData($invoiceDocument);