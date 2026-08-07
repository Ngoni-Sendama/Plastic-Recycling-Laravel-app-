(() => {
    const payload = JSON.parse(document.getElementById('material-intake-payload').textContent);

    const ESC = '\x1B';
    const LF = '\x0A';
    const PAPER_WIDTH = 384;

    const prepareLogo = async () => {
        try {
            const res = await fetch('/qz-tray/icon.png', { cache: 'no-store' });
            if (!res.ok) return null;

            const blob = await res.blob();
            const bitmap = await createImageBitmap(blob);

            const maxW = 220;
            const scale = Math.min(maxW / bitmap.width, 1);
            const w = Math.round(bitmap.width * scale);
            const h = Math.round(bitmap.height * scale);

            const c = document.createElement('canvas');
            c.width = PAPER_WIDTH;
            c.height = h;
            const ctx = c.getContext('2d');

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, PAPER_WIDTH, h);
            ctx.drawImage(bitmap, Math.floor((PAPER_WIDTH - w) / 2), 0, w, h);

            const img = ctx.getImageData(0, 0, PAPER_WIDTH, h);
            const px = img.data;
            const bytes = [];

            for (let x = 0; x < PAPER_WIDTH; x++) {
                for (let y = 0; y < h; y += 8) {
                    let b = 0;
                    for (let bit = 0; bit < 8; bit++) {
                        const py = y + bit;
                        if (py < h) {
                            const i = (py * PAPER_WIDTH + x) * 4;
                            const gray = px[i] * 0.299 + px[i + 1] * 0.587 + px[i + 2] * 0.114;
                            if (gray < 180) b |= (1 << (7 - bit));
                        }
                    }
                    bytes.push(b);
                }
            }

            const cmd = new Uint8Array(8 + bytes.length);
            cmd[0] = 0x1D;
            cmd[1] = 0x76;
            cmd[2] = 0x30;
            cmd[3] = 0x00;
            cmd[4] = PAPER_WIDTH % 256;
            cmd[5] = 0;
            cmd[6] = h % 256;
            cmd[7] = Math.floor(h / 256);
            cmd.set(bytes, 8);

            return cmd;
        } catch {
            return null;
        }
    };

    const connectAndPrint = async () => {
        try {
            if (!qz.websocket.isActive()) await qz.websocket.connect();

            const printer = await qz.printers.find('POS58 Printer');
            const config = qz.configs.create(printer);
            const logo = await prepareLogo();

            const data = [
                ESC + '\x40',
                ...(logo ? [logo] : []),
                LF,
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
            document.body.innerHTML = '<p>Unable to print. Keep QZ Tray open and try again.</p>';
        }
    };

    window.addEventListener('load', connectAndPrint);
})();
