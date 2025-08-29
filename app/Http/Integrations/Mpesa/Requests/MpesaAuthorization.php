<?php

namespace App\Http\Integrations\Mpesa\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class MpesaAuthorization extends Request
{
    protected Method $method = Method::GET;

    protected int $connectTimeout = 30;

    public function resolveEndpoint(): string
    {
        return '/oauth/v1/generate?grant_type=client_credentials';
    }

    protected function defaultQuery(): array
    {
        return [
            'grant_type' => 'client_credentials',
        ];
    }
}
