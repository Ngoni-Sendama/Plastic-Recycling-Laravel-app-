<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CR-01 Material Intake - {{ $intake->grn_number }}</title>
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

        .detail-grid .field-spacer {
            width: 2%;
            background: #fff;
            border: none;
            padding: 0;
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

        .badge {
            display: inline-block;
            min-width: 52px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #1f3864;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
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
            <div class="form-title">Material Intake / Goods Received Note</div>
            <div class="form-subtitle">Form CR-01 - Crushing Office</div>
        </div>

        <table class="meta-grid">
            <tr>
                <td class="meta-label">GRN No.</td>
                <td class="meta-value">{{ $intake->grn_number ?? '-' }}</td>
                <td class="meta-label">Date</td>
                <td class="meta-value">{{ $intake->date ? \Carbon\Carbon::parse($intake->date)->timezone('Africa/Harare')->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Buyer name</td>
                <td class="meta-value">{{ $intake->buyer?->buyer_name ?? $intake->buyer_name ?? '-' }}</td>
                <td class="meta-label">Buyer contact</td>
                <td class="meta-value">{{ $intake->buyer?->contact_number ?? '-' }}</td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Material Details</div>
            <table class="detail-grid">
                <tr>
                    <td class="field-label">Material type (PP / HD / LD - circle one)</td>
                    <td class="field-value" colspan="3">{{ $intake->material?->code ? $intake->material->code.' - '.$intake->material->name : ($intake->material?->name ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="field-label">Gross weight (kg)</td>
                    <td class="field-value">{{ number_format((float) ($intake->gross_weight_kg ?? 0), 3) }}</td>
                    <td class="field-label">Tare weight (kg)</td>
                    <td class="field-value">{{ number_format((float) ($intake->tare_weight_kg ?? 0), 3) }}</td>
                </tr>
                <tr>
                    <td class="field-label">Net weight delivered (kg)</td>
                    <td class="field-value">{{ number_format((float) ($intake->net_weight_kg ?? 0), 3) }}</td>
                    <td class="field-label">Agreed unit price ($/kg)</td>
                    <td class="field-value">${{ number_format((float) ($intake->unit_price ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="field-label">Total value payable to buyer ($)</td>
                    <td class="field-value" colspan="3">${{ number_format((float) ($intake->total_value ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="field-label">Remarks / condition of material</td>
                    <td class="field-value" colspan="3">{{ $intake->remarks ?? $intake->note ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="note">
            Note: Net weight and total value are posted to the Material Intake Log in the stock control spreadsheet. Original copy to buyer, duplicate retained by Crushing Office.
        </div>

        <div class="section">
            <div class="section-title">Signatures</div>
            <table class="signature-grid">
                <tr>
                    <td>
                        <span class="line"></span>
                        <div class="signature-label">Resident Stock Controller (received by)</div>
                    </td>
                    <td>
                        <span class="line"></span>
                        <div class="signature-label">Buyer / supplier (delivered by)</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section" style="margin-top: 18px;">
            <table class="meta-grid">
                <tr>
                    <td class="meta-label">Recorded by</td>
                    <td class="meta-value">{{ $intake->recordedByUser?->name ?? '-' }}</td>
                    <td class="meta-label">Generated</td>
                    <td class="meta-value">{{ now()->timezone('Africa/Harare')->format('d M Y H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
