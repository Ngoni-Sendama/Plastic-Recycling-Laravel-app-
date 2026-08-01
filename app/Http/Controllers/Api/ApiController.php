<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;

abstract class ApiController extends Controller
{
    /**
     * Resolve the material foreign key from a material code or an explicit id.
     *
     * The code wins when both are present because it is the stable business key
     * used by the mobile app, and the sync merge path may carry a stale id.
     *
     * @param  array<string, mixed>  $data
     */
    protected function resolveMaterialId(array $data): int
    {
        if (! empty($data['material_code'])) {
            return (int) Material::where('code', $data['material_code'])->value('id');
        }

        return (int) $data['material_id'];
    }
}
