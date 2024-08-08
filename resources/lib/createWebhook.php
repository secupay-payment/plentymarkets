<?php
use Secupay\Sdk\Model\WebhookUrlCreate;
use Secupay\Sdk\Model\WebhookListenerCreate;
use Secupay\Sdk\Service\WebhookUrlService;
use Secupay\Sdk\Service\WebhookListenerService;

require_once __DIR__ . '/SecupaySdkHelper.php';

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
    \Secupay\Sdk\Model\TransactionState::AUTHORIZED,
    \Secupay\Sdk\Model\TransactionState::DECLINE,
    \Secupay\Sdk\Model\TransactionState::FAILED,
    \Secupay\Sdk\Model\TransactionState::FULFILL,
    \Secupay\Sdk\Model\TransactionState::VOIDED,
    \Secupay\Sdk\Model\TransactionState::COMPLETED
], 'update-transaction');
$webhookEntities[] = new WebhookEntity(1472041816898, 'Transaction Invoice', [
    \Secupay\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE,
    \Secupay\Sdk\Model\TransactionInvoiceState::PAID,
    \Secupay\Sdk\Model\TransactionInvoiceState::DERECOGNIZED
], 'update-transaction-invoice');
$webhookEntities[] = new WebhookEntity(1472041839405, 'Refund', [
    \Secupay\Sdk\Model\RefundState::SUCCESSFUL,
    \Secupay\Sdk\Model\RefundState::FAILED
]);

$client = SecupaySdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));
$spaceId = SdkRestApi::getParam('spaceId');

$webhookUrlService = new WebhookUrlService($client);
$webhookListenerService = new WebhookListenerService($client);

$query = new \Secupay\Sdk\Model\EntityQuery();
$query->setNumberOfEntities(1);
$filter = new \Secupay\Sdk\Model\EntityQueryFilter();
$filter->setType(\Secupay\Sdk\Model\EntityQueryFilterType::_AND);
$filter->setChildren([
    SecupaySdkHelper::createEntityFilter('url', SdkRestApi::getParam('notificationUrl')),
    SecupaySdkHelper::createEntityFilter('state', \Secupay\Sdk\Model\CreationEntityState::ACTIVE)
]);
$query->setFilter($filter);
$webhookResult = $webhookUrlService->search($spaceId, $query);
if (empty($webhookResult)) {
    $webhookUrlRequest = new WebhookUrlCreate();
    $webhookUrlRequest->setState(\Secupay\Sdk\Model\CreationEntityState::ACTIVE);
    $webhookUrlRequest->setName('plentymarkets ' . SdkRestApi::getParam('storeId'));
    $webhookUrlRequest->setUrl(SdkRestApi::getParam('notificationUrl'));
    $webhookUrl = $webhookUrlService->create($spaceId, $webhookUrlRequest);
} else {
    $webhookUrl = $webhookResult[0];
}

$query = new \Secupay\Sdk\Model\EntityQuery();
$filter = new \Secupay\Sdk\Model\EntityQueryFilter();
$filter->setType(\Secupay\Sdk\Model\EntityQueryFilterType::_AND);
$filter->setChildren([
    SecupaySdkHelper::createEntityFilter('state', \Secupay\Sdk\Model\CreationEntityState::ACTIVE),
    SecupaySdkHelper::createEntityFilter('url.id', $webhookUrl->getId())
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
        $webhookListenerRequest->setState(\Secupay\Sdk\Model\CreationEntityState::ACTIVE);
        $webhookListenerRequest->setEntity($webhookEntity->getId());
        $webhookListenerRequest->setEntityStates($webhookEntity->getStates());
        $webhookListenerRequest->setName('plentymarkets ' . SdkRestApi::getParam('storeId') . ' ' . $webhookEntity->getName());
        $webhookListenerRequest->setUrl($webhookUrl);

        $webhookListenerService->create($spaceId, $webhookListenerRequest);
    }
}