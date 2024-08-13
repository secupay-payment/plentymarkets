<?php
use secupay\Sdk\Model\LineItemReductionCreate;
use secupay\Sdk\Model\RefundCreate;
use secupay\Sdk\Service\RefundService;
use secupay\Sdk\ApiClient;

require_once __DIR__ . '/secupaySdkHelper.php';

/**
 *
 * @param array $refundOrder
 * @param array $orderItems
 * @param boolean $isNet
 * @return \secupay\Sdk\Model\LineItemReductionCreate[]
 */
function getReductions($refundOrder, $orderItems, $isNet)
{
    $reductions = [];
    foreach ($refundOrder['orderItems'] as $item) {
        $unitPriceReduction = 0;
        if (isset($item['references'][0]['referenceOrderItemId']) && $item['quantity'] != 0) {
            $orderItemId = $item['references'][0]['referenceOrderItemId'];
            if ($orderItemId && isset($orderItems[$orderItemId])) {
                $orderItem = $orderItems[$orderItemId];
                if ($orderItem['quantity'] != 0) {
                    $orderItemUnitPrice = secupaySdkHelper::roundAmount(($isNet ? $orderItem['amounts'][0]['priceNet'] : $orderItem['amounts'][0]['priceGross']) / $orderItem['quantity']);
                    $itemUnitPrice = secupaySdkHelper::roundAmount(($isNet ? $item['amounts'][0]['priceNet'] : $item['amounts'][0]['priceGross']) / $item['quantity']);
                    if ($orderItemUnitPrice > $itemUnitPrice) {
                        $unitPriceReduction = $itemUnitPrice;
                    }
                }
            }
        }
        switch ($item['typeId']) {
            case 1: // Variation
                $reduction = new LineItemReductionCreate();
                $reduction->setLineItemUniqueId($item['itemVariationId']);
                $reduction->setQuantityReduction($unitPriceReduction == 0 ? $item['quantity'] : 0);
                $reduction->setUnitPriceReduction($unitPriceReduction);
                $reductions[] = $reduction;
                break;
            case 4: // Promotional Coupon
                $reduction = new LineItemReductionCreate();
                $reduction->setLineItemUniqueId('coupon-discount');
                $reduction->setQuantityReduction($unitPriceReduction == 0 ? $item['quantity'] : 0);
                $reduction->setUnitPriceReduction($unitPriceReduction);
                $reductions[] = $reduction;
                break;
            case 6: // Shipping Costs
                $reduction = new LineItemReductionCreate();
                $reduction->setLineItemUniqueId('shipping');
                $reduction->setQuantityReduction($unitPriceReduction == 0 ? $item['quantity'] : 0);
                $reduction->setUnitPriceReduction($unitPriceReduction);
                $reductions[] = $reduction;
                break;
            case 7: // Payment Surcharge
                $reduction = new LineItemReductionCreate();
                $reduction->setLineItemUniqueId('payment-fee');
                $reduction->setQuantityReduction($unitPriceReduction == 0 ? $item['quantity'] : 0);
                $reduction->setUnitPriceReduction($unitPriceReduction);
                $reductions[] = $reduction;
                break;
            default:
                // TODO: Handle more cases:
                // VARIATION = 1
                // ITEM_BUNDLE = 2
                // BUNDLE_COMPONENT = 3
                // PROMOTIONAL_COUPON = 4
                // GIFT_CARD = 5
                // SHIPPING_COSTS = 6
                // PAYMENT_SURCHARGE = 7
                // GIFT_WRAP = 8
                // UNASSIGEND_VARIATION = 9
                // DEPOSIT = 10
                // ORDER = 11
                break;
        }
    }
    return $reductions;
}

/**
 *
 * @param ApiClient $apiClient
 * @param int $spaceId
 * @param int $transactionId
 * @return \secupay\Sdk\Model\TransactionInvoice
 */
function getTransactionInvoice($apiClient, $spaceId, $transactionId)
{
    $query = new \secupay\Sdk\Model\EntityQuery();

    $filter = new \secupay\Sdk\Model\EntityQueryFilter();
    $filter->setType(\secupay\Sdk\Model\EntityQueryFilterType::_AND);
    $filter->setChildren(array(
        secupaySdkHelper::createEntityFilter('state', \secupay\Sdk\Model\TransactionInvoiceState::CANCELED, \secupay\Sdk\Model\CriteriaOperator::NOT_EQUALS),
        secupaySdkHelper::createEntityFilter('completion.lineItemVersion.transaction.id', $transactionId)
    ));
    $query->setFilter($filter);

    $query->setNumberOfEntities(1);

    $invoiceService = new \secupay\Sdk\Service\TransactionInvoiceService($apiClient);
    $result = $invoiceService->search($spaceId, $query);
    if (! empty($result)) {
        return $result[0];
    } else {
        throw new Exception('The transaction invoice could not be found.');
    }
}

/**
 *
 * @param RefundService $refundService
 * @param int $spaceId
 * @param int $transactionId
 * @return \secupay\Sdk\Model\Refund
 */
function getLastSuccessfulRefund($refundService, $spaceId, $transactionId)
{
    $query = new \secupay\Sdk\Model\EntityQuery();

    $filter = new \secupay\Sdk\Model\EntityQueryFilter();
    $filter->setType(\secupay\Sdk\Model\EntityQueryFilterType::_AND);
    $filters = [
        secupaySdkHelper::createEntityFilter('state', \secupay\Sdk\Model\RefundState::SUCCESSFUL),
        secupaySdkHelper::createEntityFilter('transaction.id', $transactionId)
    ];
    $filter->setChildren($filters);
    $query->setFilter($filter);

    $query->setOrderBys([
        secupaySdkHelper::createEntityOrderBy('createdOn', \secupay\Sdk\Model\EntityQueryOrderByType::DESC)
    ]);

    $query->setNumberOfEntities(1);

    $result = $refundService->search($spaceId, $query);
    if (! empty($result)) {
        return $result[0];
    } else {
        return false;
    }
}

/**
 *
 * @param ApiClient $apiClient
 * @param RefundService $refundService
 * @param int $spaceId
 * @param int $transactionId
 * @return \secupay\Sdk\Model\LineItem[]
 */
function getBaseLineItems($apiClient, $refundService, $spaceId, $transactionId)
{
    $lastSuccessfulRefund = getLastSuccessfulRefund($refundService, $spaceId, $transactionId);
    if ($lastSuccessfulRefund) {
        return $lastSuccessfulRefund->getReducedLineItems();
    } else {
        return getTransactionInvoice($apiClient, $spaceId, $transactionId)->getLineItems();
    }
}

/**
 *
 * @param float $refundAmount
 * @param \secupay\Sdk\Model\LineItemReductionCreate[] $reductions
 * @param ApiClient $apiClient
 * @param RefundService $refundService
 * @param int $spaceId
 * @param int $transactionId
 * @return \secupay\Sdk\Model\LineItemReductionCreate[]
 */
function fixReductions($refundAmount, $reductions, $apiClient, $refundService, $spaceId, $transactionId)
{
    $baseLineItems = getBaseLineItems($apiClient, $refundService, $spaceId, $transactionId);
    $reductionAmount = secupaySdkHelper::getReductionAmount($baseLineItems, $reductions);

    if ($reductionAmount != $refundAmount) {
        $fixedReductions = [];
        $baseAmount = secupaySdkHelper::calculateLineItemTotalAmount($baseLineItems);
        if ($baseAmount == 0) {
            throw new \Exception('There are no line items left that can be refunded on the transaction ' . $transactionId . ' in space ' . $spaceId . '.');
        }
        $rate = $refundAmount / $baseAmount;
        foreach ($baseLineItems as $lineItem) {
            if ($lineItem->getQuantity() > 0) {
                $reduction = new \secupay\Sdk\Model\LineItemReductionCreate();
                $reduction->setLineItemUniqueId($lineItem->getUniqueId());
                $reduction->setQuantityReduction(0);
                $reduction->setUnitPriceReduction(secupaySdkHelper::roundAmount($lineItem->getAmountIncludingTax() * $rate / $lineItem->getQuantity()));
                $fixedReductions[] = $reduction;
            }
        }
        return $fixedReductions;
    } else {
        return $reductions;
    }
}

$client = secupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$refundOrder = SdkRestApi::getParam('refundOrder');

$refundRequest = new RefundCreate();

$transactionId = SdkRestApi::getParam('transactionId');
$refundRequest->setTransaction($transactionId);

$refundRequest->setExternalId($refundOrder['id']);

$refundRequest->setType(\secupay\Sdk\Model\RefundType::MERCHANT_INITIATED_ONLINE);

$order = SdkRestApi::getParam('order');
$orderItems = [];
foreach ($order['orderItems'] as $orderItem) {
    $orderItems[$orderItem['id']] = $orderItem;
}

$isNet = isset($order['amounts'][0]['isNet']) ? $order['amounts'][0]['isNet'] : false;

$refundService = new RefundService($client);

$reductions = getReductions($refundOrder, $orderItems, $isNet);
$refundAmount = ($isNet ? $refundOrder['amounts'][0]['netTotal'] : $refundOrder['amounts'][0]['grossTotal']);
$reductions = fixReductions($refundAmount, $reductions, $client, $refundService, $spaceId, $transactionId);
$refundRequest->setReductions($reductions);
$refundResponse = $refundService->refund($spaceId, $refundRequest);

return secupaySdkHelper::convertData($refundResponse);
