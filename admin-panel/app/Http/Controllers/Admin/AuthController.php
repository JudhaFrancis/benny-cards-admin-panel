<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle the incoming login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['status'] = 'active';
        // Remove hardcoded admin check to allow staff/moderators
        // $credentials['role'] = 'admin';

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Prevent standard users from logging into admin panel
            if ($user->role->name === 'User') {
                Auth::guard('web')->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Standard users cannot access the admin portal.',
                ], 403);
            }

            $token = $user->createToken('admin-token')->plainTextToken;

            $user->load(['role.permissions.module', 'role.permissions.action']);
            $permissions = $user->permission_names;
            if ($user->role) {
                $user->role->makeHidden('permissions');
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'permissions' => $permissions,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['role.permissions.module', 'role.permissions.action']);
        $permissions = $user->permission_names;
        if ($user->role) {
            $user->role->makeHidden('permissions');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'permissions' => $permissions,
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'string'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        // Handle photo upload if present
        if (!empty($data['photo']) && str_starts_with($data['photo'], 'data:image')) {
            // Delete old photo if it exists and is a file
            if ($user->photo && File::exists(public_path($user->photo))) {
                File::delete(public_path($user->photo));
            }

            // Process Base64
            $image_service_str = $data['photo'];
            $extension = explode('/', explode(':', substr($image_service_str, 0, strpos($image_service_str, ';')))[1])[1];
            $replace = substr($image_service_str, 0, strpos($image_service_str, ',') + 1);
            $image = str_replace($replace, '', $image_service_str);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(20) . '.' . $extension;

            $path = 'uploads/profiles/' . $imageName;
            File::put(public_path($path), base64_decode($image));

            $user->photo = $path;
        }

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user,
            ]
        ]);
    }
}
