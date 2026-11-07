<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, \Spatie\Permission\Traits\HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'branch_ids',
        'status',
    ];

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'branch_ids' => 'array',
    ];

    /** Attach branch access to the logged-in user's JSON (login / me): branches + all_branches. */
    public function withBranchAccess(): static
    {
        $this->setAttribute('branches', $this->assignedBranches());
        $this->setAttribute('all_branches', \App\Support\BranchScope::hasAllBranches($this));

        return $this;
    }

    /** Branches this user may see ([{id, name}]), from users.branch_ids. */
    public function assignedBranches()
    {
        return Branch::query()->whereIn('id', $this->branch_ids ?? [])->ordered()->get(['id', 'name', 'is_active']);
    }
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['permission_names'];

    /**
     * Get all permissions of the user as formatted strings (module-action).
     */
    public function getPermissionNamesAttribute()
    {
        // If we have a role and it has permissions loaded, use them
        if ($this->role && $this->role->relationLoaded('permissions')) {
            return $this->role->permissions->map(function ($p) {
                return strtolower($p->module?->name ?? 'unknown') . '-' . strtolower($p->action?->name ?? 'unknown');
            });
        }

        // Fallback to Spatie's standard methods
        return $this->getAllPermissions()->load(['module', 'action'])->map(function ($p) {
            return strtolower($p->module?->name ?? 'unknown') . '-' . strtolower($p->action?->name ?? 'unknown');
        });
    }

    public function getGuardNames(): \Illuminate\Support\Collection
    {
        return collect(['web', 'sanctum']);
    }
}
