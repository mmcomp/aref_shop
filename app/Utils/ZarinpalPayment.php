<?php

namespace App\Utils;

use App\Http\Resources\User\PaymentResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use App\Models\Payment;
use App\Utils\Interfaces\IPayment;
use SoapClient;
use Exception;
use Log;
use App\Utils\Zarinpal;


class ZarinpalPayment implements IPayment
{

    /**
     * Request a payment transaction
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function pay(Order $order): JsonResponse
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
        $orderId = $payment->bank_orders_id;
        $amount = $order->amount;
        $localDate = date("Ymd");
        $localTime = date("His");
        $additionalData = "";
        try {
            $MerchantID = env('ZARINPAL_ID');
            $Amount 		= $amount;
            $Description 	= "تراکنش زرین پال";
            $Email 			= "";
            $Mobile 		= "";
            $CallbackURL 	= env('ZARINPAL_BACK_URL');
            $ZarinGate 		= false;
            $SandBox 		= false;
            $zp 	= new Zarinpal();
            Log::info('Zarinpal start'."$MerchantID, $Amount, $Description, $Email, $Mobile, $CallbackURL, $SandBox, $ZarinGate");
            $result = $zp->request($MerchantID, $Amount, $Description, $Email, $Mobile, $CallbackURL, $SandBox, $ZarinGate);
            Log::info('Zarinpal Result' .json_encode($result));
            $order->status = "processing";
            $order->save();
            if (isset($result["Status"]) && $result["Status"] == 100){
                $payment->res_code = $result['Authority'];
                $payment->status = "pay";
                $payment->save();
                return (new PaymentResource($payment))->additional([
                    'startpay'=>$result["StartPay"],
                    'Authority'=>$result['Authority'],
                    'error' => null
                ])->response()->setStatusCode(200);
            }
            else{
                $payment->bank_returned = $result["Message"];
                $payment->status = "error";
                $payment->save();
                return (new PaymentResource(null))->additional([
                    'errors' => ["bank_error" => [$result["Message"]]],
                ])->response()->setStatusCode(406);
            }
        } catch (Exception $e) {
            Log::info('fails in Zarinpal/pay ' . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new PaymentResource(null))->additional([
                    'error' => 'fails in Zarinpal/pay ' . json_encode($e),
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new PaymentResource(null))->additional([
                    'error' => 'fails in Zarinpal/pay',
                ])->response()->setStatusCode(500);
            }
        }
    }
    /**
     * confirm a payment transaction
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function verify(Order $order, Payment $payment)
    {

        Log::info('Verifying');
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
            'saleReferenceId' => $payment->sale_reference_id
        );

        // Call the SOAP method
        try {
            $soapClient = new SoapClient(env('MELLAT_WSDL'));
            $res = $soapClient->bpVerifyrequest($data);
            if ($res->return == 43 || $res->return == 0) {
                return [
                    "payment" => $payment,
                    "errors" => null
                ];
            } else {
                Log::info('Error in verify function with code ' . $res->return);
                return [
                    "payment" => $payment,
                    "errors" => $res->return
                ]; 
            }
        } catch (Exception $e) {
            Log::info('fails in MellatPayment/verify ' . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return [
                    "payment" => $payment,
                    "errors" => 'fails in MellatPayment/verify' . json_encode($e)
                ];
            } else if (env('APP_ENV') == 'production') {
                return [
                    "payment" => $payment,
                    "errors" => 'fails in MellatPayment/verify'
                ];
            }
        }
    }
    /**
     * deposit request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function settle(Order $order, Payment $payment)
    {

        Log::info('Settling');
        $terminalId = env('MELLAT_TERMINAL_ID');;
        $userName = env('MELLAT_USER_NAME');
        $userPassword = env('MELLAT_USER_PASSWORD');;
        $orderId = $payment->bank_orders_id;
        $settleSaleReferenceId = $payment->sale_reference_id;

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
            if ($res->return == 0 || $res->return == 45) {
                return [
                    "payment" => $payment,
                    "errors" => null
                ];
            } else {
                Log::info('Error in settle function with code ' . $res->return);
                return [
                    "payment" => $payment,
                    "errors" => $res->return
                ]; 
            }
        } catch (Exception $e) {
            Log::info('fails in MellatPayment/settle ' . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return [
                    "payment" => $payment,
                    "errors" => 'fails in MellatPayment/settle' . json_encode($e),
                ];
            } else if (env('APP_ENV') == 'production') {
                return [
                    "payment" => $payment,
                    "errors" => 'fails in MellatPayment/settle'
                ];
            }
        }
    }
}
