<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Crushing Production — {{ $production->batch_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 15px; margin-bottom: 20px; }
        .company { font-size: 22px; font-weight: 700; color: #059669; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .report-title { font-size: 14px; font-weight: 600; text-align: center; margin: 15px 0; color: #374151; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .info-label { font-weight: 600; color: #374151; }
        .info-value { color: #1a1a1a; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #059669; color: white; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .table tr:last-child td { border-bottom: none; }
        .total-row { background: #fef2f2; font-weight: 700; }
        .loss-warning { color: #dc2626; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">Highglen Ops</div>
        <div class="subtitle">Plastic Recycling Business Management</div>
    </div>

    <div class="report-title">CRUSHING PRODUCTION REPORT</div>

    <div class="info-row">
        <span class="info-label">Batch Number:</span>
        <span class="info-value">{{ $production->batch_number }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Date:</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($production->date)->format('d M Y') }}</span>
    </div>
    @if($production->material)
    <div class="info-row">
        <span class="info-label">Material:</span>
        <span class="info-value">{{ $production->material->code }} — {{ $production->material->name }}</span>
    </div>
    @endif
    @if($production->grn_reference)
    <div class="info-row">
        <span class="info-label">GRN Reference:</span>
        <span class="info-value">{{ $production->grn_reference }}</span>
    </div>
    @endif
    @if($production->recordedByUser)
    <div class="info-row">
        <span class="info-label">Recorded by:</span>
        <span class="info-value">{{ $production->recordedByUser->name }}</span>
    </div>
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
                <td>Input Weight</td>
                <td style="text-align: right;">{{ number_format($production->input_weight_kg, 3) }} kg</td>
            </tr>
            <tr>
                <td>Output Chips</td>
                <td style="text-align: right;">{{ number_format($production->output_chips_kg, 3) }} kg</td>
            </tr>
            <tr class="total-row">
                <td>Loss</td>
                <td style="text-align: right;" class="{{ $production->loss_percentage > 15 ? 'loss-warning' : '' }}">
                    {{ number_format($production->loss_kg, 3) }} kg ({{ number_format($production->loss_percentage, 1) }}%)
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Highglen Ops — Plastic Recycling Business Management</p>
        <p>Generated on {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
