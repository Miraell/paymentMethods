<?php

namespace App\Http\Resources;

use App\PaymentMethods\FixedFeePaymentMethod;
use App\PaymentMethods\PercentageFeePaymentMethod;
use Illuminate\Http\Resources\Json\JsonResource;
use MoneyFmt;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->getName(),
            'fee' => $this->getFee(),
            'disabled' => $this->isDisabled(),
        ];
    }

    public function getFee()
    {
        if ($this->resource instanceof PercentageFeePaymentMethod) {
            return  [
                'type' => 'percent',
                'percent' => $this->getFeePercent(),
            ];
        } elseif ($this->resource instanceof FixedFeePaymentMethod) {
            return  [
                'type' => 'fix',
                'amount' => MoneyFmt::format($this->getFeeAmount()),
            ];
        } else {
            return [];
        }
    }
}
