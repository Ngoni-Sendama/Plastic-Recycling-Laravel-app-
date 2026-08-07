<x-filament-panels::page>
    <form wire:submit="create">
        <div class="space-y-6">
            <x-filament::section>
                <div class="space-y-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        QZ Tray will search for printers on this computer and let you choose the one you want to save.
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            id="detect-printers"
                            class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500"
                        >
                            Detect printers
                        </button>

                        <span id="printer-status" class="text-sm text-gray-500 dark:text-gray-400"></span>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Detected printers</label>
                        <select
                            id="printer-select"
                            class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <option value="">No printers detected yet</option>
                        </select>
                    </div>
                </div>
            </x-filament::section>

            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" size="md">
                    Save Printer
                </x-filament::button>
            </div>
        </div>
    </form>

    <script src="{{ asset('qz-tray/qz-tray.js') }}"></script>
    <script>
        (() => {
            const status = document.getElementById('printer-status');
            const button = document.getElementById('detect-printers');
            const select = document.getElementById('printer-select');

            const setPrinterName = (name) => {
                if (!name) {
                    status.textContent = 'No printer selected.';
                    return;
                }

                @this.set('data.printer_name', name);
                status.textContent = `Selected: ${name}`;
                console.log('[printer-settings] set printer via Livewire', name);
            };

            const renderPrinters = (printers) => {
                select.innerHTML = '';

                if (!Array.isArray(printers) || printers.length === 0) {
                    select.innerHTML = '<option value="">No printers detected</option>';
                    return;
                }

                printers.forEach((printer) => {
                    const option = document.createElement('option');
                    option.value = printer;
                    option.textContent = printer;
                    select.appendChild(option);
                });

                console.log('[printer-settings] printers returned', printers);
                status.textContent = `Found ${printers.length} printer(s). Choose one from the list.`;
                select.value = '';
            };

            const detectPrinters = async () => {
                try {
                    status.textContent = 'Connecting to QZ Tray...';
                    console.log('[printer-settings] detecting printers');

                    if (typeof qz === 'undefined') {
                        status.textContent = 'QZ Tray is not loaded on this page.';
                        console.warn('[printer-settings] qz is undefined');
                        return;
                    }

                    if (!qz.websocket.isActive()) {
                        await qz.websocket.connect();
                    }

                    const printers = await qz.printers.find();
                    console.log('[printer-settings] raw printer response', printers);

                    if (!Array.isArray(printers) || printers.length === 0) {
                        status.textContent = 'No printers found.';
                        return;
                    }

                    renderPrinters(printers);
                } catch (error) {
                    console.error('Printer detection failed', error);
                    status.textContent = error?.message || 'Unable to detect printers.';
                }
            };

            button?.addEventListener('click', detectPrinters);
            select?.addEventListener('change', (event) => {
                console.log('[printer-settings] dropdown changed', event.target.value);
                setPrinterName(event.target.value);
            });
            window.addEventListener('load', detectPrinters);
        })();
    </script>
</x-filament-panels::page>
