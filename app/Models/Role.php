<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Role extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Role $role): void {
            // Active users must keep a valid role; role deletion is blocked in that case.
            if ($role->users()->exists()) {
                throw ValidationException::withMessages([
                    'role' => ['No se puede eliminar el rol porque tiene usuarios asignados.'],
                ]);
            }

            // If only logically deleted users are linked, detach them to satisfy FK restrictOnDelete.
            User::withTrashed()
                ->where('role_id', $role->id)
                ->whereNotNull('deleted_at')
                ->update(['role_id' => null]);
        });
    }

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_system',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'bool',
            'activo' => 'bool',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}