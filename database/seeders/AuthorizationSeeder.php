<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Dashboard', 'code' => 'dashboard', 'module' => 'dashboard'],
            ['name' => 'Usuarios', 'code' => 'users', 'module' => 'admin'],
            ['name' => 'Roles', 'code' => 'roles', 'module' => 'admin'],
            ['name' => 'Categorias', 'code' => 'categorias', 'module' => 'catalogos'],
            ['name' => 'Productos', 'code' => 'productos', 'module' => 'catalogos'],
            ['name' => 'Proveedores', 'code' => 'proveedores', 'module' => 'catalogos'],
            ['name' => 'Compras', 'code' => 'compras', 'module' => 'compras'],
            ['name' => 'Inventario', 'code' => 'inventario', 'module' => 'inventario'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::query()->updateOrCreate(
                ['code' => $permissionData['code']],
                [
                    ...$permissionData,
                    'activo' => true,
                ]
            );
        }

        $roles = [
            'admin' => [
                'name' => 'Administrador',
                'description' => 'Acceso total al sistema.',
                'permissions' => Permission::query()->pluck('code')->all(),
            ],
            'operador' => [
                'name' => 'Operador',
                'description' => 'Operacion general con acceso amplio.',
                'permissions' => [
                    'dashboard',
                    'categorias',
                    'productos',
                    'proveedores',
                    'compras',
                    'inventario',
                ],
            ],
            'almacenero' => [
                'name' => 'Almacenero',
                'description' => 'Gestion de stock y compras.',
                'permissions' => [
                    'dashboard',
                    'categorias',
                    'productos',
                    'proveedores',
                    'compras',
                    'inventario',
                ],
            ],
            'cajero' => [
                'name' => 'Cajero',
                'description' => 'Operacion de ventas y caja.',
                'permissions' => [
                    'dashboard',
                ],
            ],
        ];

        foreach ($roles as $code => $roleData) {
            $role = Role::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => true,
                    'activo' => true,
                ]
            );

            $permissionIds = Permission::query()
                ->whereIn('code', $roleData['permissions'])
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }

        $adminRole = Role::query()->where('code', 'admin')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@admin.local'],
            [
                'username' => 'admin',
                'name' => 'Administrador General',
                'telefono' => null,
                'activo' => true,
                'role_id' => $adminRole->id,
                'password' => Hash::make('password'),
            ]
        );
    }
}