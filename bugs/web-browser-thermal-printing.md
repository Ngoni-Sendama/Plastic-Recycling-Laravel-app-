# Web Browser Thermal Printing

## Issue

The web application needs thermal printing support in the browser using `mike42/escpos-php`.

The goal is to print directly to a Windows-shared thermal printer from the browser-facing Laravel app, using printer settings from `.env`.

## Current Need

The web app already has business records and receipt-style pages, but there is no browser thermal-print integration wired into the relevant Filament detail pages.

## Relevant Setup Notes

- Windows printer must be shared in Control Panel / Devices and Printers.
- The printer share name must be added to the Laravel `.env`.
- Example config:
  - `ESC_POS_PRINTER_CONNECTION=usb`
  - `ESC_POS_PRINTER_NAME="smb://Ngonie/POS58"`
- `php artisan config:clear` should apply updated printer settings.

## Likely Pages That Need Thermal Print

- Sales receipt page
- Palletizing Receipt detail page
- Cash Remittance detail page
- Dispatch detail page
- Material Intake detail page
- Crushing Production detail page

## Relevant Files

- `app/Http/Controllers/Api/PelletSaleController.php`
- `resources/views/pdf/sale-receipt.blade.php`
- `app/Filament/Resources/PelletSales/*`
- `app/Filament/Resources/PalletizingReceipts/*`
- `app/Filament/Resources/CashRemittances/*`
- `app/Filament/Resources/Dispatches/*`
- `app/Filament/Resources/MaterialIntakes/*`
- `app/Filament/Resources/CrushingProductions/*`

## Expected Behavior

- A thermal print action should be available on the browser/web detail pages.
- The selected printer should come from `.env`.
- The print should use the installed shared thermal printer on Windows.
- If thermal printing fails, the page should fail gracefully and not break the record view.

## Proposed Fix

- Add an `escpos-php` printer service in Laravel.
- Read printer connection/name from config.
- Add a thermal print action to the relevant Filament pages.
- Reuse each page's existing data model or resource payload to generate the receipt text.
- Keep PDF download/export as a fallback where useful.

## Acceptance Criteria

- Clicking the print action sends a ticket to the shared thermal printer.
- Printer settings are controlled from `.env`.
- The feature works from the browser-based admin app.
- No page crashes if the printer is offline or misconfigured.

