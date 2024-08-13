<?php
use secupay\Sdk\Model\WebhookUrlCreate;
use secupay\Sdk\Model\WebhookListenerCreate;
use secupay\Sdk\Service\WebhookUrlService;
use secupay\Sdk\Service\WebhookListenerService;

require_once __DIR__ . '/secupaySdkHelper.php';

class WebhookEntity
{

    private $id;

    private $name;

    private $states;

    public function __construct($id, $name, array $states)
    {
        $this->id = $id;
        $this->name = $name;
        $this->states = $states;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStates()
    {
        return $this->states;
    }
}

$webhookEntities = [];
$webhookEntities[] = new WebhookEntity(1472041829003, 'Transaction', [
    \secupay\Sdk\Model\TransactionState::AUTHORIZED,
    \secupay\Sdk\Model\TransactionState::DECLINE,
    \secupay\Sdk\Model\TransactionState::FAILED,
    \secupay\Sdk\Model\TransactionState::FULFILL,
    \secupay\Sdk\Model\TransactionState::VOIDED,
    \secupay\Sdk\Model\TransactionState::COMPLETED
], 'update-transaction');
$webhookEntities[] = new WebhookEntity(1472041816898, 'Transaction Invoice', [
    \secupay\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE,
    \secupay\Sdk\Model\TransactionInvoiceState::PAID,
    \secupay\Sdk\Model\TransactionInvoiceState::DERECOGNIZED
], 'update-transaction-invoice');
$webhookEntities[] = new WebhookEntity(1472041839405, 'Refund', [
    \secupay\Sdk\Model\RefundState::SUCCESSFUL,
    \secupay\Sdk\Model\RefundState::FAILED
]);

$client = secupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));
$spaceId = SdkRestApi::getParam('spaceId');

$webhookUrlService = new WebhookUrlService($client);
$webhookListenerService = new WebhookListenerService($client);

$query = new \secupay\Sdk\Model\EntityQuery();
$query->setNumberOfEntities(1);
$filter = new \secupay\Sdk\Model\EntityQueryFilter();
$filter->setType(\secupay\Sdk\Model\EntityQueryFilterType::_AND);
$filter->setChildren([
    secupaySdkHelper::createEntityFilter('url', SdkRestApi::getParam('notificationUrl')),
    secupaySdkHelper::createEntityFilter('state', \secupay\Sdk\Model\CreationEntityState::ACTIVE)
]);
$query->setFilter($filter);
$webhookResult = $webhookUrlService->search($spaceId, $query);
if (empty($webhookResult)) {
    $webhookUrlRequest = new WebhookUrlCreate();
    $webhookUrlRequest->setState(\secupay\Sdk\Model\CreationEntityState::ACTIVE);
    $webhookUrlRequest->setName('plentymarkets ' . SdkRestApi::getParam('storeId'));
    $webhookUrlRequest->setUrl(SdkRestApi::getParam('notificationUrl'));
    $webhookUrl = $webhookUrlService->create($spaceId, $webhookUrlRequest);
} else {
    $webhookUrl = $webhookResult[0];
}

$query = new \secupay\Sdk\Model\EntityQuery();
$filter = new \secupay\Sdk\Model\EntityQueryFilter();
$filter->setType(\secupay\Sdk\Model\EntityQueryFilterType::_AND);
$filter->setChildren([
    secupaySdkHelper::createEntityFilter('state', \secupay\Sdk\Model\CreationEntityState::ACTIVE),
    secupaySdkHelper::createEntityFilter('url.id', $webhookUrl->getId())
]);
$query->setFilter($filter);
$existingListeners = $webhookListenerService->search($spaceId, $query);

foreach ($webhookEntities as $webhookEntity) {
    $exists = false;
    foreach ($existingListeners as $existingListener) {
        if ($existingListener->getEntity() == $webhookEntity->getId()) {
            $exists = true;
        }
    }

    if (! $exists) {
        $webhookListenerRequest = new WebhookListenerCreate();
        $webhookListenerRequest->setState(\secupay\Sdk\Model\CreationEntityState::ACTIVE);
        $webhookListenerRequest->setEntity($webhookEntity->getId());
        $webhookListenerRequest->setEntityStates($webhookEntity->getStates());
        $webhookListenerRequest->setName('plentymarkets ' . SdkRestApi::getParam('storeId') . ' ' . $webhookEntity->getName());
        $webhookListenerRequest->setUrl($webhookUrl);

        $webhookListenerService->create($spaceId, $webhookListenerRequest);
    }
}
