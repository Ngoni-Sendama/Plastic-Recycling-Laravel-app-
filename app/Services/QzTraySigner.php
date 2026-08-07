<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class QzTraySigner
{
    private const CERT_PATH = 'private/qz/qz-certificate.crt';

    private const KEY_PATH = 'private/qz/qz-private.key';

    public function certificate(): string
    {
        $this->ensureKeyPair();

        $certificate = File::get(storage_path('app/'.self::CERT_PATH));

        if ($certificate === false || $certificate === '') {
            throw new RuntimeException('QZ Tray certificate is not available.');
        }

        return $certificate;
    }

    public function sign(string $message): string
    {
        $this->ensureKeyPair();

        $privateKey = File::get(storage_path('app/'.self::KEY_PATH));

        if ($privateKey === false || $privateKey === '') {
            throw new RuntimeException('QZ Tray private key is not available.');
        }

        $keyResource = openssl_pkey_get_private($privateKey);

        if ($keyResource === false) {
            throw new RuntimeException('Unable to read the QZ Tray private key.');
        }

        $signed = '';

        if (! openssl_sign($message, $signed, $keyResource, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the QZ Tray payload.');
        }

        return base64_encode($signed);
    }

    private function ensureKeyPair(): void
    {
        $certificatePath = storage_path('app/'.self::CERT_PATH);
        $keyPath = storage_path('app/'.self::KEY_PATH);

        if (File::exists($certificatePath) && File::exists($keyPath)) {
            return;
        }

        File::ensureDirectoryExists(dirname($certificatePath));

        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($privateKey === false) {
            throw new RuntimeException('Unable to generate the QZ Tray private key.');
        }

        $csr = openssl_csr_new([
            'commonName' => config('app.name', 'Plastic Recycling Business App'),
            'organizationName' => 'Highglen Plastic Industries',
            'countryName' => 'ZW',
            'stateOrProvinceName' => 'Harare',
            'localityName' => 'Harare',
        ], $privateKey, [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($csr === false) {
            throw new RuntimeException('Unable to generate the QZ Tray certificate request.');
        }

        $certificate = openssl_csr_sign($csr, null, $privateKey, 3650, ['digest_alg' => 'sha256']);

        if ($certificate === false) {
            throw new RuntimeException('Unable to generate the QZ Tray certificate.');
        }

        $privateKeyOutput = '';
        if (! openssl_pkey_export($privateKey, $privateKeyOutput)) {
            throw new RuntimeException('Unable to export the QZ Tray private key.');
        }

        $certificateOutput = '';
        if (! openssl_x509_export($certificate, $certificateOutput)) {
            throw new RuntimeException('Unable to export the QZ Tray certificate.');
        }

        File::put($keyPath, $privateKeyOutput);
        File::put($certificatePath, $certificateOutput);
    }
}
