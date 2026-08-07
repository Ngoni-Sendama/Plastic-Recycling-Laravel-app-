<x-filament-panels::page>
    {{ $this->content }}

    <x-filament-actions::modals />

    @php
        $printPayloadJson = json_encode($printPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @endphp

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qz-tray/2.2.4/qz-tray.js" integrity="sha512-6g4VpCzOq1hYd1K3xq+2Zp2A0gT7vA6mX8B9yYhQK2i4R5vQ1B7+Qj7oG8xg1JdBv5Q5pDq1K1hB5VxP2Dg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        (() => {
            const payload = @json($printPayload);
            const printHtml = () => `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.35; }
        .page { width: 100%; }
        .header { text-align: center; margin-bottom: 12px; }
        .company { font-size: 22px; font-weight: 700; color: #1f3864; letter-spacing: 0.3px; }
        .form-title { display: inline-block; margin-top: 6px; padding-bottom: 6px; border-bottom: 2px solid #1f3864; font-size: 14px; font-weight: 700; }
        .form-subtitle { margin-top: 4px; font-size: 11px; color: #6b7280; font-style: italic; }
        .meta-grid,.detail-grid,.signature-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .meta-grid td,.detail-grid td,.signature-grid td { border: 1px solid #111827; vertical-align: middle; padding: 8px 10px; }
        .meta-label,.field-label { background: #dce6f1; font-weight: 700; }
        .meta-value,.field-value { background: #fff; }
        .meta-grid .meta-label { width: 18%; }
        .meta-grid .meta-value { width: 32%; }
        .section { margin-top: 12px; }
        .section-title { margin: 0 0 6px; font-size: 12px; font-weight: 700; color: #1f3864; text-transform: uppercase; letter-spacing: 0.2px; }
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
    <div class="page">
        <div class="header">
            <div class="company">${payload.company}</div>
            <div class="form-title">${payload.title}</div>
            <div class="form-subtitle">${payload.form}</div>
        </div>
        <table class="meta-grid">
            <tr>
                <td class="meta-label">GRN No.</td>
                <td class="meta-value">${payload.grnNumber}</td>
                <td class="meta-label">Date</td>
                <td class="meta-value">${payload.date}</td>
            </tr>
            <tr>
                <td class="meta-label">Buyer name</td>
                <td class="meta-value">${payload.buyerName}</td>
                <td class="meta-label">Buyer contact</td>
                <td class="meta-value">${payload.buyerContact}</td>
            </tr>
        </table>
        <div class="section">
            <div class="section-title">Material Details</div>
            <table class="detail-grid">
                <tr>
                    <td class="field-label">Material type (PP / HD / LD - circle one)</td>
                    <td class="field-value" colspan="3">${payload.material}</td>
                </tr>
                <tr>
                    <td class="field-label">Gross weight (kg)</td>
                    <td class="field-value">${payload.grossWeight}</td>
                    <td class="field-label">Tare weight (kg)</td>
                    <td class="field-value">${payload.tareWeight}</td>
                </tr>
                <tr>
                    <td class="field-label">Net weight delivered (kg)</td>
                    <td class="field-value">${payload.netWeight}</td>
                    <td class="field-label">Agreed unit price ($/kg)</td>
                    <td class="field-value">$${payload.unitPrice}</td>
                </tr>
                <tr>
                    <td class="field-label">Total value payable to buyer ($)</td>
                    <td class="field-value" colspan="3">$${payload.totalValue}</td>
                </tr>
                <tr>
                    <td class="field-label">Remarks / condition of material</td>
                    <td class="field-value" colspan="3">${payload.remarks}</td>
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
    </div>
</body>
</html>`;

            const handlePrint = async () => {
                if (typeof qz === 'undefined') {
                    return;
                }

                try {
                    qz.security.setCertificatePromise((resolve) => resolve(''));
                    qz.security.setSignaturePromise((toSign) => (resolve) => resolve(''));

                    if (!qz.websocket.isActive()) {
                        await qz.websocket.connect();
                    }

                    const printer = await qz.printers.getDefault();
                    const config = qz.configs.create(printer, {
                        scaleContent: true,
                        copies: 1,
                        rasterize: false,
                    });

                    const data = [
                        {
                            type: 'html',
                            format: 'plain',
                            flavor: 'plain',
                            data: printHtml(),
                        },
                    ];

                    await qz.print(config, data);
                } catch (error) {
                    console.error('QZ print error', error);
                    window.alert('Unable to print through QZ Tray. Please confirm QZ Tray is running and the printer is connected.');
                }
            };

            window.addEventListener('material-intake-qz-print', handlePrint);
        })();
    </script>
</x-filament-panels::page>
