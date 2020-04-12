<?php

namespace App\Traits;

use App\PaymentMethods\BasePaymentMethod;
use Illuminate\Validation\Rule;

trait WithPaymentMethod
{

    public function paymentMethodRules()
    {
        return [
            'payment_method' => [Rule::in(BasePaymentMethod::PAYMENT_METHODS)],
        ];
    }

    public function getPaymentMethod()
    {
        return BasePaymentMethod::resolveByName($this->payment_method);
    }
}
