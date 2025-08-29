<?php

namespace App\Services;

use App\Models\Enums\PaymentMethodFieldOption;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class PaymentMethodService
{
    public function __construct(private readonly SettingsService $settingsService) {}

    public function get(
        ?string $query = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {
        $paymentMethodQuery = PaymentMethod::search($query, function ($defaultQuery) use ($query, $with, $withCount, $limit) {
            $defaultQuery->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', "%{$query}%");
            });

            $defaultQuery->when($with, function ($queryBuilder) use ($with) {
                $queryBuilder->with($with);
            });

            $defaultQuery->when($withCount, function ($queryBuilder) use ($withCount) {
                $queryBuilder->withCount($withCount);
            });

            $defaultQuery->when($limit, function ($queryBuilder) use ($limit) {
                $queryBuilder->limit($limit);
            });
        });

        $paymentMethodQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $paymentMethodQuery->get()
            : $paymentMethodQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $properties = [];

            foreach (PaymentMethodFieldOption::options() as $option) {
                if ($value = data_get($data, $option)) {
                    $properties[$option] = $value;
                }
            }

            $attributes = [
                'name' => data_get($data, 'name'),
                'description' => data_get($data, 'description', null),
                'required_fields' => data_get($data, 'required_fields', null),
                'default_payment_status' => data_get($data, 'default_payment_status', null),
                'phone_number' => data_get($data, 'phone_number', null),
                'paybill_number' => data_get($data, 'paybill_number', null),
                'account_number' => data_get($data, 'account_number', null),
                'till_number' => data_get($data, 'till_number', null),
                'account_name' => data_get($data, 'account_name', null),
                'properties' => $properties,
            ];

            return PaymentMethod::create($attributes);
        });
    }

    public function update(PaymentMethod $method, array $data)
    {
        return DB::transaction(function () use ($method, $data) {

            $properties = [];

            foreach (PaymentMethodFieldOption::options() as $option) {
                if ($value = data_get($data, $option)) {
                    $properties[$option] = $value;
                }
            }

            $attributes = [
                'name' => data_get($data, 'name', $method->name),
                'description' => data_get($data, 'description', $method->description),
                'required_fields' => data_get($data, 'required_fields', $method->required_fields),
                'default_payment_status' => data_get($data, 'default_payment_status', $method->default_payment_status),
                'phone_number' => data_get($data, 'phone_number', $method->phone_number),
                'paybill_number' => data_get($data, 'paybill_number', $method->paybill_number),
                'account_number' => data_get($data, 'account_number', $method->account_number),
                'till_number' => data_get($data, 'till_number', $method->till_number),
                'account_name' => data_get($data, 'account_name', $method->account_name),
                'properties' => $properties,
            ];

            return $method->update($attributes);
        });
    }

    public function delete(PaymentMethod $method)
    {
        return DB::transaction(function () use ($method) {
            return $method->delete();
        });
    }

    public function importRow(array $data)
    {
        $attributes = [
            'name' => data_get($data, 'name'),
            'description' => data_get($data, 'description', null),
        ];

        return PaymentMethod::updateOrCreate(['name' => $attributes['name']], $attributes);
    }

    public function getMpesaPaymentMethod()
    {
        $mpesaId = data_get($this->settingsService->get('payment'), 'mpesa_payment_method');

        return is_null($mpesaId) ? PaymentMethod::search('mpesa')->first() : PaymentMethod::find($mpesaId);
    }

    public function isMpesaPaymentMethod(PaymentMethod $paymentMethod): bool
    {
        if (is_null($paymentMethod)) {
            return false;
        }

        return $paymentMethod->id === $this->getMpesaPaymentMethod()?->id;
    }
}
