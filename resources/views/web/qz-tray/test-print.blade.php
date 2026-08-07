<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Test Print</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f3f4f6; }
        .card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; text-align: center; max-width: 400px; }
        .status { margin-top: 1rem; font-size: 0.875rem; color: #6b7280; }
        .success { color: #059669; }
        .error { color: #dc2626; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="font-size: 1.25rem; font-weight: 600;">Thermal Test Print</h1>
        <p style="color: #6b7280; margin-top: 0.5rem;">Connecting to QZ Tray...</p>
        <div id="status" class="status"></div>
    </div>

    <script src="{{ asset('qz-tray/qz-tray.js') }}"></script>
    <script>
        (() => {
            const status = document.getElementById('status');
            const printerName = @json($printerName);

            const ESC = '\x1B';
            const LF = '\x0A';

            const connectAndPrint = async () => {
                try {
                    await qz.websocket.connect();

                    const preferredName = (printerName || '').trim();
                    const printer = preferredName ? await qz.printers.find(preferredName) : await qz.printers.find();
                    const config = qz.configs.create(printer);

                    const data = [
                        ESC + '\x40',
                        ESC + '\x61' + '\x31',
                        ESC + '\x45' + '\x0D',
                        'Highglen Plastic Industries' + LF,
                        ESC + '\x45' + '\x0A',
                        'TEST RECEIPT' + LF,
                        '--------------------------------' + LF,
                        ESC + '\x61' + '\x30',
                        'Date: 07-Aug-2026' + LF,
                        'Time: 14:30' + LF,
                        'GRN No.: GRN-00123' + LF,
                        'Buyer: Acme Plastics' + LF,
                        'Material: PP - Polypropylene' + LF,
                        'Gross Wt: 1170.000 kg' + LF,
                        'Tare Wt: 5.000 kg' + LF,
                        'Net Wt: 1165.000 kg' + LF,
                        'Unit Price: $0.42' + LF,
                        'Total Value: $489.30' + LF,
                        '--------------------------------' + LF,
                        ESC + '\x61' + '\x31',
                        'Thank you' + LF,
                        LF + LF + LF,
                        ESC + '\x69',
                    ];

                    await qz.print(config, data);

                    status.textContent = 'Print sent successfully!';
                    status.className = 'status success';
                } catch (error) {
                    console.error(error);
                    status.textContent = error?.message || 'Print failed. Is QZ Tray running?';
                    status.className = 'status error';
                }
            };

            window.addEventListener('load', connectAndPrint);
        })();
    </script>
</body>
</html>
