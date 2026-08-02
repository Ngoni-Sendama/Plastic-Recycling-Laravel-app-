<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return AuditLogResource::collection(
            AuditLog::query()
                ->with('user')
                ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
                ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
                ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
                ->latest()
                ->limit(100)
                ->get()
        );
    }
}
