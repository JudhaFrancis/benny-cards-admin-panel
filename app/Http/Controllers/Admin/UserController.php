<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get all roles for dropdowns.
     */
    public function roles()
    {
        $roles = Role::all(['id', 'name']);
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        // Check if user is super admin - utilizing the loaded role relationship or checking directly
        $isSuperAdmin = $currentUser->role && strtolower($currentUser->role->name) === 'super-admin';

        $isManagement = $currentUser->role && in_array(strtolower($currentUser->role->name), ['super-admin', 'admin', 'staff']);

        $users = User::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($request->email, function ($query, $email) {
                $query->where('email', 'like', "%{$email}%");
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->{'role.name'} || $request->role_id, function ($query) use ($request) {
                $roleName = $request->{'role.name'};
                if ($roleName) {
                    $query->whereHas('role', function ($q) use ($roleName) {
                        $q->where('name', 'like', "%{$roleName}%");
                    });
                }
                if ($request->role_id) {
                    $query->where('role_id', $request->role_id);
                }
            })
            ->when($request->created_at, function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->when(!$isSuperAdmin && !$isManagement, function ($query) use ($currentUser) {
                $query->where('id', $currentUser->id);
            })
            ->with('role')
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'branch_ids' => ['nullable', 'array', function ($attr, $value, $fail) use ($request) {
                if (!$this->isCustomerRole($request->input('role_id')) && empty($value)) {
                    $fail('Select at least one branch.');
                }
            }],
            'branch_ids.*' => 'integer|exists:branches,id',
        ]);
        if (!$this->isCustomerRole($data['role_id']) && empty($data['branch_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Select at least one branch.',
                'errors' => ['branch_ids' => ['Select at least one branch.']],
            ], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'branch_ids' => $this->branchIdsFor($data),
            'status' => $data['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Clean branch id list; the customer-facing User role never has branches.
     */
    protected function isCustomerRole($roleId): bool
    {
        $role = \App\Models\Role::find($roleId);

        return $role && strtolower($role->name) === 'user';
    }

    protected function branchIdsFor(array $data): array
    {
        $role = \App\Models\Role::find($data['role_id']);
        if ($role && strtolower($role->name) === 'user') {
            return [];
        }

        return collect($data['branch_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load('role')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'integer|exists:branches,id',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];
        $user->status = $data['status'];
        // Branch access is stored on the user; it only changes when branch_ids is sent
        if ($request->has('branch_ids')) {
            $user->branch_ids = $this->branchIdsFor($data);
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
