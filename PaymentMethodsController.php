<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentMethodResource;
use App\PaymentMethods\BasePaymentMethod;

class PaymentMethodsController extends Controller
{
    public function index()
    {
        return PaymentMethodResource::collection(
            collect(BasePaymentMethod::PAYMENT_METHODS)->mapWithKeys(function ($payment_method_name) {
                return [$payment_method_name =>  BasePaymentMethod::resolveByName($payment_method_name)];
            })
        );
    }
}
