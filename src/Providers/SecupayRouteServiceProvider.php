<?php
namespace secupay\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class secupayRouteServiceProvider extends RouteServiceProvider
{

    /**
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->post('secupay/update-transaction', 'secupay\Controllers\PaymentNotificationController@updateTransaction');
        $router->get('secupay/fail-transaction/{id}', 'secupay\Controllers\PaymentProcessController@failTransaction')->where('id', '\d+');
        $router->post('secupay/pay-order', 'secupay\Controllers\PaymentProcessController@payOrder');
        $router->get('secupay/download-invoice/{id}', 'secupay\Controllers\PaymentTransactionController@downloadInvoice')->where('id', '\d+');
        $router->get('secupay/download-packing-slip/{id}', 'secupay\Controllers\PaymentTransactionController@downloadPackingSlip')->where('id', '\d+');
    }
}
