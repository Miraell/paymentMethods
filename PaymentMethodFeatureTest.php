<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\PaymentMethods\BitcoinPaymentMethod;
use App\PaymentMethods\PaxumPaymentMethod;
use Tests\Feature\ApiFeatureTestCase;

class PaymentMethodFeatureTest extends ApiFeatureTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testUpdatePaymentMethodWithoutDetails()
    {
        $data = [
            'payment_method_name' => 'epayments',
        ];
        $this->actingAs($this->user)
            ->put(route('profile.update'), $data)
            ->assertStatus(422);
    }

    public function testUpdateWebmoneyPaymentMethodDetails()
    {
        $data = [
            'payment_method_name' => 'webmoney',
            'payment_method_details' => [
                'webmoneyWallet' => 'Z123456678901'
            ],
        ];
        $this->actingAs($this->user)
            ->put(route('profile.update'), $data)
            ->assertStatus(200);
    }

    public function testUpdateEpaymentsPaymentMethodDetails()
    {
        $epayments = 'epayments';
        $data = [
            'payment_method_name' => $epayments,
            'payment_method_details' => [
                'ePid' => '123-45678'
            ],
        ];
        $this->actingAs($this->user)
            ->put(route('profile.update'), $data)
            ->assertStatus(200);
        $this->assertEquals($epayments, $this->user->payment_method_name);
    }

    public function testUpdatePaxumPaymentMethodDetails()
    {
        $paxum = 'paxum';
        $data = [
            'payment_method_name' => $paxum,
            'payment_method_details' => [
                PaxumPaymentMethod::WALLET_FIELD => 'test@test.ru'
            ],
        ];
        $this->actingAs($this->user)
            ->put(route('profile.update'), $data)
            ->assertStatus(200);
        $this->assertEquals($paxum, $this->user->payment_method_name);
    }

    public function testUpdateBitcoinPaymentMethodDetailsInvalidData()
    {
        $data = [
            'payment_method_name' => BitcoinPaymentMethod::NAME,
            'payment_method_details' => [
                BitcoinPaymentMethod::WALLET_FIELD => '9FZbgi29cpjq2GjdwV8eyHuJJnkLtktZc5',
            ],
        ];
        $this->actingAs($this->user)
            ->put(route('profile.update'), $data)
            ->assertJsonValidationErrors([
                'payment_method_details',
            ]);
    }

    public function testUpdateBitcoinPaymentMethodDetailsValidData()
    {
        $data = [
            'payment_method_name' => BitcoinPaymentMethod::NAME,
            'payment_method_details' => [
                BitcoinPaymentMethod::WALLET_FIELD => '3FZbgi29cpjq2GjdwV8eyHuJJnkLtktZc5',
            ],
        ];
        $this->actingAs($this->user)
            ->put(route('profile.update'), $data)
            ->assertOk();
        $this->assertEquals(BitcoinPaymentMethod::NAME, $this->user->payment_method_name);
        $this->assertEquals(
            '3FZbgi29cpjq2GjdwV8eyHuJJnkLtktZc5',
            $this->user->payment_method_details[BitcoinPaymentMethod::WALLET_FIELD]
        );
    }
}
