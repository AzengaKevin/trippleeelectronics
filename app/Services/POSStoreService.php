<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;

class POSStoreService
{
    private const POS_STORE_KEY = 'current-pos-store';

    public function setCurrentPOSStore($storeId)
    {

        session()->put(self::POS_STORE_KEY, $storeId);
    }

    public function getCurrentPOSStore(?User $user = null)
    {
        $store = $user?->stores()?->with('paymentMethods')?->first();

        if ($store) {

            return $this->processStoreFields($store);
        }

        $storeId = session()->get(self::POS_STORE_KEY);

        if ($storeId) {

            $store = Store::query()->with('paymentMethods')->find($storeId);

            return $this->processStoreFields($store);
        }

        $store = Store::query()->with('paymentMethods')->first();

        return $this->processStoreFields($store);
    }

    private function processStoreFields(?Store $store): ?array
    {
        if (! $store) {
            return null;
        }

        return [
            'id' => $store->id,
            'name' => $store->name,
            'paymentMethods' => $store->paymentMethods->map(function ($method) {

                $labelBuilder = str($method->name);

                foreach (data_get($method, 'required_fields', []) as $field) {

                    $value = $method->pivot->{$field};

                    $labelBuilder = $labelBuilder->append(" : {$value}");
                }

                return [
                    'value' => $method->id,
                    'label' => $labelBuilder->value(),
                ];
            }),
        ];
    }
}
