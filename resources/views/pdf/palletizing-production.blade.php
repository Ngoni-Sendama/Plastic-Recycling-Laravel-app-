<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PL-02 Palletizing Production Report - {{ $production->batch_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.35; }
        .header { text-align: center; margin-bottom: 12px; }
        .company { font-size: 22px; font-weight: 700; color: #1f3864; }
        .form-title { display: inline-block; margin-top: 6px; padding-bottom: 6px; border-bottom: 2px solid #1f3864; font-size: 14px; font-weight: 700; }
        .form-subtitle { margin-top: 4px; font-size: 11px; color: #6b7280; font-style: italic; }
        .meta-grid,.detail-grid,.signature-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .meta-grid td,.detail-grid td,.signature-grid td { border: 1px solid #111827; vertical-align: middle; padding: 8px 10px; }
        .meta-label,.field-label { background: #dce6f1; font-weight: 700; }
        .meta-value,.field-value { background: #fff; }
        .meta-grid .meta-label { width: 18%; }
        .meta-grid .meta-value { width: 32%; }
        .section { margin-top: 12px; }
        .section-title { margin: 0 0 6px; font-size: 12px; font-weight: 700; color: #1f3864; text-transform: uppercase; }
        .detail-grid .field-label { width: 28%; }
        .detail-grid .field-value { width: 22%; }
        .note { margin-top: 10px; font-size: 10.5px; color: #555; font-style: italic; }
        .signature-grid { margin-top: 18px; }
        .signature-grid td { height: 58px; text-align: center; vertical-align: bottom; padding-bottom: 10px; }
        .signature-label { font-size: 10px; color: #555; }
        .line { display: block; width: 100%; border-top: 1px solid #111827; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">HIGHGLEN PLASTIC INDUSTRIES</div>
        <div class="form-title">Palletizing Production Report</div>
        <div class="form-subtitle">Form PL-02 - Palletizing Office</div>
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
            <td class="meta-label">GRN No.(s) of chips used as input</td>
            <td class="meta-value">{{ $production->palletizingReceipt?->grn_number ?? $production->grn_reference ?? '-' }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Production Details</div>
        <table class="detail-grid">
            <tr>
                <td class="field-label">Input weight of chips (kg)</td>
                <td class="field-value">{{ number_format((float) ($production->chips_input_kg ?? 0), 3) }}</td>
                <td class="field-label">Output weight of pellets (kg)</td>
                <td class="field-value">{{ number_format((float) ($production->pellets_output_kg ?? 0), 3) }}</td>
            </tr>
            <tr>
                <td class="field-label">Processing loss (kg)</td>
                <td class="field-value">{{ number_format((float) ($production->loss_kg ?? 0), 3) }}</td>
                <td class="field-label">Processing loss (%)</td>
                <td class="field-value">{{ number_format((float) ($production->loss_percentage ?? 0) * 100, 2) }}%</td>
            </tr>
            <tr>
                <td class="field-label">Remarks</td>
                <td class="field-value" colspan="3">{{ $production->remarks ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="note">
        Note: Figures are posted to the Palletizing Production Log. Loss % = loss (kg) ÷ input weight (kg).
    </div>

    <div class="section">
        <div class="section-title">Signatures</div>
        <table class="signature-grid">
            <tr>
                <td>
                    <span class="line"></span>
                    <div class="signature-label">Machine operator</div>
                </td>
                <td>
                    <span class="line"></span>
                    <div class="signature-label">Supervisor (verified by)</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
