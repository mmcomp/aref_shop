<?php

namespace App\Utils;

use App\Http\Resources\User\PaymentResource;
use Illuminate\Http\JsonResponse;
use App\Models\Payment;
use App\Utils\Interfaces\IPayment;
use SoapClient;
use Exception;
use Log;


class MellatPayment implements IPayment
{

    /**
     * Request a payment transaction
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function pay(Object $order): JsonResponse
    {

        $raiseError = new RaiseError;
        $payment = Payment::where('is_deleted', false)->where('orders_id', $order->id)->where('users_id', $order->users_id)->where('res_code', null)->first();
        $raiseError->ValidationError($payment, ['payment' => ['processing']]);
        if (!$payment) {
            $payment = Payment::create([
                'orders_id' => $order->id,
                'users_id' => $order->users_id,
                'price' => $order->amount * 10,
                'bank_orders_id' => time()
            ]);
        }
        $terminalId = env('MELLAT_TERMINAL_ID');
        $userName = env('MELLAT_USER_NAME');
        $userPassword = env('MELLAT_USER_PASSWORD');
        $orderId = $payment->bank_orders_id;
        $amount = $order->amount;
        $localDate = date("Ymd");
        $localTime = date("His");
        $additionalData = "";
        $callBackUrl = env('MELLAT_CALL_BACK_URL');
        $data = array(
            'terminalId' => $terminalId,
            'userName' => $userName,
            'userPassword' => $userPassword,
            'orderId' => $orderId,
            'amount' => $amount * 10,
            'localDate' => $localDate,
            'localTime' => $localTime,
            'additionalData' => $additionalData,
            'callBackUrl' => $callBackUrl,
            'payerId' => 0
        );
        try {
            $soapClient = new SoapClient(env('MELLAT_WSDL'));
            $res = $soapClient->bpPayrequest($data);
            $payment->pay_output = $res->return;
            $payment->save();
            $order->status = "processing";
            $order->save();
            $resultArray = explode(',', $res->return);
            if ($resultArray[0] == "0") {
                $payment->res_code = 0;
                $payment->ref_id = $resultArray[1];
                $payment->status = "pay";
                $payment->save();
                return (new PaymentResource($payment))->additional([
                    'error' => null
                ])->response()->setStatusCode(200);
            } else {
                $payment->res_code = $res->return;
                $payment->status = "error";
                $payment->save();
                return (new PaymentResource(null))->additional([
                    'errors' => ["bank_error" => [$resultArray[0]]],
                ])->response()->setStatusCode(406);
            }
        } catch (Exception $e) {
            Log::info('fails in MellatPayment/pay ' . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new PaymentResource(null))->additional([
                    'error' => 'fails in MellatPayment/pay ' . json_encode($e),
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new PaymentResource(null))->additional([
                    'error' => 'fails in MellatPayment/pay',
                ])->response()->setStatusCode(500);
            }
        }
    }
    /**
     * confirm a payment transaction
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function verify(Object $order, Object $payment)
    {

        $terminalId = env('MELLAT_TERMINAL_ID');
        $userName = env('MELLAT_USER_NAME');
        $userPassword = env('MELLAT_USER_PASSWORD');
        $orderId = $payment->bank_orders_id;
        $data = array(
            'terminalId' => $terminalId,
            'userName' => $userName,
            'userPassword' => $userPassword,
            'orderId' => $orderId,
            'saleOrderId' => $orderId,
            'saleReferenceId' => $payment->ref_id
        );

        // Call the SOAP method
        try {
            $soapClient = new SoapClient(env('MELLAT_WSDL'));
            $res = $soapClient->bpVerifyrequest($data);
            if ($res->return == 43 || $res->return == 0) {
                // $payment->sale_order_id = $orderId;
                // $payment->sale_reference_id = $payment->ref_id;
                // $payment->bank_returned = json_encode([
                //    "res_code" => $payment->res_code,
                //    "ref_id" => $payment->ref_id,
                //    "sale_order_id" => $orderId,
                //    "sale_reference_id" => $payment->res_code
                // ]);
                // $payment->save();
                return (new PaymentResource($payment))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            }
        } catch (Exception $e) {
            Log::info('fails in MellatPayment/verify ' . json_encode($e));
        }
    }
    /**
     * deposit request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function settle(Object $order, Object $payment)
    {
        
        $terminalId = env('MELLAT_TERMINAL_ID');;
        $userName = env('MELLAT_USER_NAME');
        $userPassword = env('MELLAT_USER_PASSWORD');;
        $orderId = $payment->bank_orders_id;
        $settleSaleReferenceId = $payment->ref_id;

        $data = array(
            'terminalId' => $terminalId,
            'userName' => $userName,
            'userPassword' => $userPassword,
            'orderId' => $orderId,
            'saleOrderId' => $orderId,
            'saleReferenceId' => $settleSaleReferenceId
        );

        // Call the SOAP method
        try {
            $soapClient = new SoapClient(env('MELLAT_WSDL'));
            $res = $soapClient->bpSettlerequest($data);
            // $payment->sale_order_id = $orderId;
            // $payment->sale_reference_id = $payment->ref_id;
            // $payment->save();
            if ($res->return == 0 || $res->return == 45) {
                return (new PaymentResource($payment))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            }
        } catch (Exception $e) {
            Log::info('fails in MellatPayment/settle ' . json_encode($e));
        }
    }
}
