<?php
use Secupay\Sdk\Service\TransactionInvoiceService;

require_once __DIR__ . '/SecupaySdkHelper.php';

$client = SecupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionInvoiceService($client);
$transactionInvoice = $service->read($spaceId, SdkRestApi::getParam('id'));

return SecupaySdkHelper::convertData($transactionInvoice);