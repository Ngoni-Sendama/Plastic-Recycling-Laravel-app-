<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Receipt — {{ $sale->receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 15px; margin-bottom: 20px; }
        .company { font-size: 22px; font-weight: 700; color: #059669; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .receipt-title { font-size: 14px; font-weight: 600; text-align: center; margin: 15px 0; color: #374151; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .info-label { font-weight: 600; color: #374151; }
        .info-value { color: #1a1a1a; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #059669; color: white; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .table tr:last-child td { border-bottom: none; }
        .total-row { background: #f0fdf4; font-weight: 700; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 10px; color: #9ca3af; }
        .amount { font-size: 16px; font-weight: 700; color: #059669; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">Highglen Ops</div>
        <div class="subtitle">Plastic Recycling Business Management</div>
    </div>

    <div class="receipt-title">SALE RECEIPT</div>

    <div class="info-row">
        <span class="info-label">Receipt No:</span>
        <span class="info-value">{{ $sale->receipt_number }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Date:</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Customer:</span>
        <span class="info-value">{{ $sale->customer_name }}</span>
    </div>
    @if($sale->recordedByUser)
    <div class="info-row">
        <span class="info-label">Recorded by:</span>
        <span class="info-value">{{ $sale->recordedByUser->name }}</span>
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Quantity</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pellets sold</td>
                <td style="text-align: right;">{{ number_format($sale->kg_sold, 2) }} kg</td>
                <td style="text-align: right;">${{ number_format($sale->unit_price, 2) }}/kg</td>
                <td style="text-align: right;">${{ number_format($sale->amount_received, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3"><strong>Total Amount</strong></td>
                <td style="text-align: right;"><strong>${{ number_format($sale->amount_received, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="amount">
        TOTAL: ${{ number_format($sale->amount_received, 2) }}
    </div>

    <div class="footer">
        <p>Highglen Ops — Plastic Recycling Business Management</p>
        <p>Generated on {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
