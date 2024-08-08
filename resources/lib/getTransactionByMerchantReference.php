<?php
use Secupay\Sdk\Service\TransactionService;
use Secupay\Sdk\Model\EntityQuery;
use Secupay\Sdk\Model\EntityQueryFilter;
use Secupay\Sdk\Model\EntityQueryOrderBy;
use Secupay\Sdk\Model\EntityQueryOrderByType;
use Secupay\Sdk\Model\EntityQueryFilterType;
use Secupay\Sdk\Model\CriteriaOperator;

require_once __DIR__ . '/SecupaySdkHelper.php';

$client = SecupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionService($client);

$merchantReferenceFilter = new EntityQueryFilter();
$merchantReferenceFilter->setType(EntityQueryFilterType::LEAF);
$merchantReferenceFilter->setOperator(CriteriaOperator::EQUALS);
$merchantReferenceFilter->setFieldName('merchantReference');
$merchantReferenceFilter->setValue(SdkRestApi::getParam('merchantReference'));

// To avoid overlapping usage of old IDs we check also that the already
// created transaction is not very long back.
$dateFilter = new EntityQueryFilter();
$dateFilter->setType(EntityQueryFilterType::LEAF);
$dateFilter->setOperator(CriteriaOperator::GREATER_THAN);
$dateFilter->setFieldName('createdOn');
$dateFilter->setValue(date('c', strtotime("-3 months")));

$filter = new EntityQueryFilter();
$filter->setType(EntityQueryFilterType::_AND);
$filter->setChildren([$dateFilter, $merchantReferenceFilter]);

$query = new EntityQuery();
$query->setFilter($filter);
$orderBy = new EntityQueryOrderBy();
$orderBy->setFieldName('createdOn');
$orderBy->setSorting(EntityQueryOrderByType::DESC);
$query->setOrderBys($orderBy);
$query->setNumberOfEntities(1);
$transactions = $service->search($spaceId, $query);

return SecupaySdkHelper::convertData(current($transactions));