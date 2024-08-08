<?php
namespace Secupay\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class SecupayRouteServiceProvider extends RouteServiceProvider
{

    /**
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->post('secupay/update-transaction', 'Secupay\Controllers\PaymentNotificationController@updateTransaction');
        $router->get('secupay/fail-transaction/{id}', 'Secupay\Controllers\PaymentProcessController@failTransaction')->where('id', '\d+');
        $router->post('secupay/pay-order', 'Secupay\Controllers\PaymentProcessController@payOrder');
        $router->get('secupay/download-invoice/{id}', 'Secupay\Controllers\PaymentTransactionController@downloadInvoice')->where('id', '\d+');
        $router->get('secupay/download-packing-slip/{id}', 'Secupay\Controllers\PaymentTransactionController@downloadPackingSlip')->where('id', '\d+');
    }
}