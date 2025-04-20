<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('label.invoice') }}</title>

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

        .text-center {
            text-align: center;
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

        .avoid-page-break {
            page-break-inside: avoid;
        }

        .payment-info {
            margin-top: 15px;
            padding: 12px;
            border: 1px solid #c1cfde;
            background-color: #f3f6f9;
            border-radius: 6px;
            font-size: 11px;
        }

        .reverse-charge {
            font-size: 12px;
            color: #d32f2f;
            margin-top: 20px;
            text-align: center;
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
    <h1>{{ trans('label.invoice') }}</h1>

    <!-- Supplier -->
    <div class="frame" style="margin-right: 1%;">
        <span class="icon-title">{{ trans('label.supplier') }}</span>
        <strong>{{ $supplier['name'] }}</strong><br>
        <span class="address">
            {{ $supplier['address1'] }}<br>
            {{ $supplier['address2'] }}<br>
            {{ $supplier['address3'] }}<br>
        </span>
        {{ trans('label.vat') }}: {{ $supplier['vat'] }}<br>
        {{ trans('label.registration') }}: {{ $supplier['registration'] }}<br>
        {{ trans('label.email') }}: {{ $supplier['email'] }}<br>
        {{ trans('label.phone') }}: {{ $supplier['phone'] }}
    </div>

    <!-- Customer -->
    <div class="frame">
        <span class="icon-title">{{ trans('label.customer') }}</span>
        <strong>{{ $customer['name'] }}</strong><br>
        <span class="address">
            {{ $customer['address1'] }}<br>
            {{ $customer['address2'] }}<br>
            {{ $customer['address3'] }}<br>
        </span>
        {{ trans('label.vat') }}: {{ $customer['vat'] }}<br>
        @if ($customer['registration'])
            {{ trans('label.registration') }}: {{ $customer['registration'] }}<br>
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
                <strong>{{ trans('label.invoice') }} #:</strong> {{ $invoice['number'] }}<br>
            </td>
            <td class="text-right">
                <strong>{{ trans('label.issue_date') }}:</strong> {{ $invoice['date'] }}<br>
                <strong>{{ trans('label.due_date') }}:</strong> <span class="highlight">{{ $invoice['dueDate'] }}</span>
            </td>
        </tr>
    </table>

    <!-- Invoice Items -->
    <table>
        <thead>
        <tr>
            <th>{{ trans('label.description') }}</th>
            <th class="text-right">{{ trans('label.quantity') }}</th>
            <th class="text-right">{{ trans('label.unit_price') }}</th>
            <th class="text-right">{{ trans('label.amount') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($invoice['items'] as $item)
            <tr>
                <td>
                    @if($item->task->url)
                        <a href="{{ $item->task->url }}">{{ $item->task->name }}</a>
                    @else
                        {{ $item->task->name }}
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->hours, 1) }} {{ trans('label.shorts.hours') }}</td>
                <td class="text-right">{{ number_format($invoice['unit_price'], 2) }} {{ $invoice['currency'] }}</td>
                <td class="text-right">{{ number_format($invoice['unit_price'] * $item->hours, 2) }} {{ $invoice['currency'] }}</td>
            </tr>
        @endforeach
        </tbody>

        <!-- Invoice Summary -->
        <tfoot>
        @if (isset($invoice['tax']))
            <tr>
                <td class="text-right">
                    <strong>{{ trans('label.subtotal') }}
                        : {{ number_format($invoice['subtotal'], 2) }} {{ $invoice['currency'] }}</strong>
                </td>
            </tr>
            <tr>
                <td class="text-right">
                    <strong>{{ trans('pdf.invoice.tax') }}
                        : {{ number_format($invoice['tax'], 2) }} {{ $invoice['currency'] }}</strong></td>
            </tr>
        @endif
        <tr class="total-row">
            <th><strong>{{ trans('label.total') }}:</strong></th>
            <th class="text-right">{{ number_format($invoice['totalHours'], 1) }} {{ trans('label.shorts.hours') }}</th>
            <th class="text-center">---</th>
            <th class="text-right">{{ number_format($invoice['totalAmount'], 2) }} {{ $invoice['currency'] }}</th>
        </tr>
        </tfoot>
    </table>
</div>

<div class="avoid-page-break">
    <!-- Reverse Charge Notice -->
    @if ($invoice['isReverseCharge'])
        <div class="reverse-charge">
            {{ trans('pdf.invoice.reverse_charge') }}
        </div>
    @endif

    <!-- Payment Information -->
    <div class="payment-info">
        <h3 style="font-size: 14px; margin: 0 0 8px; color: #2A3F54;">Payment Details</h3>
        {{ trans('label.bank_name') }}: {{ $bank['name'] }}<br>
        {{ trans('label.bank_account') }}: {{ $bank['account'] }}/{{ $bank['code'] }}<br>
        <span class="payment-highlight">
                {{ trans('pdf.invoice.iban') }}: {{ $bank['iban'] }}<br>
                {{ trans('pdf.invoice.swift') }}: {{ $bank['swift'] }}<br>
                {{ trans('pdf.invoice.reference_id') }}: {{ $invoice['number'] }}
            </span>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ trans('pdf.invoice.thank_you') }}<br>
        {{ trans('pdf.invoice.questions_contact') }} {{ $supplier['email'] }}
    </div>
</div>
</body>
</html>
