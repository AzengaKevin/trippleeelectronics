<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $order->reference }} Invoice</title>
    <style>
        @page {
            size: A4;
            margin: 1.27cm;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.2;
            color: #333;
            max-width: 21cm;
            margin: 0 auto;
            padding: 8px;
            font-size: 11px;
            position: relative;
            min-height: 29.7cm;
        }

        .content {}

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .header {
            margin-bottom: 10px;
            border-bottom: 1px solid #0403fe;
            padding-bottom: 10px;
        }

        .shop-info {
            float: left;
            width: 60%;
        }

        .invoice-info {
            float: right;
            width: 38%;
            text-align: right;
        }

        h1 {
            color: #0403fe;
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        h2 {
            color: #444;
            font-size: 12px;
            margin: 5px 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        p {
            margin: 3px 0;
        }

        .customer-info {
            margin-bottom: 10px;
        }

        .customer-details,
        .delivery-details {
            float: left;
            height: 4cm;
            width: 48%;
            border: 1px solid #0403fe;
        }

        .customer-details .header,
        .delivery-details .header {
            background: #0403fe;
            padding: 5px;
        }

        .customer-details .header h2,
        .delivery-details .header h2 {
            color: white;
            margin: 0;
            font-size: 12px;
            border: 0;
            padding: 0;
            margin: 0;
        }

        .customer-details p,
        .delivery-details p {
            padding: 5px;
        }

        .delivery-details {
            float: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            border: 1px solid #000;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        thead {
            background-color: #0403fe;
            color: white;
            text-align: left;
            padding: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }

        table,
        th,
        td {
            border-collapse: collapse;
        }

        thead>tr>th {
            padding: 5px;
            border-right: 1px solid #000;
        }

        td {
            padding: 5px;
            border-right: 1px solid #000;
            border-left: 1px solid #000;
        }

        .totals {
            margin-top: 10px;
        }

        .totals table {
            width: 100%;
        }

        .totals td {
            border: none;
            padding: 3px;
        }

        .totals tr:last-child {
            font-weight: bold;
            border-top: 1px solid #0403fe;
        }

        .signature {
            margin-top: 20px;
        }

        .signature-line {
            float: left;
            width: 45%;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 3px;
            font-size: 10px;
        }

        .signature-line:last-child {
            float: right;
        }

        .text-right {
            text-align: right;
        }

        .text-capitalize {
            text-transform: capitalize;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .totals-text {
            font-size: 14px;
            text-transform: uppercase;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .border-top {
            border-top: 1px solid #000;
        }

        .border-right {
            border-right: 1px solid #000;
        }

        .p-2 {
            padding: 4px;
        }

        .footnote {
            font-size: 12px;
            color: #666;
            margin-top: 16px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

@php
    $subtotal = $order->computedAmount();

    $tax = $order->totalTax();

    $discount = $order->totalDiscount();

    $shipping = $order->shipping_amount;

    $grandTotal = $order->total_amount;

    $paidAmount = $order->completePayments->sum('amount');

    $balanceAmount = $paidAmount - $order->total_amount;

    $outstandingAmount = $order->total_amount - $paidAmount;

    $discountColumnsOffset = $discount > 0 ? 0 : -1;

    $taxColumnsOffset = $tax > 0 ? 0 : -1;

    $shippingColumnsOffset = $shipping > 0 ? 0 : -1;

    $paymentsColumnsOffset = $order->completePayments->count() * -1;

    $paidAmountColumns = $paidAmount > 0 ? 0 : -1;

    $outstandingAmountColumns = $outstandingAmount > 0 ? 0 : -1;

    $max = 25 + $discountColumnsOffset + $taxColumnsOffset + $shippingColumnsOffset;

    $count = $max - $order->items->count();
@endphp

<body>
    <div class="content">
        <div class="header clearfix">
            <div class="shop-info">
                <h1>{{ $order->store->name ?? config('app.name') }}</h1>
                <p>{{ $order->store->address ?? data_get($settings, 'location', '-') }}<br>
                    Phone: {{ $order->store->phone ?? data_get($settings, 'phone', '-') }}<br>
                    Email: {{ $order->store->email ?? data_get($settings, 'email', '-') }}<br>
                    PAYBILL: {{ $order->store->paybill ?? '-' }} <br>
                    ACCOUNT NUMBER: {{ $order->store->account_number ?? $order->reference }} <br>
                    KRA PIN: {{ $order->store->kra_pin ?? '-' }}</p>
            </div>
            <div class="invoice-info">
                <h1>INVOICE</h1>
                <p><strong>Invoice #:</strong> {{ $order->reference }}<br>
                    <strong>Date:</strong> {{ $order->created_at->toDateString() }}<br>
                    <strong>Due Date:</strong> {{ $order->created_at->addDays(14)->toDateString() }}
                </p>
            </div>
        </div>

        <div class="customer-info clearfix">
            <div class="customer-details">
                <div class="header">
                    <h2>INVOICE TO:</h2>
                </div>
                <p>
                    @if ($order->customer)
                        <strong>{{ $order->customer?->name ?? '-' }}</strong><br>
                    @endif
                    @if ($order->customer?->address)
                        {{ $order->customer?->address ?? '-' }}<br>
                    @endif
                    @if ($order->customer?->phone)
                        Phone: {{ $order->customer?->phone ?? '-' }}<br>
                    @endif
                    @if ($order->customer?->email)
                        Email: {{ $order->customer?->email ?? '-' }}
                    @endif
                </p>
            </div>
            <div class="delivery-details">
                <div class="header">
                    <h2>DELIVERY</h2>
                </div>
                <p>

                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Disc.</th>
                    <th>Tax</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->item->name ?? ($item->description ?? 'Item') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ $item->discount_rate > 0 ? $item->discount_rate . '%' : '0%' }}</td>
                        <td class="text-right">{{ $item->tax_rate > 0 ? $item->tax_rate . '%' : '0%' }}</td>
                        <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
                @for ($i = 0; $i < $count; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr class="totals-text border-top border-right">
                    <td></td>
                    <td class="text-capitalize">Invoice is valid for 14 days from the above date.</td>
                    <th colspan="4" class="text-right p-2">Sub Total:</th>
                    <th class="text-right p-2">{{ number_format($grandTotal, 2) }}</th>
                </tr>
                @if ($tax > 0)
                    <tr class="totals-text border-right">
                        <td></td>
                        <td></td>
                        <th colspan="4" class="text-right border-top p-2">Tax:</th>
                        <th class="text-right border-top p-2">{{ number_format($tax, 2) }}</th>
                    </tr>
                @endif

                @if ($discount > 0)
                    <tr class="totals-text border-right">
                        <td></td>
                        <td></td>
                        <th colspan="4" class="text-right border-top p-2">Discount:</th>
                        <th class="text-right border-top p-2">{{ number_format($order->totalDiscount(), 2) }}</th>
                    </tr>
                @endif

                @if ($shipping > 0)
                    <tr class="totals-text border-right">
                        <td></td>
                        <td></td>
                        <th colspan="4" class="text-right border-top p-2">Shipping Cost:</th>
                        <th class="text-right border-top p-2">{{ number_format($order->shipping_amount, 2) }}</th>
                    </tr>
                @endif
                <tr class="totals-text border-right">
                    <td></td>
                    <td></td>
                    <th colspan="4" class="text-right border-top p-2">GRAND TOTAL:</th>
                    <th class="text-right border-top p-2">{{ number_format($order->total_amount, 2) }}</th>
                </tr>

                @foreach ($order->completePayments as $payment)
                    <tr class="totals-text border-right">
                        <td></td>
                        <td></td>
                        <th colspan="4" class="text-right border-top p-2">
                            {{ $payment->paymentMethod?->name ?? '-' }} Payment:</th>
                        <th class="text-right border-top p-2">{{ number_format($payment->amount, 2) }}</th>
                    </tr>
                @endforeach

                @if ($paidAmount > 0)
                    <tr class="totals-text border-right">
                        <td></td>
                        <td></td>
                        <th colspan="4" class="text-right border-top p-2">Paid Amount:</th>
                        <th class="text-right border-top p-2">{{ number_format($paidAmount, 2) }}</th>
                    </tr>
                @endif

                @if ($outstandingAmount > 0)
                    <tr class="totals-text border-right">
                        <td></td>
                        <td></td>
                        <th colspan="4" class="text-right border-top p-2">Outstanding Amount:</th>
                        <th class="text-right border-top p-2">{{ number_format($outstandingAmount, 2) }}</th>
                    </tr>
                @endif
            </tfoot>
        </table>

        <p class="footnote text-center">This is a computer Generated Invoice.</p>
    </div>
</body>

</html>
