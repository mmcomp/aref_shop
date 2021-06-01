<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\OrderResource;
use App\Http\Resources\User\bpPayRequestResource;
use App\Models\Order;
use Carbon\Carbon;
use SoapClient;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Request a payment transaction
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bpPayRequest()
    {

        $client = new Soapclient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        $namespace = 'http://interfaces.core.sw.bps.com/';
        $user_id = Auth::user()->id;
        $order = Order::where('status', 'waiting')->where('users_id', $user_id)->first();
        if ($order) {
            // Check for an error
            $err = $client->getError();
            if ($err) {
                return (new bpPayRequestResource(null))->additional([
                    'error' => $err,
                ])->response()->setStatusCode(406);
            }

            $terminalId = env('TERMINAL_ID');
            $userName = env('USER_NAME');
            $userPassword = env('USER_PASSWORD');
            $orderId = $order->id;
            $amount = $order->amount;
            $localDate = Carbon::now()->format('‫‪YYYYMMDD‬‬');
            $localTime = Carbon::now()->format('‫‪HH:MM:SS‬‬');
            $additionalData = "";
            $callBackUrl = env('CALL_BACK_URL');
            $payerId = 0;
            $parameters = array(
                'terminalId' => $terminalId,
                'userName' => $userName,
                'userPassword' => $userPassword,
                'orderId' => $orderId,
                'amount' => $amount,
                'localDate' => $localDate,
                'localTime' => $localTime,
                'additionalData' => $additionalData,
                'callBackUrl' => $callBackUrl,
                'payerId' => $payerId
            );

            // Call the SOAP method
            $result = $client->call('bpPayRequest', $parameters, $namespace);
            // Check for a fault
            if ($client->fault) {
                return (new bpPayRequestResource(null))->additional([
                    'error' => $result,
                ])->response()->setStatusCode(406);
            } else {
                // Check for errors
                $resultStr  = $result;
                $err = $client->getError();
                if ($err) {
                    // Display the error
                    return (new bpPayRequestResource(null))->additional([
                        'error' => $err,
                    ])->response()->setStatusCode(406);
                } else {
                    // Display the result
                    $res = explode(',', $resultStr);
                    $ResCode = $res[0];
                    if ($ResCode == "0") {
                        // Update table, Save RefId
                        echo "<script language='javascript' type='text/javascript'>postRefId('" . $res[1] . "');</script>";
                    } else {
                        Log::info('Result code is not zero, it is '. $ResCode);
                        // log error in app
                        // Update table, log the error
                        // Show proper message to user
                    }
                } // end Display the result
            } // end Check for errors
        }
        return (new bpPayRequestResource(null))->additional([
            'error' => 'There is not any waiting orders for the loggedIn user',
        ])->response()->setStatusCode(406);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
