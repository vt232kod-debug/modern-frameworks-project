<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * User management (roles are assigned here) — admins only, see routes/api.php.
 */
class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->listResponse($request, User::query(), User::FILTERS);
    }

    public function store(Request $request): JsonResponse
    {
        $user = User::create($this->validatePayload($request, $this->rules()));

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $rules = $this->rules($user);
        // The password may be omitted when updating
        $rules['password'] = ['sometimes', ...$rules['password']];
        $user->update($this->validatePayload($request, $rules));

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(null, 204);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users')->ignore($user)],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(User::ROLES)],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id', Rule::unique('users')->ignore($user)],
        ];
    }
}
