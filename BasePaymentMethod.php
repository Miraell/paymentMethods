<?php

namespace App\PaymentMethods;

use App\Models\User;
use App\WithdrawalUser;
use Carbon\CarbonInterface;
use Money\Money;
use Str;

abstract class BasePaymentMethod
{
    const NAME = null;

    protected $min_payout;
    protected $disabled = false;

    const PAYMENT_METHODS = [
        BitcoinPaymentMethod::NAME,
        EpaymentsPaymentMethod::NAME,
        PaxumPaymentMethod::NAME,
        WebmoneyPaymentMethod::NAME,
        WireTransferPaymentMethod::NAME,
    ];

    public function __construct(Money $min_payout)
    {
        $this->min_payout = $min_payout;
    }

    abstract public function calculateFee(Money $money): Money;

    public function availableForPayout(WithdrawalUser $user)
    {
        if ($this->min_payout->isZero() && $user->getBalance()->lessThanOrEqual($this->min_payout)) {
            return false;
        }
        if ($user->getBalance()->lessThan($this->min_payout)) {
            return false;
        }
        return true;
    }

    public function getName()
    {
        return static::NAME;
    }

    // returns QueryBuilder
    public function users()
    {
        return User::where([
            ['payment_method_name', $this->getName()]
        ]);
    }

    public function getUsersForPayout(CarbonInterface $timestamp = null)
    {
        return $this->users()->where('banned', false)->get()
            ->map(function ($user) use ($timestamp) {
                return new WithdrawalUser($user, $timestamp);
            })
            ->filter(function ($user) {
                return $this->availableForPayout($user);
            });
    }

    public static function resolveByName($name)
    {
        $pm = Str::studly($name);
        $class_name = "App\PaymentMethods\\{$pm}PaymentMethod";
        return resolve($class_name);
    }

    public function isDisabled()
    {
        return $this->disabled;
    }
}
