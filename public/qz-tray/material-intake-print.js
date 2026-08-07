(() => {
const payloadElement = document.getElementById('material-intake-payload');

if (!payloadElement) {
    console.error('material-intake-payload element not found');
    return;
}

const payload = JSON.parse(payloadElement.textContent);

const ESC = '\x1B';
const LF = '\x0A';

const logoUrl = '/qz-tray/icon.png';

/*
 * Most 58mm thermal printers have a printable width around 384 dots
 * at 203 DPI.
 *
 * Keep the actual logo a little smaller so it has margins.
 */
const PAPER_WIDTH = 384;
const LOGO_MAX_WIDTH = 100;

/**
 * Load logo, resize it, center it on a 384px canvas,
 * convert to PNG Base64.
 */
const prepareLogo = async (url) => {
    const response = await fetch(url, {
        cache: 'no-store'
    });

    if (!response.ok) {
        throw new Error(
            `Unable to load logo. HTTP ${response.status}`
        );
    }

    const blob = await response.blob();
    const bitmap = await createImageBitmap(blob);

    /*
     * Calculate resized dimensions while preserving aspect ratio.
     */
    const scale = Math.min(
        LOGO_MAX_WIDTH / bitmap.width,
        1
    );

    const resizedWidth = Math.round(bitmap.width * scale);
    const resizedHeight = Math.round(bitmap.height * scale);

    /*
     * Canvas is full printer width.
     * This ensures the image itself is centered.
     */
    const canvas = document.createElement('canvas');

    canvas.width = PAPER_WIDTH;
    canvas.height = resizedHeight + 10;

    const ctx = canvas.getContext('2d');

    /*
     * Thermal printer background should be white.
     */
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(
        0,
        0,
        canvas.width,
        canvas.height
    );

    /*
     * Center resized logo.
     */
    const x = Math.round(
        (PAPER_WIDTH - resizedWidth) / 2
    );

    ctx.drawImage(
        bitmap,
        x,
        5,
        resizedWidth,
        resizedHeight
    );

    /*
     * Convert to grayscale / high contrast.
     * This normally gives thermal printers a cleaner logo.
     */
    const imageData = ctx.getImageData(
        0,
        0,
        canvas.width,
        canvas.height
    );

    const pixels = imageData.data;

    for (let i = 0; i < pixels.length; i += 4) {
        const r = pixels[i];
        const g = pixels[i + 1];
        const b = pixels[i + 2];

        /*
         * Luminance.
         */
        const gray =
            (r * 0.299) +
            (g * 0.587) +
            (b * 0.114);

        /*
         * Simple threshold for black/white printing.
         *
         * Increase 180 if your logo prints too light.
         * Decrease it if it prints too dark.
         */
        const value = gray < 180 ? 0 : 255;

        pixels[i] = value;
        pixels[i + 1] = value;
        pixels[i + 2] = value;
        pixels[i + 3] = 255;
    }

    ctx.putImageData(imageData, 0, 0);

    /*
     * QZ accepts the Base64 portion.
     */
    return canvas
        .toDataURL('image/png')
        .split(',')[1];
};

const connectAndPrint = async () => {
    try {
        /*
         * Connect QZ Tray
         */
        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }

        /*
         * Find your printer
         */
        const printer = await qz.printers.find(
            'POS58 Printer'
        );

        console.log('Printer found:', printer);

        /*
         * Raw ESC/POS configuration.
         */
        const config = qz.configs.create(
            printer,
            {
                encoding: 'UTF-8'
            }
        );

        /*
         * Resize/prepare logo before printing.
         */
        const logoBase64 = await prepareLogo(
            logoUrl
        );

        /*
         * IMPORTANT:
         *
         * Do NOT create:
         *
         * const receiptData = [...]
         * data = [logo, receiptData]
         *
         * because receiptData becomes a nested array.
         *
         * Instead every raw ESC/POS command/text is
         * directly inside this QZ data array.
         */
        const data = [

            /*
             * Initialize printer
             */
            ESC + '\x40',

            /*
             * Center image
             */
            ESC + '\x61' + '\x31',

            /*
             * Logo
             */
            {
                type: 'raw',
                format: 'image',
                flavor: 'base64',
                data: logoBase64,
                options: {
                    language: 'ESCPOS',
                    dotDensity: 'single',
                    quantization: 'black'
                }
            },

            /*
             * Space after logo
             */
            LF,

            /*
             * Center receipt heading
             */
            ESC + '\x61' + '\x31',

            /*
             * Bold ON
             */
            ESC + '\x45' + '\x01',

            String(payload.company ?? '') + LF,

            /*
             * Bold OFF
             */
            ESC + '\x45' + '\x00',

            String(payload.title ?? '') + LF,

            '--------------------------------' + LF,

            /*
             * Left alignment
             */
            ESC + '\x61' + '\x30',

            'Date: ' +
                String(payload.date ?? '') +
                LF,

            'GRN No.: ' +
                String(payload.grnNumber ?? '') +
                LF,

            'Buyer: ' +
                String(payload.buyerName ?? '') +
                LF,

            'Material: ' +
                String(payload.material ?? '') +
                LF,

            'Gross Wt: ' +
                String(payload.grossWeight ?? '') +
                ' kg' +
                LF,

            'Tare Wt: ' +
                String(payload.tareWeight ?? '') +
                ' kg' +
                LF,

            'Net Wt: ' +
                String(payload.netWeight ?? '') +
                ' kg' +
                LF,

            'Unit Price: $' +
                String(payload.unitPrice ?? '') +
                LF,

            'Total Value: $' +
                String(payload.totalValue ?? '') +
                LF,

            '--------------------------------' +
                LF,

            /*
             * Footer
             */
            ESC + '\x61' + '\x31',

            'Thank you' + LF,

            /*
             * Feed paper
             */
            LF,
            LF,
            LF,

            /*
             * Cut paper.
             *
             * Some cheap POS58 models don't support
             * the cutter. If yours doesn't, remove this.
             */
            ESC + '\x69'
        ];

        console.log('Sending print job...');

        await qz.print(config, data);

        console.log('Print successful');

        window.close();

    } catch (error) {
        console.error(
            'Print error:',
            error
        );

        document.body.innerHTML = `
            <div style="
                font-family: Arial, sans-serif;
                padding: 20px;
                text-align: center;
            ">
                <h3>Unable to print</h3>

                <p>
                    Keep QZ Tray open and try again.
                </p>

                <pre style="
                    white-space: pre-wrap;
                    text-align: left;
                    background: #f5f5f5;
                    padding: 10px;
                ">${
                    String(
                        error?.message ??
                        error
                    )
                }</pre>
            </div>
        `;
    }
};

window.addEventListener(
    'load',
    connectAndPrint
);


})();