<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P & L Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .container {
            height: 21cm;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 1cm;
        }

        .company-header {
            text-align: center;
            padding-bottom: 0.5cm;
            border-bottom: 2px solid #432cd6;
        }

        .company-name {
            font-size: 1.5em;
            color: #432cd6;
            font-weight: bold;
            margin-bottom: 0.15cm;
        }

        .company-details {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 0.15cm;
            line-height: 1.4;
        }

        .report-title {
            font-size: 1.3em;
            color: #432cd6;
            font-weight: bold;
            margin: 0.5cm 0;
            text-align: center;
        }

        .report-period {
            text-align: center;
            font-size: 0.9em;
            margin-bottom: 0.5cm;
            color: #432cd6;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5cm;
        }

        th {
            background-color: #432cd6;
            color: #fff;
            text-align: left;
            padding: 0.15cm;
            border: 1px solid #3219c0;
        }

        td {
            padding: 0.15cm;
            border: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .totals-row {
            font-weight: bold;
            background-color: #e8e5ff !important;
        }

        .footer {
            text-align: center;
            margin-top: 1cm;
            font-size: 0.8em;
            color: #432cd6;
        }

        .profit-positive {
            color: #009900;
        }

        .profit-negative {
            color: #cc0000;
        }

        .hammer-sickle {
            font-size: 1.5em;
            vertical-align: middle;
            color: #432cd6;
        }

        .text-right {
            text-align: right;
        }

        .currency {
            font-weight: semibold;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="company-header">
            <div class="company-name">{{ $store->name ?? config('app.name') }}</span></div>
            <div class="company-details">
                {{ $store?->address ?? data_get($settings, 'location') }}<br>
                TEL: {{ $store->phone ?? data_get($settings, 'phone') }}<br>
            </div>
            <div class="report-period">Reporting Period: {{ $from }} - {{ $from }}</div>
            <div class="report-title">P & L Report</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>P/L</th>
                    <th>Total Cost</th>
                    <th>Total Sales</th>
                    <th>Net Profit</th>
                    <th>Profit %</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="currency">{{ number_format($item->cost, 2) }}</td>
                        <td class="currency">{{ number_format($item->price, 2) }}</td>
                        <td class="currency {{ $item->profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                            {{ number_format($item->profit, 2) }}</td>
                        <td class="currency">{{ number_format($item->total_cost, 2) }}</td>
                        <td class="currency">{{ number_format($item->total_sales, 2) }}</td>
                        <td class="currency {{ $item->net_profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                            {{ number_format($item->net_profit, 2) }}</td>
                        <td class="currency {{ $item->profit_margin >= 0 ? 'profit-positive' : 'profit-negative' }}">
                            {{ number_format($item->profit_margin, 2) }}%</td>
                    </tr>
                @endforeach
                <tr class="totals-row">
                    <td>TOTALS</td>
                    <td>{{ $items->sum('quantity') }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @php
                        $totalProfit = $items->sum('net_profit');

                        $totalCost = $items->sum('total_cost');

                        $profitMargin = $totalCost > 0 ? ($totalProfit / $totalCost) * 100 : 100;
                    @endphp
                    <td class="currency">{{ number_format($items->sum('total_cost'), 2) }}</td>
                    <td class="currency">{{ number_format($items->sum('total_sales'), 2) }}</td>
                    <td class="currency {{ $totalProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        {{ number_format($totalProfit, 2) }}</td>
                    <td class="currency {{ $profitMargin >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        {{ number_format($profitMargin, 2) }}%</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-section">
            <table class="summary-table">
                <thead>
                    <th colspan="4">PAYMENTS SUMMARY</th>
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
                        <td><strong>-</strong></td>
                        <td class="text-right"><strong>Ksh {{ number_format($totalPayments, 2) }}</strong>
                        </td>
                        <td class="text-right"><strong>100%</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>{{ $store?->name ?? data_get($settings, 'site_name') }} - P & L Sales Report</p>
            <p>System generated report - For internal use only</p>
        </div>
    </div>
</body>

</html>
