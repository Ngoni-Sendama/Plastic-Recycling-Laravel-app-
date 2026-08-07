<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CR-02 Crushing Production - {{ $production->batch_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.35;
        }

        .page {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .company {
            font-size: 22px;
            font-weight: 700;
            color: #1f3864;
            letter-spacing: 0.3px;
        }

        .form-title {
            display: inline-block;
            margin-top: 6px;
            padding-bottom: 6px;
            border-bottom: 2px solid #1f3864;
            font-size: 14px;
            font-weight: 700;
        }

        .form-subtitle {
            margin-top: 4px;
            font-size: 11px;
            color: #6b7280;
            font-style: italic;
        }

        .meta-grid,
        .detail-grid,
        .signature-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-grid td,
        .detail-grid td,
        .signature-grid td {
            border: 1px solid #111827;
            vertical-align: middle;
            padding: 8px 10px;
        }

        .meta-label,
        .field-label {
            background: #dce6f1;
            font-weight: 700;
        }

        .meta-value,
        .field-value {
            background: #fff;
        }

        .meta-grid .meta-label {
            width: 18%;
        }

        .meta-grid .meta-value {
            width: 32%;
        }

        .section {
            margin-top: 12px;
        }

        .section-title {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 700;
            color: #1f3864;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .detail-grid .field-label {
            width: 28%;
        }

        .detail-grid .field-value {
            width: 22%;
        }

        .detail-grid .field-full {
            width: 72%;
        }

        .note {
            margin-top: 10px;
            font-size: 10.5px;
            color: #555;
            font-style: italic;
        }

        .signature-grid {
            margin-top: 18px;
        }

        .signature-grid td {
            height: 58px;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 10px;
        }

        .signature-label {
            font-size: 10px;
            color: #555;
        }

        .line {
            display: block;
            width: 100%;
            border-top: 1px solid #111827;
            margin-bottom: 6px;
        }

        .muted {
            color: #6b7280;
        }

        .right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="company">HIGHGLEN PLASTIC INDUSTRIES</div>
            <div class="form-title">Crushing Production Report</div>
            <div class="form-subtitle">Form CR-02 - Crushing Office</div>
        </div>

        <table class="meta-grid">
            <tr>
                <td class="meta-label">Batch No.</td>
                <td class="meta-value">{{ $production->batch_number ?? '-' }}</td>
                <td class="meta-label">Date</td>
                <td class="meta-value">{{ $production->date ? \Carbon\Carbon::parse($production->date)->timezone('Africa/Harare')->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Recorded by</td>
                <td class="meta-value">{{ $production->recordedByUser?->name ?? '-' }}</td>
                <td class="meta-label">Material</td>
                <td class="meta-value">{{ $production->material?->code ? $production->material->code.' - '.$production->material->name : ($production->material?->name ?? '-') }}</td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Production Details</div>
            <table class="detail-grid">
                <tr>
                    <td class="field-label">GRN No.(s) used as input</td>
                    <td class="field-value" colspan="3">
                        {{ $production->materialIntake?->grn_number ?? $production->grn_reference ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Material type (PP / HD / LD - circle one)</td>
                    <td class="field-value" colspan="3">
                        {{ $production->material?->code ? $production->material->code.' - '.$production->material->name : ($production->material?->name ?? '-') }}
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Input weight of raw material (kg)</td>
                    <td class="field-value">{{ number_format((float) ($production->input_weight_kg ?? 0), 3) }}</td>
                    <td class="field-label">Output weight of chips (kg)</td>
                    <td class="field-value">{{ number_format((float) ($production->output_chips_kg ?? 0), 3) }}</td>
                </tr>
                <tr>
                    <td class="field-label">Loss weight (kg)</td>
                    <td class="field-value">{{ number_format((float) ($production->loss_kg ?? 0), 3) }}</td>
                    <td class="field-label">Loss percentage</td>
                    <td class="field-value">{{ number_format((float) ($production->loss_percentage ?? 0) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td class="field-label">Remarks</td>
                    <td class="field-value" colspan="3">{{ $production->remarks ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="note">
            Note: Figures are posted to the Crushing Production Log. Loss % = loss (kg) ÷ input weight (kg).
        </div>

        <div class="section">
            <div class="section-title">Signatures</div>
            <table class="signature-grid">
                <tr>
                    <td>
                        <span class="line"></span>
                        <div class="signature-label">Crusher Operator</div>
                    </td>
                    <td>
                        <span class="line"></span>
                        <div class="signature-label">Resident stock controller (verified by)</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section" style="margin-top: 18px;">
            <table class="meta-grid">
                <tr>
                    <td class="meta-label">Generated</td>
                    <td class="meta-value">{{ now()->timezone('Africa/Harare')->format('d M Y H:i') }}</td>
                    <td class="meta-label">Batch status</td>
                    <td class="meta-value">{{ $production->deleted_at ? 'Archived' : 'Active' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
