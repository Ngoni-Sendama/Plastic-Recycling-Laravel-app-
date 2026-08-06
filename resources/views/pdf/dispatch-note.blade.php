<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispatch Note - {{ $dispatch->dispatch_note_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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

    <div class="title">CR-03 STOCK DISPATCH NOTE</div>

    <div class="row"><div class="label">Dispatch note number</div><div class="value">{{ $dispatch->dispatch_note_number ?? '-' }}</div></div>
    <div class="row"><div class="label">Date</div><div class="value">{{ $dispatch->date ? \Carbon\Carbon::parse($dispatch->date)->format('d M Y') : '-' }}</div></div>
    <div class="row"><div class="label">Batch reference</div><div class="value">{{ $dispatch->batch_reference ?: '-' }}</div></div>
    <div class="row"><div class="label">Material</div><div class="value">{{ $dispatch->material?->name ?: '-' }}</div></div>
    <div class="row"><div class="label">Weight dispatched</div><div class="value">{{ number_format((float) ($dispatch->weight_dispatched_kg ?? 0), 3) }} kg</div></div>
    <div class="row"><div class="label">Transported by</div><div class="value">{{ $dispatch->transported_by ?: '-' }}</div></div>
    <div class="row"><div class="label">Recorded by</div><div class="value">{{ $dispatch->recordedByUser?->name ?: '-' }}</div></div>

    <div class="footer">Generated {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
