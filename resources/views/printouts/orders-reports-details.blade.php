<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Electronics Shop - Detailed Sales Report</title>
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.3;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #0403fe;
            padding-bottom: 5px;
        }

        .header h1 {
            color: #0403fe;
            margin: 5px 0;
            font-size: 24px;
        }

        .header p {
            margin: 2px 0;
            font-weight: bold;
            font-size: 9px;
        }

        .report-info {
            background-color: #f5f5f5;
            padding: 5px;
            margin-bottom: 10px;
            border-left: 3px solid #0403fe;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            /* page-break-inside: avoid; */
        }

        th {
            background-color: #0403fe;
            color: white;
            text-align: left;
            padding: 5px;
            font-weight: normal;
            font-size: 9px;
        }

        td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
            font-size: 9px;
        }

        .order-header {
            background-color: #e6f2ff;
            font-weight: bold;
            border-bottom: 2px solid #000;
            font-size: 9px;
        }

        .order-header.deleted {
            background-color: #ffebee;
            color: #d32f2f;
            text-decoration: line-through;
        }

        .order-footer {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 9px;
        }

        .order-footer.deleted {
            background-color: #ffebee;
            color: #d32f2f;
            text-decoration: line-through;
        }

        .deleted-row {
            color: #d32f2f;
            text-decoration: line-through;
        }

        .text-right {
            text-align: right;
            text-align: end;
        }

        .text-center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-primary {
            background-color: #0403fe;
            color: white;
        }

        .badge-deleted {
            background-color: #d32f2f;
            color: white;
        }

        .bigger-total {
            font-size: 11px;
            font-weight: bold;
            color: #0403fe;
        }

        .bigger-total.deleted {
            color: #d32f2f;
        }

        .signature {
            margin-top: 3px;
            font-size: 9px;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .summary-table {
            margin-top: 10px;
            width: 100%;
            border: 1px solid #0403fe;
            page-break-inside: avoid;
        }

        .summary-table th {
            background-color: #0403fe;
            color: white;
            padding: 6px;
            font-size: 9px;
        }

        .summary-table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }

        .summary-table tr:last-child td {
            border-bottom: none;
        }

        .highlight-cell {
            font-weight: bold;
            color: #0403fe;
        }

        /* Print-specific styles */
        @media print {
            body {
                font-size: 9px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
            }
            
            .summary-table {
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: avoid;
            }

            .deleted-row, .order-header.deleted, .order-footer.deleted {
                color: #d32f2f !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .text-error{
                color: #d32f2f !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="uppercase">
    <div>
        <div class="header">
            <h1>
                {{ $filters['store']?->name ?? data_get($settings, 'site_name') }}
            </h1>
            <p>
                {{ $filters['store']?->address ?? data_get($settings, 'location', '-') }} | Phone:
                {{ $filters['store']?->phone ?? data_get($settings, 'phone') }}</p>
            <p>Detailed Sales Report | Generated: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="report-info">
            <strong>Report Period:</strong> {{ $filters['from'] }} - {{ $filters['to'] }} |
            <strong>Orders Count:</strong> {{ data_get($statistics, 'total_orders') }} |
            <strong>Deleted Orders:</strong> <span>{{ $orders->whereNotNull('deleted_at')->count() }}</span> |
            <strong>Total Sales:</strong> Ksh {{ number_format(data_get($statistics, 'total_sales', 0), 2) }}
        </div>

        <div>
            <table>
                <thead>
                    <tr>
                        <th width="3%">#</th>
                        <th width="10%">SKU</th>
                        <th width="40%">Product</th>
                        <th width="7%">Qty</th>
                        <th width="10%">Unit Price</th>
                        <th width="10%">Discount</th>
                        <th width="20%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="order-header {{ $order->deleted_at ? 'deleted' : '' }}">
                            <td colspan="7">
                                {{ $order->reference }} |
                                {{ $order->created_at->format('d/m/Y H:i') }} |
                                {{ $order->author->name ?? 'Online' }}
                            </td>
                        </tr>
                        @foreach ($order->items as $item)
                            <tr class="{{ $order->deleted_at ? 'deleted-row' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->item?->sku ?? '-' }}</td>
                                <td>{{ $item->item?->name ? str($item->item?->name)->limit(40) : '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Ksh {{ number_format($item->price, 2) }}</td>
                                <td>Ksh {{ number_format(0, 2) }}</td>
                                <td class="text-right">Ksh {{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="order-footer {{ $order->deleted_at ? 'deleted' : '' }}">
                            <td colspan="6">
                                @php
                                    $payments = $order->payments->map(
                                        fn($p) => str($p->paymentMethod->name ?? '-')
                                            ->append(' Ksh ')
                                            ->append(number_format($p->amount, 2)),
                                    );
                                @endphp
                                {{ $payments->implode(' | ') }}
                            </td>
                            <td class="text-right bigger-total {{ $order->deleted_at ? 'deleted' : '' }}">Ksh {{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th colspan="2">SALES SUMMARY</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Number of Sales</td>
                    <td class="highlight-cell text-right">{{ data_get($statistics, 'total_orders') }}</td>
                </tr>
                <tr>
                    <td>Deleted Orders</td>
                    <td class="text-right">{{ $orders->whereNotNull('deleted_at')->count() }}</td>
                </tr>
                <tr class="deleted-row">
                    <td>Deleted Orders Amount</td>
                    <td class="highlight-cell text-right">Ksh
                        {{ number_format($orders->whereNotNull('deleted_at')->sum('total_amount'), 2) }}</td>
                </tr>
                <tr>
                    <td>Total Sales Amount</td>
                    <td class="highlight-cell text-right">Ksh
                        {{ number_format(data_get($statistics, 'total_sales', 0), 2) }}</td>
                </tr>
                <tr>
                    <td>Average Amount per Sale</td>
                    <td class="highlight-cell text-right">Ksh
                        {{ number_format(data_get($statistics, 'average_order_value', 0), 2) }}</td>
                </tr>
                <tr>
                    <td>Total Number of Items Sold</td>
                    <td class="highlight-cell text-right">{{ data_get($statistics, 'total_number_of_items', 0) }}</td>
                </tr>
                <tr>
                    <td>Average Amount per Item</td>
                    <td class="highlight-cell text-right">Ksh
                        {{ number_format(data_get($statistics, 'total_sales', 0) / max(1, data_get($statistics, 'total_number_of_items', 1)), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="summary-section">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th width="40%">Payment Method</th>
                        <th width="15%">Count</th>
                        <th width="25%" class="text-right">Amount</th>
                        <th width="20%" class="text-right">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPayments = $methods->sum('total');
                    @endphp

                    @foreach ($methods as $method)
                        <tr>
                            <td>{{ data_get($method, 'name') }}</td>
                            <td>{{ data_get($method, 'count') }}</td>
                            <td class="text-right">Ksh {{ number_format(data_get($method, 'total'), 2) }}</td>
                            <td class="text-right">
                                {{ $totalPayments > 0 ? number_format((data_get($method, 'total') / $totalPayments) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                    <tr class="highlight-cell">
                        <td><strong>TOTAL</strong></td>
                        <td><strong>{{ $methods->sum('count') }}</strong></td>
                        <td class="text-right"><strong>Ksh {{ number_format($totalPayments, 2) }}</strong></td>
                        <td class="text-right"><strong>100%</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>{{ $filters['store']?->name ?? data_get($settings, 'site_name') }} - Detailed Sales Report</p>
            <p>System generated report - For internal use only | Printed on: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>

</html>