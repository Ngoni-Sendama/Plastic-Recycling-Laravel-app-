<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Remittance - {{ $remittance->voucher_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 15px; margin-bottom: 20px; }
        .company { color: #059669; font-size: 22px; font-weight: 700; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .receipt-title { font-size: 14px; font-weight: 600; text-align: center; margin: 15px 0; color: #374151; }
        .row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .label { font-weight: 600; color: #374151; }
        .value { color: #1a1a1a; text-align: right; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #059669; color: white; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .table tr:last-child td { border-bottom: none; }
        .total { background: #f0fdf4; font-weight: 700; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">Highglen Plastic Industries</div>
        <div class="subtitle">Plastic Recycling Business Management</div>
    </div>

    <div class="receipt-title">PL-04 CASH REMITTANCE VOUCHER</div>
    <div class="row"><span class="label">Voucher number:</span><span class="value">{{ $remittance->voucher_number }}</span></div>
    <div class="row"><span class="label">Date:</span><span class="value">{{ \Carbon\Carbon::parse($remittance->date)->format('d M Y') }}</span></div>
    <div class="row"><span class="label">Period covered:</span><span class="value">{{ $remittance->period_covered ?: '-' }}</span></div>
    @if($remittance->recordedByUser)
        <div class="row"><span class="label">Recorded by:</span><span class="value">{{ $remittance->recordedByUser->name }}</span></div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Metric</th>
                <th style="text-align: right;">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Chips delivered</td>
                <td style="text-align: right;">{{ number_format($remittance->chips_delivered_kg, 3) }} kg</td>
            </tr>
            <tr>
                <td>Recovery price per kg</td>
                <td style="text-align: right;">${{ number_format($remittance->recovery_price_per_kg, 2) }}</td>
            </tr>
            <tr>
                <td>Sales revenue</td>
                <td style="text-align: right;">${{ number_format($remittance->sales_revenue, 2) }}</td>
            </tr>
            <tr>
                <td>Cash remitted</td>
                <td style="text-align: right;">${{ number_format($remittance->cash_remitted, 2) }}</td>
            </tr>
            <tr>
                <td>Max remittance due</td>
                <td style="text-align: right;">${{ number_format($remittance->max_remittance_due, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Balance retained</td>
                <td style="text-align: right;">${{ number_format($remittance->balance_retained, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Highglen Plastic Industries</p>
        <p>Generated on {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
