<?php

declare(strict_types=1);

$file = __DIR__.'/../public/docs/openapi.yaml';
$lines = file($file);
if ($lines === false) {
    fwrite(STDERR, "Cannot read $file\n");
    exit(1);
}

$protectedPaths = [
    '/users', '/users/{user}',
    '/materials', '/materials/{material}',
    '/dashboard',
    '/reports/stock', '/reports/production', '/reports/sales', '/reports/cash-reconciliation',
    '/material-intakes', '/crushing-productions', '/dispatches',
    '/palletizing-receipts', '/palletizing-productions',
    '/pellet-sales', '/cash-remittances',
];

$currentPath = null;
$currentMethod = null;
$inResponses = false;
$insertedFor = [];

$out = [];
$skipNextRef = false;

foreach ($lines as $line) {
    $trimmed = rtrim($line);

    // Drop any previously inserted 403 block (line + its $ref line).
    if ($trimmed === "        '403':") {
        $skipNextRef = true;

        continue;
    }

    if ($skipNextRef) {
        $skipNextRef = false;
        if (str_contains($trimmed, '#/components/responses/Forbidden')) {
            continue;
        }
    }

    // Track current path: lines like "  /users:"
    if (preg_match('/^  \/([a-z0-9{}\/-]+):$/', $trimmed, $m)) {
        $currentPath = '/'.trim($m[1], '/');
        $currentMethod = null;
        $inResponses = false;
    }

    // Track current method: "    get:", "    post:", etc.
    if (preg_match('/^    (get|post|patch|put|delete):$/', $trimmed, $m)) {
        $currentMethod = $m[1];
        $inResponses = false;
    }

    // Start of a responses block for this operation.
    if ($trimmed === '      responses:') {
        $inResponses = true;
        $out[] = $line;

        $isProtected = $currentPath !== null
            && in_array($currentPath, $protectedPaths, true)
            && $currentMethod !== null;

        $key = $currentPath.'|'.$currentMethod;

        if ($isProtected && ! isset($insertedFor[$key])) {
            $out[] = "        '403':\n";
            $out[] = "          \$ref: '#/components/responses/Forbidden'\n";
            $insertedFor[$key] = true;
        }

        continue;
    }

    $out[] = $line;
}

file_put_contents($file, implode('', $out));

echo 'Inserted 403 into '.count($insertedFor)." operations.\n";
