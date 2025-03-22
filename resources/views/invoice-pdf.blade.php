<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #2A3F54;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #dfe5ec;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 20px;
            text-align: center;
            color: #2A3F54;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            padding: 6px 8px;
            text-align: left;
        }

        table th {
            background-color: #2A3F54;
            color: #ffffff;
            text-transform: uppercase;
            font-weight: bold;
            border-bottom: 2px solid #c1cfde;
            font-size: 11px;
        }

        table tr:nth-child(even) td {
            background-color: #f3f6f9;
        }

        .text-right {
            text-align: right;
        }

        .highlight {
            color: #e53935;
            font-weight: bold;
        }

        .icon-title {
            color: #2A3F54;
            font-weight: bold;
            font-size: 12px;
            display: block;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .frame {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            padding: 12px;
            border: 1px solid #dfe5ec;
            border-radius: 6px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background-color: #fcfcfc;
        }

        .payment-info {
            margin-top: 15px;
            padding: 12px;
            border: 1px solid #c1cfde;
            background-color: #f3f6f9;
            border-radius: 6px;
            font-size: 11px;
        }

        .summary td {
            padding: 6px 8px;
        }

        .summary .total-row {
            font-weight: bold;
            background-color: #2A3F54;
            color: #ffffff;
        }

        .reverse-charge {
            font-size: 12px;
            color: #d32f2f;
            margin-top: 20px;
            text-align: center;
            border-top: 1px solid #dfe5ec;
            padding-top: 10px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #6b6b6b;
        }

        .address {
            font-size: 9px;
            font-style: italic;
        }

        .payment-highlight {
            font-weight: bold;
            color: darkorange;
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <h1>Invoice</h1>

    <!-- Supplier -->
    <div class="frame" style="margin-right: 1%;">
        <span class="icon-title">Supplier</span>
        <strong>{{ $supplier['name'] }}</strong><br>
        <span class="address">
            {{ $supplier['address1'] }}<br>
            {{ $supplier['address2'] }}<br>
            {{ $supplier['address3'] }}<br>
        </span>
        VAT #: {{ $supplier['vat'] }}<br>
        Registration #: {{ $supplier['registration'] }}<br>
        Email: {{ $supplier['email'] }}<br>
        Phone: {{ $supplier['phone'] }}
    </div>

    <!-- Customer -->
    <div class="frame">
        <span class="icon-title">Customer</span>
        <strong>{{ $customer['name'] }}</strong><br>
        <span class="address">
            {{ $customer['address1'] }}<br>
            {{ $customer['address2'] }}<br>
            {{ $customer['address3'] }}<br>
        </span>
        VAT #: {{ $customer['vat'] }}<br>
        @if ($customer['registration'])
            Registration #: {{ $customer['registration'] }}<br>
        @else
            &nbsp;<br>
        @endif
        &nbsp;<br>
        &nbsp;
    </div>

    <!-- Invoice Details -->
    <table>
        <tr>
            <td>
                <strong>Invoice #:</strong> {{ $invoice['number'] }}<br>
                <strong>Invoice Date:</strong> {{ $invoice['date'] }}
            </td>
            <td class="text-right">
                <strong>Due Date:</strong> <span class="highlight">{{ $invoice['dueDate'] }}</span>
            </td>
        </tr>
    </table>

    <!-- Invoice Items -->
    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($invoice['items'] as $item)
            <tr>
                <td>
                    @if($item['url'])
                        <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                    @else
                        {{ $item['name'] }}
                    @endif
                </td>
                <td class="text-right">{{ number_format($item['hours'], 1) }} hours</td>
                <td class="text-right">{{ number_format($invoice['unit_price'], 2) }} {{ $invoice['currency'] }}</td>
                <td class="text-right">{{ number_format($item['amount'], 2) }} {{ $invoice['currency'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Invoice Summary -->
    <table class="summary" style="margin-top: 15px;">
        @if (isset($invoice['tax']))
            <tr>
                <td class="text-right">
                    <strong>Subtotal: {{ number_format($invoice['subtotal'], 2) }} {{ $invoice['currency'] }}</strong>
                </td>
            </tr>
            <tr>
                <td class="text-right">
                    <strong>Tax: {{ number_format($invoice['tax'], 2) }} {{ $invoice['currency'] }}</strong></td>
            </tr>
        @endif
        <tr class="total-row">
            <th class="text-right">
                <strong>Total: {{ number_format($invoice['totalAmount'], 2) }} {{ $invoice['currency'] }}</strong></th>
        </tr>
    </table>

    <!-- Reverse Charge Notice -->
    @if ($invoice['isRPDP'])
        <div class="reverse-charge">
            Reverse charge – VAT to be paid by the customer under local tax laws.
        </div>
    @endif

    <!-- Payment Information -->
    <div class="payment-info">
        <h3 style="font-size: 14px; margin: 0 0 8px; color: #2A3F54;">Payment Details</h3>
        Bank Name: {{ $bank['name'] }}<br>
        Account: {{ $bank['account'] }}/{{ $bank['code'] }}<br>
        <span class="payment-highlight">
            IBAN: {{ $bank['iban'] }}<br>
            SWIFT/BIC: {{ $bank['swift'] }}<br>
            Payment Reference: {{ $invoice['number'] }}
        </span>
    </div>

    <!-- Footer -->
    <div class="footer">
        Thank you for your business!<br>
        Questions? Contact {{ $supplier['email'] }}
    </div>
</div>
</body>
</html>
