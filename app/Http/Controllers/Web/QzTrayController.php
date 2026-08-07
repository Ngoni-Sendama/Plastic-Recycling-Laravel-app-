<?php

namespace App\Http\Controllers\Web;

use App\Services\QzTraySigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QzTrayController
{
    public function certificate(QzTraySigner $signer): string
    {
        return $signer->certificate();
    }

    public function sign(Request $request, QzTraySigner $signer): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        return response()->json([
            'signature' => $signer->sign($data['message']),
        ]);
    }
}
