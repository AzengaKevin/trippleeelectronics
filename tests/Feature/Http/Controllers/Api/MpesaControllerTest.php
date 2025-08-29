<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MpesaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_mpesa_validate_route()
    {
        $response = $this->postJson(route('api.mpesa.validate'), [
            'Shortcode' => '174379',
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => 1,
            'Msisdn' => '254708374149',
            'BillRefNumber' => 'Test123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
            ]);
    }

    public function test_mpesa_confirm_route()
    {
        $response = $this->postJson(route('api.mpesa.confirm'), [
            'Shortcode' => '174379',
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => 1,
            'Msisdn' => '254708374149',
            'BillRefNumber' => 'Test123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
            ]);
    }

    public function test_mpesa_timeout_route()
    {
        $response = $this->postJson(route('api.mpesa.timeout'), [
            'Shortcode' => '174379',
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => 1,
            'Msisdn' => '254708374149',
            'BillRefNumber' => 'Test123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
            ]);
    }

    public function test_mpesa_result_route()
    {
        $response = $this->postJson(route('api.mpesa.result'), [
            'Shortcode' => '174379',
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => 1,
            'Msisdn' => '254708374149',
            'BillRefNumber' => 'Test123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
            ]);
    }

    public function test_mpesa_callback_route()
    {
        $this->withoutExceptionHandling();

        $response = $this->postJson(route('api.mpesa.callback'), [
            'Shortcode' => '174379',
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => 1,
            'Msisdn' => '254708374149',
            'BillRefNumber' => 'Test123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
            ]);
    }
}
