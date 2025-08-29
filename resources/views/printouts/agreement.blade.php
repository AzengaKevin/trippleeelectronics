<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tripple e electronics Agreement Form</title>
    <style>
        @page {
            size: A4;
            margin: 0.3cm;
        }

        body {
            font-family: Arial, sans-serif;
            width: 210mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
            color: #000;
            font-size: 12px;
            background: #fff;
        }

        h1,
        h2 {
            color: #0403fe;
        }

        h1 {
            text-align: center;
            text-transform: uppercase;
            border-bottom: 3px solid #0403fe;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        td,
        th {
            border: 1px solid #ccc;
            padding: 10px;
            vertical-align: top;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        .section-title {
            background-color: #0403fe;
            color: white;
            padding: 8px;
            font-size: 18px;
            margin-top: 40px;
            margin-bottom: 10px;
        }

        .checkbox-group {
            margin: 10px 0;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            margin-top: 50px;
            color: #555;
        }

        ol {
            margin-left: 20px;
        }

        .w-third {
            width: 33.33%;
        }

        .w-half {
            width: 50%;
        }

        .w-two-thirds {
            width: 66.67%;
        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .avoid-page-break-inside {
            page-break-inside: avoid;
        }

        @media print {
            body {
                margin: 0;
                width: auto;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <h1>{{ $agreement->store?->name ?? data_get($settings, 'site_name', config('app.name')) }} Agreement Form</h1>

    <div class="section-title">1. Client Information</div>
    <table>
        <tr>
            <th class="w-third text-start">Full Name</th>
            <td class="w-two-thirds">{{ $agreement?->client?->name ?? '___________________________' }}</td>
        </tr>
        <tr>
            <th class="w-third text-start">Phone Number</th>
            <td class="w-two-thirds">{{ $agreement?->client?->phone ?? '___________________________' }}</td>
        </tr>
        <tr>
            <th class="w-third text-start">Email Address</th>
            <td class="w-two-thirds">{{ $agreement?->client?->email ?? '___________________________' }}</td>
        </tr>
        <tr>
            <th class="w-third text-start">Physical Address</th>
            <td class="w-two-thirds">{{ $agreement?->client?->adress ?? '___________________________' }}</td>
        </tr>
    </table>

    <div class="section-title">2. Agreement Details</div>

    @if ($agreement?->agreementable_type === 'order')
        @php
            $paidAmount = $agreement->agreementable->completePayments->sum('amount');
            $balanceAmount = $paidAmount - $agreement->agreementable->total_amount;
            $outstandingAmount = $agreement->agreementable->total_amount - $paidAmount;
        @endphp
        <p>The agreement about an order deal described below.</p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Disc.</th>
                    <th>Tax</th>
                    <th class="text-end">Total(Ksh.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agreement->agreementable->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->item->name ?? ($item->description ?? 'Item') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->discount_rate > 0 ? $item->discount_rate . '%' : '0%' }}</td>
                        <td>{{ $item->tax_rate > 0 ? $item->tax_rate . '%' : '0%' }}</td>
                        <td class="text-end">{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <tr>
                <td class="font-bold">Subtotal:</td>
                <td class="text-end">Ksh. {{ number_format($agreement->agreementable->amount, 2) }}</td>
            </tr>
            @if ($agreement->agreementable->tax_amount > 0)
                <tr>
                    <td class="font-bold">Tax:</td>
                    <td class="text-end">Ksh. {{ number_format($agreement->agreementable->tax_amount, 2) }}</td>
                </tr>
            @endif
            @if ($agreement->agreementable->discount_amount > 0)
                <tr>
                    <td class="font-bold">Discounts:</td>
                    <td class="text-end">Ksh. {{ number_format($agreement->agreementable->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="font-bold">Shipping:</td>
                <td class="text-end">Ksh. {{ number_format($agreement->agreementable->shipping_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="font-bold">GRAND TOTAL:</td>
                <td class="text-end">Ksh. {{ number_format($agreement->agreementable->total_amount, 2) }}</td>
            </tr>
            @foreach ($agreement->agreementable->completePayments as $payment)
                <tr>
                    <td class="font-bold">{{ $payment->paymentMethod?->name ?? '-' }} Payment:</td>
                    <td class="text-end">Ksh. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="font-bold">Paid Amount:</td>
                <td class="text-end">Ksh. {{ number_format($paidAmount, 2) }}</td>
            </tr>
            <tr>
                <td class="font-bold">Outstanding Amount:</td>
                <td class="text-end">Ksh. {{ number_format($outstandingAmount, 2) }}</td>
            </tr>
        </table>

        <div>
            <h2>Content</h2>
            <p>{{ $agreement->content }}</p>
        </div>
    @endif
    <div class="avoid-page-break-inside">
        <div class="section-title">3. Terms & Conditions</div>
        <ol>
            <li>Service scope will follow the description above.</li>
            <li>Payments are due based on the agreed terms before final delivery.</li>
            <li>Up to two revisions are allowed unless otherwise stated.</li>
            <li>Client data will remain confidential.</li>
            <li>Either party may terminate this agreement with 7 days' notice.</li>
            <li>Tripple e electronics is not liable for damages beyond agreed work.</li>
            <li>Final deliverables are owned by the client upon full payment.</li>
        </ol>
    </div>

    <div class="avoid-page-break-inside">
        <div class="section-title">4. Acknowledgement & Signatures</div>
        <table>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Tripple e electronics</th>
                <th>Referee</th>
            </tr>
            <tr>
                <th class="text-start">Full Name</th>
                <td>__________________________</td>
                <td>__________________________</td>
                <td>__________________________</td>
            </tr>
            <tr>
                <th class="text-start">Signature</th>
                <td>__________________________</td>
                <td>__________________________</td>
                <td>__________________________</td>
            </tr>
            <tr>
                <th class="text-start">Date</th>
                <td>__________________________</td>
                <td>__________________________</td>
                <td>__________________________</td>
            </tr>
        </table>
    </div>


    <div class="footer">
        <strong>Contact:</strong> {{ $agreement->store?->phone ?? data_get($settings, 'phone') }} |
        <strong>Website:</strong> <a href="https://tripple-e-electronics.com">tripple-e-electronics.com</a> |
        <strong>Email:</strong> {{ $agreement->store?->email ?? data_get($settings, 'email') }}<br>
    </div>

</body>

</html>
