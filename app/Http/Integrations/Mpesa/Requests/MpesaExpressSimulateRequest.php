<?php

namespace App\Http\Integrations\Mpesa\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class MpesaExpressSimulateRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(private array $data) {}

    public function resolveEndpoint(): string
    {
        return '/mpesa/stkpush/v1/processrequest';
    }

    protected function defaultBody(): array
    {
        return [
            'BusinessShortCode' => config('services.mpesa.shortcode'),
            'Password' => base64_encode(str(config('services.mpesa.shortcode'))->append(config('services.mpesa.lipa_na_mpesa_passkey'))->append(now()->format('YmdHis'))->value()),
            'Timestamp' => now()->format('YmdHis'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $this->data['amount'],
            'PartyA' => $this->data['phone'],
            'PartyB' => config('services.mpesa.shortcode'),
            'PhoneNumber' => $this->data['phone'],
            'CallBackURL' => config('services.mpesa.callback_url'),
            'AccountReference' => $this->data['reference'],
            'TransactionDesc' => $this->data['description'],
        ];
    }
}
