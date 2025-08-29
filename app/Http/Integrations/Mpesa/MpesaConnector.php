<?php

namespace App\Http\Integrations\Mpesa;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class MpesaConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return config('services.mpesa.env') === 'live'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [];
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new BasicAuthenticator(
            config('services.mpesa.consumer_key'),
            config('services.mpesa.consumer_secret')
        );
    }
}
