<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PL-03 Pellet Sales - Cash Sale Receipt - {{ $sale->receipt_number }}</title>
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
        <div class="form-title">Pellet Sales - Cash Sale Receipt</div>
        <div class="form-subtitle">Form PL-03 - Palletizing Office</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td class="meta-label">Receipt No.</td>
            <td class="meta-value">{{ $sale->receipt_number ?? '-' }}</td>
            <td class="meta-label">Date</td>
            <td class="meta-value">{{ $sale->date ? \Carbon\Carbon::parse($sale->date)->timezone('Africa/Harare')->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Customer name</td>
            <td class="meta-value">{{ $sale->customer_name ?? '-' }}</td>
            <td class="meta-label">Recorded by</td>
            <td class="meta-value">{{ $sale->recordedByUser?->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Sales Details</div>
        <table class="detail-grid">
            <tr>
                <td class="field-label">Pellets sold (kg)</td>
                <td class="field-value">{{ number_format((float) ($sale->kg_sold ?? 0), 3) }}</td>
                <td class="field-label">Unit price ($/kg)</td>
                <td class="field-value">${{ number_format((float) ($sale->unit_price ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="field-label">Total cash received ($)</td>
                <td class="field-value" colspan="3">${{ number_format((float) ($sale->amount_received ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="field-label">Remarks</td>
                <td class="field-value" colspan="3">{{ $sale->remarks ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="note">
        Note: Posted to the Pellet Sales Log same day. Cash received funds the cash remittance to the Crushing Office and the balance retained by the Palletizing Office.
    </div>

    <div class="section">
        <div class="section-title">Signatures</div>
        <table class="signature-grid">
            <tr>
                <td>
                    <span class="line"></span>
                    <div class="signature-label">Sold by (supervisor)</div>
                </td>
                <td>
                    <span class="line"></span>
                    <div class="signature-label">Customer</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
