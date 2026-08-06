<?php

namespace App\Services;

use App\Models\PelletSale;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use RuntimeException;
use Throwable;

class PelletSaleThermalPrinter
{
    public function print(PelletSale $sale): void
    {
        $printer = null;

        try {
            $printer = new Printer($this->connector(), CapabilityProfile::load('simple'));

            $this->printHeader($printer);
            $this->printBody($printer, $sale);
            $this->printFooter($printer);

            $printer->cut();
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                'Unable to print the pellet sale receipt.',
                0,
                $throwable,
            );
        } finally {
            if ($printer instanceof Printer) {
                $printer->close();
            }
        }
    }

    protected function connector(): WindowsPrintConnector|NetworkPrintConnector|FilePrintConnector
    {
        $connection = (string) config('escpos.connection', 'windows');
        $name = (string) config('escpos.name', '');
        $host = (string) config('escpos.host', '127.0.0.1');
        $port = (int) config('escpos.port', 9100);

        return match ($connection) {
            'network' => new NetworkPrintConnector($host, $port),
            'file' => new FilePrintConnector($this->resolveFileTarget($name)),
            default => $this->windowsConnector($name),
        };
    }

    protected function printHeader(Printer $printer): void
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        $logoPath = public_path('icon.png');

        if (is_file($logoPath)) {
            $resizedLogoPath = $this->resizeLogoForReceipt($logoPath);

            $printer->bitImage(EscposImage::load($resizedLogoPath), Printer::IMG_DEFAULT);
            $printer->feed(1);
        }

        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->text("Highglen Plastic Industries\n");
        $printer->selectPrintMode();
        $printer->text("Pellet Sales Receipt\n");
        $printer->text("--------------------------------\n");
    }

    protected function printBody(Printer $printer, PelletSale $sale): void
    {
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->text("SALE DETAILS\n");
        $printer->selectPrintMode();

        $printer->text(sprintf("Receipt No: %s\n", $sale->receipt_number ?: '-'));
        $printer->text(sprintf('Date: %s'."\n", $sale->date?->toDateString() ?? '-'));
        $printer->text(sprintf("Customer: %s\n", $sale->customer_name ?: '-'));
        $printer->text(sprintf("Kg Sold: %s kg\n", number_format((float) $sale->kg_sold, 2)));
        $printer->text(sprintf("Unit Price: %s\n", $this->money($sale->unit_price)));
        $printer->text(sprintf("Amount Received: %s\n", $this->money($sale->amount_received)));
        $printer->text(sprintf("Recorded By: %s\n", $sale->recordedByUser?->name ?: '-'));
    }

    protected function printFooter(Printer $printer): void
    {
        $printer->feed(1);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Thank you\n");
    }

    protected function resolveFileTarget(string $name): string
    {
        return $name !== '' ? $name : 'php://stdout';
    }

    protected function windowsConnector(string $name): WindowsPrintConnector
    {
        if ($name === '') {
            throw new RuntimeException('ESC/POS printer name is not configured.');
        }

        return new WindowsPrintConnector($name);
    }

    protected function money(float|string|null $value): string
    {
        return '$'.number_format((float) $value, 2);
    }

    protected function resizeLogoForReceipt(string $sourcePath): string
    {
        if (! function_exists('imagecreatefrompng')) {
            return $sourcePath;
        }

        $image = imagecreatefrompng($sourcePath);

        if ($image === false) {
            return $sourcePath;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $targetWidth = 72;

        if ($sourceWidth <= $targetWidth) {
            imagedestroy($image);

            return $sourcePath;
        }

        $targetHeight = (int) round(($targetWidth / $sourceWidth) * $sourceHeight);
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        $tempPath = storage_path('app/temp-pellet-sale-logo.png');
        imagepng($resized, $tempPath);

        imagedestroy($image);
        imagedestroy($resized);

        return $tempPath;
    }
}
