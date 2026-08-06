<?php

namespace App\Services;

use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use RuntimeException;
use Throwable;

class RecordThermalPrinter
{
    /**
     * @param  array<string, string>  $lines
     */
    public function print(string $title, array $lines): void
    {
        $printer = null;

        try {
            $printer = new Printer($this->connector(), CapabilityProfile::load('simple'));

            $this->printHeader($printer, $title);

            $printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($lines as $label => $value) {
                $printer->text(sprintf('%s: %s'."\n", $label, $value));
            }

            $printer->feed(1);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Thank you\n");
            $printer->cut();
        } catch (Throwable $throwable) {
            throw new RuntimeException('Unable to print the record.', 0, $throwable);
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

    protected function printHeader(Printer $printer, string $title): void
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
        $printer->text($title."\n");
        $printer->text("--------------------------------\n");
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

        $tempPath = storage_path('app/temp-record-logo.png');
        imagepng($resized, $tempPath);

        imagedestroy($image);
        imagedestroy($resized);

        return $tempPath;
    }
}
