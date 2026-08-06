<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Receipt - {{ $sale->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Helvetica, Arial, sans-serif; color: #111827; padding: 28px; }
        .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 14px; margin-bottom: 18px; }
        .company { color: #059669; font-size: 22px; font-weight: 700; }
        .subtitle { color: #6b7280; font-size: 11px; margin-top: 4px; }
        .title { text-align: center; font-size: 14px; font-weight: 700; margin: 14px 0 18px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .label { font-weight: 700; color: #374151; }
        .value { text-align: right; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">Highglen Plastic Industries</div>
        <div class="subtitle">Plastic Recycling Business Management</div>
    </div>

    <div class="title">SALE RECEIPT</div>

    <div class="row"><div class="label">Receipt No:</div><div class="value">{{ $sale->receipt_number ?? '-' }}</div></div>
    <div class="row"><div class="label">Date:</div><div class="value">{{ $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M Y') : '-' }}</div></div>
    <div class="row"><div class="label">Customer:</div><div class="value">{{ $sale->customer_name ?? '-' }}</div></div>
    @if($sale->recordedByUser)
        <div class="row"><div class="label">Recorded by:</div><div class="value">{{ $sale->recordedByUser->name }}</div></div>
    @endif

    <div class="row"><div class="label">Pellets sold</div><div class="value">{{ number_format((float) ($sale->kg_sold ?? 0), 3) }} kg</div></div>
    <div class="row"><div class="label">Unit price</div><div class="value">${{ number_format((float) ($sale->unit_price ?? 0), 2) }}/kg</div></div>
    <div class="row"><div class="label">Amount received</div><div class="value">${{ number_format((float) ($sale->amount_received ?? 0), 2) }}</div></div>

    <div class="footer">
        <p>Highglen Plastic Industries</p>
        <p>Generated on {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
