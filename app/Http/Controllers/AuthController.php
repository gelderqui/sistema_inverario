<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthenticatedUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = [
            'username' => $validated['username'],
            'password' => $validated['password'],
            'activo' => true,
        ];

        if (! Auth::attempt($credentials, (bool) ($validated['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'username' => __('The provided credentials are incorrect or the user is inactive.'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user()->load(['roles.permissions', 'permissions']);

        return response()->json([
            'message' => 'Authenticated successfully.',
            'user' => new AuthenticatedUserResource($user),
        ]);
    }

    public function show(Request $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(
            $request->user()->load(['roles.permissions', 'permissions'])
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
