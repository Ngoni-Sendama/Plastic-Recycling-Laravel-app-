(() => {
    const payload = JSON.parse(document.getElementById('material-intake-payload').textContent);

    const ESC = '\x1B';
    const LF = '\x0A';

    const data = [
        ESC + '\x40',           // init
        ESC + '\x61' + '\x31',  // center align
        ESC + '\x45' + '\x0D',  // bold on
        payload.company + LF,
        ESC + '\x45' + '\x0A',  // bold off
        payload.title + LF,
        '--------------------------------' + LF,
        ESC + '\x61' + '\x30',  // left align
        '  Date: ' + payload.date + LF,
        '  GRN No.: ' + payload.grnNumber + LF,
        '  Buyer: ' + payload.buyerName + LF,
        '  Material: ' + payload.material + LF,
        '  Gross Wt: ' + payload.grossWeight + ' kg' + LF,
        '  Tare Wt: ' + payload.tareWeight + ' kg' + LF,
        '  Net Wt: ' + payload.netWeight + ' kg' + LF,
        '  Unit Price: $' + payload.unitPrice + LF,
        '  Total Value: $' + payload.totalValue + LF,
        '--------------------------------' + LF,
        ESC + '\x61' + '\x31',  // center align
        'Thank you' + LF,
        LF + LF + LF,
        ESC + '\x69',           // cut
    ];

    const connectAndPrint = async () => {
        try {
            if (!qz.websocket.isActive()) {
                await qz.websocket.connect();
            }

            const printer = await qz.printers.find('POS58 Printer');
            const config = qz.configs.create(printer);
            await qz.print(config, data);
            window.close();
        } catch (error) {
            console.error(error);
            document.body.innerHTML = '<p>Unable to print. Keep QZ Tray open and try again.</p>';
        }
    };

    window.addEventListener('load', connectAndPrint);
})();
