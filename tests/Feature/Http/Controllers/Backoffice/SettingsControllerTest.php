<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: ['access-backoffice', 'manage-settings']);
    }

    public function test_backoffice_settings_show_route()
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAs($this->user)->get(route('backoffice.settings.show'));

        $response->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $assertableInertia) {
            $assertableInertia->hasAll('settings', 'groups', 'params', 'paymentMethods');
        });
    }

    public function test_backoffice_settings_update_route()
    {
        $this->withoutExceptionHandling();

        $payload = [
            'site_name' => config('app.name'),
            'email' => $this->faker->email(),
            'phone' => $this->faker->e164PhoneNumber(),
            'location' => $this->faker->streetAddress(),
            'show_categories_banner' => $this->faker->boolean(),
            'facebook_link' => $this->faker->url(),
            'tiktok_link' => $this->faker->url(),
            'instagram_link' => $this->faker->url(),
            'whatsapp_link' => $this->faker->url(),
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.settings.update'), $payload);

        /** @var SettingsService $settingsService */
        $settingsService = app(SettingsService::class);

        $settings = $settingsService->get();

        $this->assertEquals($payload['site_name'], $settings['site_name']);

        $this->assertEquals($payload['email'], $settings['email']);

        $this->assertEquals($payload['phone'], $settings['phone']);

        $this->assertEquals($payload['location'], $settings['location']);

        $this->assertEquals($payload['show_categories_banner'], $settings['show_categories_banner']);

        $this->assertEquals($payload['facebook_link'], $settings['facebook_link']);

        $this->assertEquals($payload['tiktok_link'], $settings['tiktok_link']);

        $this->assertEquals($payload['instagram_link'], $settings['instagram_link']);

        $this->assertEquals($payload['whatsapp_link'], $settings['whatsapp_link']);

        $response->assertRedirect();
    }

    public function test_backoffice_settings_update_receipt_settings(): void
    {
        $payload = [
            'receipt_footer' => $this->faker->text(100),
            'show_receipt_footer' => $this->faker->boolean(),
            'show_receipt_header' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.settings.update', [
            'group' => 'receipt',
        ]), $payload);

        $settings = app(SettingsService::class)->get('receipt');

        $this->assertEquals($settings['receipt_footer'], $payload['receipt_footer']);
        $this->assertEquals($settings['show_receipt_footer'], $payload['show_receipt_footer']);
        $this->assertEquals($settings['show_receipt_header'], $payload['show_receipt_header']);

        $response->assertRedirect();
    }

    public function test_backoffice_settings_update_payment_settings(): void
    {

        $this->withoutExceptionHandling();

        $mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'Mpesa']);

        $cashPaymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $payload = [
            'mpesa_payment_method' => $mpesaPaymentMethod->id,
            'cash_payment_method' => $cashPaymentMethod->id,
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.settings.update', [
            'group' => 'payment',
        ]), $payload);

        $settings = app(SettingsService::class)->get('payment');

        $this->assertEquals($settings['mpesa_payment_method'], $mpesaPaymentMethod->id);
        $this->assertEquals($settings['cash_payment_method'], $cashPaymentMethod->id);

        $response->assertRedirect();
    }
}
