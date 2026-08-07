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

            const print = async () => {
                try {
                    await qz.websocket.connect();

                    const printer = printerName
                        ? await qz.printers.find(printerName)
                        : await qz.printers.find();

                    const config = qz.configs.create(printer);

                    const data = [{
                        type: 'pixel',
                        format: 'html',
                        flavor: 'plain',
                        data: `
                            <div style="font-family: monospace; width: 300px; padding: 10px;">
                                <h2 style="text-align: center; margin: 0;">Highglen Plastic Industries</h2>
                                <hr style="border: 1px dashed #000;">
                                <p style="text-align: center; margin: 5px 0;"><strong>TEST PRINT</strong></p>
                                <p>Date: ${new Date().toLocaleDateString()}</p>
                                <p>Time: ${new Date().toLocaleTimeString()}</p>
                                <p>Printer: ${printer}</p>
                                <hr style="border: 1px dashed #000;">
                                <p style="text-align: center; font-size: 12px;">If you can read this,<br>your printer is working!</p>
                            </div>
                        `,
                    }];

                    await qz.print(config, data);

                    status.textContent = 'Print sent successfully!';
                    status.className = 'status success';
                } catch (error) {
                    console.error(error);
                    status.textContent = error?.message || 'Print failed. Is QZ Tray running?';
                    status.className = 'status error';
                }
            };

            window.addEventListener('load', print);
        })();
    </script>
</body>
</html>
