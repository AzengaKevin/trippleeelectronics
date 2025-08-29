<?php

namespace App\Imports;

use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\RefundStatus;
use App\Models\Enums\ShippingStatus;
use App\Models\Individual;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class OrderImport extends StringValueBinder implements OnEachRow, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithCustomValueBinder, WithHeadingRow, WithStartRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable', 'string'],
            'customer_phone' => ['nullable', 'string'],
            'author_phone' => ['nullable', 'string'],
            'store_short_name' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'shipping_amount' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
            'tax_amount' => ['nullable', 'numeric'],
            'discount_amount' => ['nullable', 'numeric'],
            'tendered_amount' => ['nullable', 'numeric'],
            'balance_amount' => ['nullable', 'numeric'],
            'order_status' => ['nullable', Rule::in(OrderStatus::options())],
            'fulfillment_status' => ['nullable', Rule::in(FulfillmentStatus::options())],
            'shipping_status' => ['nullable', Rule::in(ShippingStatus::options())],
            'refund_status' => ['nullable', Rule::in(RefundStatus::options())],
            'channel' => ['nullable', 'string'],
            'refferal_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $customer = null;

        $author = null;

        $store = null;

        if ($customerPhone = data_get($data, 'customer_phone')) {

            $customer = Individual::where('phone', $customerPhone)->first();

            if (! $customer) {

                $customer = Organization::where('phone', $customerPhone)->first();
            }
        }

        $data['customer'] = $customer;

        if ($authorPhone = data_get($data, 'author_phone')) {

            $author = User::where('phone', $authorPhone)->first();
        }

        $data['author'] = $author;

        if ($storeShortName = data_get($data, 'store_short_name')) {

            $store = Store::where('short_name', $storeShortName)->first();
        }

        $data['store'] = $store;

        app(OrderService::class)->importRow($data);
    }
}
