<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Http\Integrations\Mpesa\MpesaConnector;
use App\Http\Integrations\Mpesa\Requests\MpesaAuthorization;
use App\Http\Integrations\Mpesa\Requests\MpesaExpressSimulateRequest;
use App\Models\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Saloon\Http\Auth\TokenAuthenticator;

class MpesaService
{
    public const MPESA_ACCESS_TOKEN_SESSION_KEY = 'mpesa-access-token-info';

    private function getAccessToken()
    {
        $info = session(self::MPESA_ACCESS_TOKEN_SESSION_KEY, null);

        if ($info && Carbon::parse(data_get($info, 'when'))->addSeconds(intval(data_get($info, 'expires_in')))->isFuture()) {

            return data_get($info, 'access_token');
        }

        $connector = new MpesaConnector;

        $request = new MpesaAuthorization;

        $response = $connector->send($request);

        $result = $response->json();

        session([self::MPESA_ACCESS_TOKEN_SESSION_KEY => [
            ...$result,
            'when' => now()->toDateTimeString(),
        ]]);

        return data_get($result, 'access_token');
    }

    public function saloonTriggerStkPush(array $data, ?Transaction $transaction = null)
    {

        $attributes = [
            'amount' => intval(ceil(data_get($data, 'amount'))),
            'phone' => $this->sanitizePhoneNumber(data_get($data, 'phone')),
            'reference' => data_get($data, 'reference'),
            'description' => data_get($data, 'description'),
        ];

        $connector = new MpesaConnector;

        $accessToken = $this->getAccessToken();

        $request = new MpesaExpressSimulateRequest($attributes);

        $response = $connector->authenticate(new TokenAuthenticator($accessToken))->send($request);

        $result = $response->json();

        if (data_get($result, 'ResponseCode') == '0') {

            if ($transaction) {

                $result = $transaction->update([
                    'status' => TransactionStatus::INITIATED,
                    'data' => [
                        'CheckoutRequestID' => data_get($result, 'CheckoutRequestID'),
                        'ResponseCode' => data_get($result, 'ResponseCode'),
                        'ResponseDescription' => data_get($result, 'ResponseDescription'),
                        'MerchantRequestID' => data_get($result, 'MerchantRequestID'),
                        'CustomerMessage' => data_get($result, 'CustomerMessage'),
                    ],
                ]);

            }

            return $result;
        }
    }

    public function sanitizePhoneNumber($phone)
    {
        $phone = trim(str_replace(' ', '', $phone));

        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {

            return '254'.substr($phone, 1, 9);
        } elseif (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {

            return $phone;
        } elseif (substr($phone, 0, 1) == '+') {

            return str_replace('+', '', trim($phone));
        } else {

            throw new CustomException('Invalid Phone Number');
        }
    }

    private function generateSecurityCredential()
    {
        $encrypted = null;

        $certificatePath = config('services.mpesa.env') === 'live'
            ? Storage::disk('local')->path('mpesa/ProductionCertificate.cer')
            : Storage::disk('local')->path('mpesa/SandboxCertificate.cer');

        $initiatorPassword = config('services.mpesa.initiator_password');

        if (! file_exists($certificatePath)) {

            throw new CustomException('Certificate not found.');
        }

        $publicKey = file_get_contents($certificatePath);

        if (! $publicKey) {

            throw new CustomException('Certificate not found or unreadable.');
        }

        openssl_public_encrypt($initiatorPassword, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }
}
