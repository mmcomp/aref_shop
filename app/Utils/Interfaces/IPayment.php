<?php
namespace App\Utils\Interfaces;

use Illuminate\Http\JsonResponse;

interface IPayment
{
    public static function pay(Object $order): JsonResponse;
    public static function verify(Object $order);
    public static function settle(Object $order);
}