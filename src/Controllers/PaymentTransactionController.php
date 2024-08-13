<?php
namespace secupay\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;
use secupay\Services\secupaySdkService;

class PaymentTransactionController extends Controller
{

    use Loggable;

    /**
     *
     * @var Request
     */
    private $request;

    /**
     *
     * @var Response
     */
    private $response;

    /**
     *
     * @var secupaySdkService
     */
    private $sdkService;

    /**
     * PaymentController constructor.
     *
     * @param Request $request
     * @param Response $response
     * @param secupaySdkService $sdkService
     */
    public function __construct(Request $request, Response $response, secupaySdkService $sdkService)
    {
        $this->request = $request;
        $this->response = $response;
        $this->sdkService = $sdkService;
    }

    public function downloadInvoice(int $id)
    {
        $transaction = $this->sdkService->call('getTransaction', [
            'id' => $id
        ]);
        if (is_array($transaction) && ! isset($transaction['error'])) {
            $invoiceDocument = $this->sdkService->call('getInvoiceDocument', [
                'id' => $id
            ]);
            if (is_array($invoiceDocument) && ! isset($invoiceDocument['error'])) {
                return $this->download($invoiceDocument);
            }
        }
    }

    public function downloadPackingSlip(int $id)
    {
        $transaction = $this->sdkService->call('getTransaction', [
            'id' => $id
        ]);
        if (is_array($transaction) && ! isset($transaction['error'])) {
            $packingSlip = $this->sdkService->call('getPackingSlip', [
                'id' => $id
            ]);
            if (is_array($packingSlip) && ! isset($packingSlip['error'])) {
                return $this->download($packingSlip);
            }
        }
    }

    private function download($document)
    {
        return $this->response->make(base64_decode($document['data']), 200, [
            'Pragma' => 'public',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . $document['title'] . '.pdf',
            'Content-Description' => $document['title']
        ]);
    }
}
