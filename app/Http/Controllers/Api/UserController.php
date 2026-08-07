<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection(User::query()
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $query->where('username', 'like', '%'.$request->string('search').'%');
            })
            ->orderBy('name')
            ->get());
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $user = User::create($request->validated());

        $roleName = $request->input('spatie_role') ?? $request->input('role');
        if ($roleName) {
            $user->syncRoles([$roleName]);
        }

        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        $user->load(['auditLogs' => fn ($query) => $query->latest()->limit(20)]);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $spatieRole = $data['spatie_role'] ?? null;
        unset($data['spatie_role']);

        $user->update($data);

        if ($request->has('spatie_role') || $request->has('role')) {
            $roleName = $spatieRole ?? $user->role;
            if ($roleName) {
                $user->syncRoles([$roleName]);
            }
        }

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
