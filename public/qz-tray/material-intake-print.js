(() => {
    const payload = JSON.parse(document.getElementById('material-intake-payload').textContent);

    const ESC = '\x1B';
    const LF = '\x0A';

    const connectAndPrint = async () => {
        try {
            await qz.websocket.connect();

            const preferredName = (payload.printerName || '').trim();
            const printer = preferredName ? await qz.printers.find(preferredName) : await qz.printers.find();
            const config = qz.configs.create(printer);

            const data = [
                ESC + '\x40',
                ESC + '\x61' + '\x31',
                ESC + '\x45' + '\x0D',
                payload.company + LF,
                ESC + '\x45' + '\x0A',
                payload.title + LF,
                '--------------------------------' + LF,
                ESC + '\x61' + '\x30',
                'Date: ' + payload.date + LF,
                'GRN No.: ' + payload.grnNumber + LF,
                'Buyer: ' + payload.buyerName + LF,
                'Material: ' + payload.material + LF,
                'Gross Wt: ' + payload.grossWeight + ' kg' + LF,
                'Tare Wt: ' + payload.tareWeight + ' kg' + LF,
                'Net Wt: ' + payload.netWeight + ' kg' + LF,
                'Unit Price: $' + payload.unitPrice + LF,
                'Total Value: $' + payload.totalValue + LF,
                '--------------------------------' + LF,
                ESC + '\x61' + '\x31',
                'Thank you' + LF,
                LF + LF + LF,
                ESC + '\x69',
            ];

            await qz.print(config, data);
            window.close();
        } catch (error) {
            console.error(error);
            document.body.innerHTML = '<p>Unable to print. Keep QZ Tray open and try again.</p><pre>' + (error?.message || error) + '</pre>';
        }
    };

    window.addEventListener('load', connectAndPrint);
})();
