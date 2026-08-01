<?php

/**
 * Post-process the generated Postman collection to make it out-of-box usable:
 *  - defines the bearerToken collection variable (the converter references it
 *    for auth but never declares it),
 *  - wires the Login request to auto-store the returned token into
 *    bearerToken so the RN team can test all 32 endpoints immediately.
 *
 * Run: php scripts/postprocess-postman.php
 */

$path = __DIR__.'/../public/docs/plastic-recycling.postman_collection.json';

$collection = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

// 1. Declare the auth token variable if not present.
$hasToken = false;
foreach (($collection['variable'] ?? []) as $variable) {
    if (($variable['key'] ?? null) === 'bearerToken') {
        $hasToken = true;
    }
}

if (! $hasToken) {
    $collection['variable'][] = [
        'key' => 'bearerToken',
        'value' => '',
        'type' => 'string',
        'description' => 'Sanctum bearer token returned by POST /login. Auto-captured after running the Login request.',
    ];
}

// 2. Add a test script to the login request that stores the token.
//    Items are passed by reference so the edit persists back to the collection.
$walk = function (array &$items) use (&$walk): void {
    foreach ($items as &$item) {
        if (isset($item['request'])) {
            $name = strtolower((string) ($item['name'] ?? ''));
            $url = is_array($item['request']['url'] ?? null)
                ? implode('/', $item['request']['url']['path'] ?? [])
                : (string) ($item['request']['url'] ?? '');

            if (str_contains($name, 'log in') || str_contains($url, 'login')) {
                $item['event'] = [
                    [
                        'listen' => 'test',
                        'script' => [
                            'type' => 'text/javascript',
                            'exec' => [
                                'const json = pm.response.json();',
                                'if (pm.response.code === 200 && json.token) {',
                                "    pm.collectionVariables.set('bearerToken', json.token);",
                                "    console.log('Stored bearer token. All authed requests now work.');",
                                '} else {',
                                "    console.log('Login failed - check username/password in the body.');",
                                '}',
                            ],
                        ],
                    ],
                ];
            }
        } elseif (isset($item['item'])) {
            $walk($item['item']);
        }

        unset($item);
    }
};

$walk($collection['item']);

file_put_contents($path, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

echo 'Post-processed collection: '.count($collection['item'])." top-level folders.\n";
