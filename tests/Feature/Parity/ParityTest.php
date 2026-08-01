<?php

use App\Services\SyncTableRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Parse task-01 doc: table name => documented columns.
 *
 * @return array<string, array<int, string>>
 */
function parityDocumentedTables(): array
{
    $md = file_get_contents(base_path('public/docs/tasks/01-database-tables-and-relationships.md'));

    $tables = [];

    if (preg_match_all('/^### (\w+)\s*$/m', $md, $heads, PREG_OFFSET_CAPTURE)) {
        foreach ($heads[1] as $i => [$name]) {
            $start = $heads[0][$i][1];
            $end = isset($heads[0][$i + 1][1]) ? $heads[0][$i + 1][1] : strlen($md);
            $section = substr($md, $start, $end - $start);

            if (preg_match('/Main columns:\s*\n((?:- .*\n)*)/', $section, $m)) {
                preg_match_all('/^- ([a-z_][a-z0-9_]*)/m', $m[1], $cols);

                $tables[$name] = array_values(array_unique($cols[1]));
            }
        }
    }

    return $tables;
}

/**
 * Parse task-03 doc: [method, api path] pairs from the suggested routes section.
 *
 * @return array<int, array{0: string, 1: string}>
 */
function parityDocumentedEndpoints(): array
{
    $md = file_get_contents(base_path('public/docs/tasks/03-laravel-api-mobile-sync.md'));

    $start = strpos($md, '## Suggested Laravel API Routes');
    $end = strpos($md, '## Mobile App Changes Needed', $start);

    $section = substr($md, $start, $end - $start);

    preg_match_all('/`(GET|POST|PATCH|DELETE) \/api\/([a-z0-9{}.\/-]+)`/m', $section, $matches, PREG_SET_ORDER);

    return array_map(fn ($m) => [$m[1], $m[2]], $matches);
}

/**
 * Parse task-05 doc: syncable table names from the push/pull payload shapes.
 *
 * @return array<int, string>
 */
function parityDocumentedSyncTables(): array
{
    $md = file_get_contents(base_path('public/docs/tasks/05-offline-online-sync.md'));

    $tables = [];

    // Only keys inside the "changes" / "deleted" payload blocks count as
    // syncable tables — the push response also has "accepted" / "rejected".
    if (preg_match_all('/"(?:changes|deleted)": \{([^}]*)\}/', $md, $blocks)) {
        foreach ($blocks[1] as $block) {
            preg_match_all('/"([a-z_]+)": \[\]/', $block, $matches);
            $tables = [...$tables, ...$matches[1]];
        }
    }

    return array_values(array_unique($tables));
}

/**
 * Parse the mobile modules.js into endpoint + toApi/fromApi key lists.
 *
 * @return array<string, array{endpoint: string|null, toApi: array<int, string>, fromApi: array<int, string>}>
 */
function parityMobileModules(): array
{
    $js = file_get_contents(base_path('Plastic-Recycling-Business-App/mobile/src/modules.js'));

    $modules = [];

    if (preg_match_all('/^  (\w+): \{/m', $js, $keys, PREG_OFFSET_CAPTURE)) {
        foreach ($keys[1] as $i => [$key]) {
            $start = $keys[0][$i][1];
            $end = isset($keys[0][$i + 1][1])
                ? $keys[0][$i + 1][1]
                : strpos($js, "\n};", $start);
            $block = substr($js, $start, $end - $start);

            $module = ['endpoint' => null, 'toApi' => [], 'fromApi' => []];

            if (preg_match('/endpoint: "([^"]+)"/', $block, $e)) {
                $module['endpoint'] = $e[1];
            }

            if (preg_match('/toApi:\s*\(v\)\s*=>\s*\(\{([\s\S]*?)\n\s*\}\)/', $block, $t)) {
                preg_match_all('/([a-z_]+):\s*v\./', $t[1], $toKeys);
                $module['toApi'] = array_values(array_unique($toKeys[1]));
            }

            if (preg_match('/fromApi:\s*\(r\)\s*=>\s*\(\{([\s\S]*?)\n\s*\}\)/', $block, $f)) {
                preg_match_all('/\br\.([a-z_]+)/', $f[1], $fromKeys);
                $module['fromApi'] = array_values(array_unique($fromKeys[1]));
            }

            $modules[$key] = $module;
        }
    }

    return $modules;
}

function parityRouteRegistered(string $method, string $path): bool
{
    $uri = 'api/'.$path;

    return collect(Route::getRoutes()->getRoutes())->contains(
        fn ($route) => in_array($method, $route->methods(), true) && $route->uri() === $uri
    );
}

test('documented tables and columns (task 01) exist in the database schema', function () {
    $tables = parityDocumentedTables();

    $this->assertNotEmpty($tables, 'task-01 doc has no documented tables');

    foreach ($tables as $table => $columns) {
        $this->assertTrue(Schema::hasTable($table), "table [{$table}] documented in task 01 is missing");

        $actual = Schema::getColumnListing($table);

        foreach ($columns as $column) {
            if ($column === 'timestamps') {
                continue;
            }

            $this->assertTrue(
                in_array($column, $actual, true),
                "column [{$table}.{$column}] documented in task 01 is missing from the schema"
            );
        }
    }
});

test('documented API endpoints (task 03) are registered', function () {
    $endpoints = parityDocumentedEndpoints();

    $this->assertNotEmpty($endpoints, 'task-03 doc has no documented endpoints');

    foreach ($endpoints as [$method, $path]) {
        $this->assertTrue(
            parityRouteRegistered($method, $path),
            "{$method} /api/{$path} documented in task 03 is not registered"
        );
    }
});

test('documented sync tables (task 05) exist in the sync registry', function () {
    $tables = SyncTableRegistry::tables();

    foreach (parityDocumentedSyncTables() as $table) {
        $this->assertArrayHasKey($table, $tables, "sync table [{$table}] documented in task 05 is not in SyncTableRegistry");
    }
});

test('mobile module endpoints (modules.js) are registered and match sync table names', function () {
    $modules = parityMobileModules();

    if ($modules === []) {
        $this->markTestSkipped('Mobile modules.js is not present in this checkout.');

        return;
    }

    foreach ($modules as $key => $module) {
        $this->assertNotNull($module['endpoint'], "module [{$key}] has no endpoint");

        $path = ltrim((string) $module['endpoint'], '/');

        $this->assertTrue(parityRouteRegistered('GET', $path), "GET /api/{$path} for module [{$key}] is not registered");
        $this->assertTrue(parityRouteRegistered('POST', $path), "POST /api/{$path} for module [{$key}] is not registered");

        $table = str_replace('-', '_', $path);

        $this->assertArrayHasKey($table, SyncTableRegistry::tables(), "module [{$key}] endpoint maps to unknown sync table [{$table}]");
    }
});

test('mobile toApi payloads (modules.js) are accepted by the API validation rules', function () {
    $modules = parityMobileModules();

    if ($modules === []) {
        $this->markTestSkipped('Mobile modules.js is not present in this checkout.');

        return;
    }

    foreach ($modules as $key => $module) {
        $table = str_replace('-', '_', ltrim((string) $module['endpoint'], '/'));
        $rules = array_keys(SyncTableRegistry::rulesFor($table));

        foreach ($module['toApi'] as $field) {
            $this->assertTrue(
                in_array($field, $rules, true),
                "toApi field [{$field}] of module [{$key}] is not in the {$table} validation rules"
            );
        }
    }
});

test('mobile fromApi reads (modules.js) exist in the API resource responses', function () {
    $modules = parityMobileModules();

    if ($modules === []) {
        $this->markTestSkipped('Mobile modules.js is not present in this checkout.');

        return;
    }

    foreach ($modules as $key => $module) {
        $table = str_replace('-', '_', ltrim((string) $module['endpoint'], '/'));

        $this->assertArrayHasKey($table, SyncTableRegistry::tables(), "module [{$key}] maps to unknown sync table [{$table}]");

        [$modelClass, $resourceClass] = SyncTableRegistry::tables()[$table];

        $model = $modelClass::factory()->create();

        if (in_array($table, SyncTableRegistry::materialTables(), true)) {
            $model->load('material');
        }

        $model->load('recordedByUser');

        $keys = array_keys((new $resourceClass($model))->resolve());

        foreach ($module['fromApi'] as $field) {
            $this->assertTrue(
                in_array($field, $keys, true),
                "fromApi field [{$field}] of module [{$key}] is missing from the {$resourceClass} response"
            );
        }
    }
});
