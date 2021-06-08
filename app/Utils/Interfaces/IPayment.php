<?php
namespace App\Utils\Interfaces;

use Illuminate\Http\JsonResponse;

interface IPayment
{
    public static function pay(Object $order): JsonResponse;
    public static function verify(Object $order, Object $payment);
    public static function settle(Object $order, Object $payment);
}