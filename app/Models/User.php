<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'telefono',
        'activo',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activo' => 'bool',
            'password' => 'hashed',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string|array $roles): bool
    {
        $roleValues = collect((array) $roles)->filter()->values();

        if ($roleValues->isEmpty()) {
            return false;
        }

        $assignedRoles = $this->relationLoaded('roles') ? $this->roles : $this->roles()->get();

        return $assignedRoles->contains(function (Role $role) use ($roleValues): bool {
            return $roleValues->contains($role->code) || $roleValues->contains($role->name);
        });
    }

    public function allPermissions(): Collection
    {
        $directPermissions = $this->relationLoaded('permissions')
            ? $this->permissions
            : $this->permissions()->get();

        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->with('permissions')->get();

        $rolePermissions = $roles
            ->loadMissing('permissions')
            ->flatMap(fn (Role $role) => $role->permissions);

        return $directPermissions
            ->concat($rolePermissions)
            ->unique('id')
            ->sortBy('code')
            ->values();
    }

    public function hasPermission(string|array $permissions): bool
    {
        $permissionValues = collect((array) $permissions)->filter()->values();

        if ($permissionValues->isEmpty()) {
            return false;
        }

        return $this->allPermissions()->contains(function (Permission $permission) use ($permissionValues): bool {
            return $permissionValues->contains($permission->code) || $permissionValues->contains($permission->name);
        });
    }
}
