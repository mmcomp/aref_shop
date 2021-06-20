<?php
namespace App\Utils\Interfaces;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

interface IPayment
{
    public static function pay(Order $order): JsonResponse;
    public static function verify(Order $order, Payment $payment);
    public static function settle(Order $order, Payment $payment);
}
