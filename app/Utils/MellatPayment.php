<?php
namespace App\Utils;

use App\Http\Resources\User\PaymentResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Order;
use SoapClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;

interface Mellat
{
    public function pay():JsonResponse;
}
class MellatPayment implements Mellat{

    /**
     * Request a payment transaction
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay():JsonResponse
    {

        $raiseError = new RaiseError;
        $user_id = Auth::user()->id;
        $order = Order::where('status', 'waiting')->where('users_id', $user_id)->first();
        if ($order) {
            $payment = Payment::where('is_deleted', false)->where('orders_id', $order->id)->where('users_id', $user_id)->where('res_code', null)->first();
            $raiseError->ValidationError($payment, ['payment' => ['processing']]);
            if(!$payment) {
                $payment = Payment::create([
                    'orders_id' => $order->id,
                    'users_id' => $user_id,
                    'price' => $order->amount*10
                 ]);
            } 
            $terminalId = env('MELLAT_TERMINAL_ID');
            $userName = env('MELLAT_USER_NAME');
            $userPassword = env('MELLAT_USER_PASSWORD');
            $orderId = $payment->id.'_'.time();
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
                'amount' => $amount*10,
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
                $resultArray = explode(',', $res->return);
                if ($resultArray[0] == "0") {
                    $payment->res_code = 0;
                    $payment->ref_id = $resultArray[1]; 
                    $payment->save(); 
                    return (new PaymentResource($payment))->additional([
                        'error' => null
                    ])->response()->setStatusCode(200);
                } else {
                    return (new PaymentResource(null))->additional([
                        'errors' => ["bank_error" => [$resultArray[0]]],
                    ])->response()->setStatusCode(406);
                }
    
            } catch (Exception $e) {
                Log::info('fails in PaymentController/pay ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new PaymentResource(null))->additional([
                        'error' => 'fails in PaymentController/pay ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new PaymentResource(null))->additional([
                        'error' => 'fails in PaymentController/pay',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new PaymentResource(null))->additional([
            'errors' => ["order" => ["There is not any waiting order for the loggedIn user!"]]
        ])->response()->setStatusCode(406);
    }
}
