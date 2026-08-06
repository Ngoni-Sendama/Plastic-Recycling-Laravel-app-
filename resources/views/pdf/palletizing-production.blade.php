<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Palletizing Production - {{ $production->batch_number }}</title>
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

    <div class="title">PL-02 PALLETIZING PRODUCTION REPORT</div>

    <div class="row"><div class="label">Batch number</div><div class="value">{{ $production->batch_number ?? '-' }}</div></div>
    <div class="row"><div class="label">Date</div><div class="value">{{ $production->date ? \Carbon\Carbon::parse($production->date)->format('d M Y') : '-' }}</div></div>
    <div class="row"><div class="label">GRN reference</div><div class="value">{{ $production->grn_reference ?: '-' }}</div></div>
    <div class="row"><div class="label">Chips input</div><div class="value">{{ number_format((float) ($production->chips_input_kg ?? 0), 1) }} kg</div></div>
    <div class="row"><div class="label">Pellets output</div><div class="value">{{ number_format((float) ($production->pellets_output_kg ?? 0), 1) }} kg</div></div>
    <div class="row"><div class="label">Loss</div><div class="value">{{ number_format((float) ($production->loss_kg ?? 0), 1) }} kg ({{ number_format((float) (($production->loss_percentage ?? 0) * 100), 1) }}%)</div></div>

    <div class="footer">Generated {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
