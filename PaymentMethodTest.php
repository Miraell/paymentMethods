<?php

namespace Tests\Unit\PaymentMethods;

use App\Models\BalanceTransaction;
use App\Models\User;
use App\PaymentMethods\BasePaymentMethod;
use App\PaymentMethods\WebmoneyPaymentMethod;
use App\WithdrawalUser;
use MoneyFmt;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    public function testAvailableForPayoutWhenMinPayoutIsZeroAndBalanceIsZero()
    {
        $webmoney = new WebmoneyPaymentMethod(MoneyFmt::make(0), 0);
        $user = factory(User::class)->create(['payment_method_name' => WebmoneyPaymentMethod::NAME]);
        $this->assertFalse($webmoney->availableForPayout(new WithdrawalUser($user)));
    }

    public function testAvailableForPayoutWhenMinPayoutIsZeroAndBalanceIsPositive()
    {
        $webmoney = new WebmoneyPaymentMethod(MoneyFmt::make(0), 0);
        $user = factory(User::class)->create(['payment_method_name' => WebmoneyPaymentMethod::NAME]);
        $user->transactions()->save(factory(BalanceTransaction::class)->state('deposit')->make());
        $this->assertTrue($webmoney->availableForPayout(new WithdrawalUser($user)));
    }

    public function testAvailableForPayoutWhenMinPayoutIsPositiveAndBalanceIsLessThanMinPayout()
    {
        $webmoney = new WebmoneyPaymentMethod(MoneyFmt::make(10), 0);
        $user = factory(User::class)->create(['payment_method_name' => WebmoneyPaymentMethod::NAME]);
        $user->transactions()->save(factory(BalanceTransaction::class)->state('deposit')->make([
            'amount' => MoneyFmt::make(5)
        ]));
        $this->assertFalse($webmoney->availableForPayout(new WithdrawalUser($user)));
    }

    public function testAvailableForPayoutWhenMinPayoutIsPositiveAndBalanceIsEqualsMinPayout()
    {
        $webmoney = new WebmoneyPaymentMethod(MoneyFmt::make(10), 0);
        $user = factory(User::class)->create(['payment_method_name' => WebmoneyPaymentMethod::NAME]);
        $user->transactions()->save(factory(BalanceTransaction::class)->state('deposit')
                ->make(['amount' => MoneyFmt::make(10)]));
        $this->assertTrue($webmoney->availableForPayout(new WithdrawalUser($user)));
    }

    public function testGetUsersForPayoutDoesNotIncludeBannedUser()
    {
        $mock = \Mockery::mock(WebmoneyPaymentMethod::class, [MoneyFmt::make(10), 0])->makePartial();
        $mock->shouldReceive('availableForPayout')->andReturn(true)->zeroOrMoreTimes();

        $user = factory(User::class)->create([
            'payment_method_name' => WebmoneyPaymentMethod::NAME,
        ]);
        $banned_user = factory(User::class)->state('is_banned')->create([
            'payment_method_name' => WebmoneyPaymentMethod::NAME,
        ]);

        $for_payout = $mock->getUsersForPayout();
        // XXX пока не сделан assertContains, используем заглушку
        $for_payout =  new \Illuminate\Database\Eloquent\Collection($for_payout->all());

        $this->assertTrue($for_payout->contains($user));
        $this->assertFalse($for_payout->contains($banned_user));
    }
}
