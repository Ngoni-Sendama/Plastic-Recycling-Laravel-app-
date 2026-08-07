(() => {
    const payload = JSON.parse(document.getElementById('material-intake-payload').textContent);

    const printHtml = () => [
        '<!DOCTYPE html>',
        '<html lang="en">',
        '<head>',
        '<meta charset="utf-8">',
        '<style>',
        '@page { size: 58mm auto; margin: 0; }',
        '* { box-sizing: border-box; margin: 0; padding: 0; }',
        'body {',
        '  font-family: "Courier New", Courier, monospace;',
        '  font-size: 12px;',
        '  line-height: 1.3;',
        '  color: #000;',
        '  width: 48mm;',
        '  margin: 0 auto;',
        '  padding: 2mm 1mm;',
        '}',
        '.center { text-align: center; }',
        '.logo { width: 30mm; height: auto; display: block; margin: 0 auto 2mm; }',
        '.company { font-size: 14px; font-weight: bold; margin-bottom: 1mm; }',
        '.title { font-size: 12px; margin-bottom: 2mm; }',
        '.sep { border-top: 1px dashed #000; margin: 2mm 0; }',
        '.row { margin-bottom: 1.5mm; font-size: 11px; }',
        '.row .l { font-weight: bold; }',
        '.thanks { margin-top: 3mm; font-size: 12px; }',
        '</style>',
        '</head>',
        '<body>',
        '<div class="center">',
        payload.logoBase64 ? '<img class="logo" src="' + payload.logoBase64 + '" />' : '',
        '<div class="company">' + payload.company + '</div>',
        '<div class="title">' + payload.title + '</div>',
        '</div>',
        '<div class="sep"></div>',
        '<div class="row"><span class="l">Date:</span> ' + payload.date + '</div>',
        '<div class="row"><span class="l">GRN No.:</span> ' + payload.grnNumber + '</div>',
        '<div class="row"><span class="l">Buyer:</span> ' + payload.buyerName + '</div>',
        '<div class="row"><span class="l">Material:</span> ' + payload.material + '</div>',
        '<div class="row"><span class="l">Gross Wt:</span> ' + payload.grossWeight + ' kg</div>',
        '<div class="row"><span class="l">Tare Wt:</span> ' + payload.tareWeight + ' kg</div>',
        '<div class="row"><span class="l">Net Wt:</span> ' + payload.netWeight + ' kg</div>',
        '<div class="row"><span class="l">Unit Price:</span> $' + payload.unitPrice + '</div>',
        '<div class="row"><span class="l">Total Value:</span> $' + payload.totalValue + '</div>',
        '<div class="sep"></div>',
        '<div class="center thanks">Thank you</div>',
        '</body>',
        '</html>',
    ].join('');

    const connectAndPrint = async () => {
        try {
            if (!qz.websocket.isActive()) {
                await qz.websocket.connect();
            }

            const printer = await qz.printers.find('POS58 Printer');
            const config = qz.configs.create(printer, {
                size: { width: 58, height: 200 },
                units: 'mm',
                margins: 0,
            });
            const data = [{
                type: 'pixel',
                format: 'html',
                flavor: 'plain',
                data: printHtml(),
            }];

            await qz.print(config, data);
            window.close();
        } catch (error) {
            console.error(error);
            document.body.innerHTML = '<p>Unable to print through QZ Tray. Please keep QZ Tray open and try again.</p>';
        }
    };

    window.addEventListener('load', connectAndPrint);
})();
