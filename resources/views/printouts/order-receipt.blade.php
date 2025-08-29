<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order {{ $order->reference }} Receipt</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-size: 14px;
            line-height: 1.2;
        }

        .wrapper {
            margin-inline: auto;
        }

        body {
            width: 70mm;
            padding-block: 1mm;
            padding-inline: 2mm;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 2mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
        }

        .business-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 1mm;
        }

        .transaction-info {
            width: 100%;
            margin-bottom: 3mm;
        }

        .transaction-row {
            width: 100%;
            margin-bottom: 1mm;
            clear: both;
        }

        .label {
            float: left;
            width: 30%;
            font-weight: bold;
        }

        .value {
            float: right;
            width: 70%;
            text-align: right;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 1mm 0;
        }

        .items-table td {
            padding: 1mm 0;
            vertical-align: top;
        }

        .item-name {
            width: 60%;
        }

        .item-qty {
            width: 15%;
            text-align: center;
        }

        .item-price {
            width: 25%;
            text-align: right;
        }

        .totals-table {
            width: 100%;
            margin-bottom: 2mm;
        }

        .totals-table td {
            padding: 1mm 0;
        }

        .total-label {
            width: 60%;
            text-align: right;
            font-weight: bold;
        }

        .total-value {
            width: 40%;
            text-align: right;
        }

        .grand-total {
            border-top: 1px dashed #000;
            font-weight: bold;
        }

        .footer {
            width: 100%;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 2mm;
            font-size: 10px;
        }

        .clearfix {
            clear: both;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-weight-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        @if (data_get($settings, 'show_receipt_header'))
            <div class="header">
                <div class="business-name">{{ $order->store?->name ?? config('app.name') }}</div>
                <div>{{ $order->store?->address ?? data_get($settings, 'location') }}</div>
                <div>Tel: {{ $order->store?->phone ?? data_get($settings, 'phone') }}</div>
            </div>
        @else
            <div class="header">
                <div class="business-name">CASH RECEIPT</div>
            </div>
        @endif

        <div class="transaction-info">
            <div class="transaction-row">
                <div class="label">Date:</div>
                <div class="value">{{ $order->created_at->format('Y-m-d') }}</div>
                <div class="clearfix"></div>
            </div>
            <div class="transaction-row">
                <div class="label">Time:</div>
                <div class="value">{{ $order->created_at->format('H:i:s') }}</div>
                <div class="clearfix"></div>
            </div>
            <div class="transaction-row">
                <div class="label">Invoice #:</div>
                <div class="value">{{ $order->reference }}</div>
                <div class="clearfix"></div>
            </div>
            <div class="transaction-row">
                <div class="label">Staff:</div>
                <div class="value">{{ $order->author?->name ?? '-' }}</div>
                <div class="clearfix"></div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="item-name text-left">Item</th>
                    <th class="item-qty text-center">Qty</th>
                    <th class="item-price text-center">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td class="item-name">{{ str($item->item->name)->trim()->limit(40) }}</td>
                        <td class="item-qty">{{ $item->quantity }}</td>
                        <td class="item-price">{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="total-label">Subtotal:</td>
                <td class="total-value">Ksh. {{ number_format($order->totalPrice(), 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">Tax:</td>
                <td class="total-value">Ksh. {{ number_format($order->totalTax(), 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">Discount:</td>
                <td class="total-value">Ksh. {{ number_format($order->totalDiscount(), 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="total-label">TOTAL:</td>
                <td class="total-value">Ksh. {{ number_format($order->total_amount, 2) }}</td>
            </tr>

            @if ($order->completePayments->count() === 1)
                <tr>
                    <td class="total-label">Payment Method:</td>
                    <td class="total-value">{{ $order->completePayments->first()?->paymentMethod?->name ?? '-' }}</td>
                </tr>
            @endif

            @if ($order->completePayments->count() > 1)
                @foreach ($order->completePayments as $payment)
                    <tr>
                        <td class="total-label">{{ $payment->paymentMethod->name }}</td>
                        <td class="total-value">Ksh. {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            @endif

            @php
                $paidAmount = $order->completePayments->sum('amount');
                $balanceAmount = $paidAmount - $order->total_amount;
                $outstandingAmount = $order->total_amount - $paidAmount;
            @endphp

            <tr>
                <td class="total-label">Paid:</td>
                <td class="total-value">Ksh. {{ number_format($paidAmount, 2) }}</td>
            </tr>

            @if ($balanceAmount > 0)
                <tr>
                    <td class="total-label">Balance:</td>
                    <td class="total-value">Ksh. {{ number_format($order->balance_amount, 2) }}</td>
                </tr>
            @endif

            @if ($outstandingAmount > 0)
                <tr>
                    <td class="total-label">Outstanding:</td>
                    <td class="total-value">Ksh. {{ number_format($outstandingAmount, 2) }}</td>
                </tr>
            @endif
        </table>

        <div class="footer">
            @if ($order->customer?->name)
                <p>Customer: {{ $order->customer?->name }}</p>
            @endif
            <br>
            <p>{!! data_get($settings, 'receipt_footer', 'Thank you for your business!') !!}</p>
        </div>
    </div>
</body>

</html>
