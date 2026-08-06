<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Intake - {{ $intake->grn_number }}</title>
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

    <div class="title">CR-01 MATERIAL INTAKE / GOODS RECEIVED NOTE</div>

    <div class="row"><div class="label">GRN number</div><div class="value">{{ $intake->grn_number ?? '-' }}</div></div>
    <div class="row"><div class="label">Date</div><div class="value">{{ $intake->date ? \Carbon\Carbon::parse($intake->date)->format('d M Y') : '-' }}</div></div>
    <div class="row"><div class="label">Buyer</div><div class="value">{{ $intake->buyer?->buyer_name ?? $intake->buyer_name ?? '-' }}</div></div>
    <div class="row"><div class="label">Buyer contact</div><div class="value">{{ $intake->buyer?->contact_number ?? '-' }}</div></div>
    <div class="row"><div class="label">Material</div><div class="value">{{ $intake->material?->name ?? '-' }}</div></div>
    <div class="row"><div class="label">Gross weight</div><div class="value">{{ number_format((float) ($intake->gross_weight_kg ?? 0), 0) }} kg</div></div>
    <div class="row"><div class="label">Tare weight</div><div class="value">{{ number_format((float) ($intake->tare_weight_kg ?? 0), 0) }} kg</div></div>
    <div class="row"><div class="label">Net weight</div><div class="value">{{ number_format((float) ($intake->net_weight_kg ?? 0), 0) }} kg</div></div>
    <div class="row"><div class="label">Unit price</div><div class="value">${{ number_format((float) ($intake->unit_price ?? 0), 2) }}</div></div>
    <div class="row"><div class="label">Total value</div><div class="value">${{ number_format((float) ($intake->total_value ?? 0), 2) }}</div></div>

    <div class="footer">Generated {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
