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
            ['name' => 'View dashboard', 'code' => 'dashboard.view', 'module' => 'dashboard'],
            ['name' => 'Manage users', 'code' => 'users.manage', 'module' => 'admin'],
            ['name' => 'Manage roles', 'code' => 'roles.manage', 'module' => 'admin'],
            ['name' => 'View inventory', 'code' => 'inventory.view', 'module' => 'inventory'],
            ['name' => 'Manage inventory', 'code' => 'inventory.manage', 'module' => 'inventory'],
            ['name' => 'Adjust inventory', 'code' => 'inventory.adjust', 'module' => 'inventory'],
            ['name' => 'View purchases', 'code' => 'purchases.view', 'module' => 'purchases'],
            ['name' => 'Create purchases', 'code' => 'purchases.create', 'module' => 'purchases'],
            ['name' => 'Receive purchases', 'code' => 'purchases.receive', 'module' => 'purchases'],
            ['name' => 'Access POS', 'code' => 'pos.access', 'module' => 'sales'],
            ['name' => 'Create sales', 'code' => 'sales.create', 'module' => 'sales'],
            ['name' => 'Void sales', 'code' => 'sales.void', 'module' => 'sales'],
            ['name' => 'Open cash', 'code' => 'cash.open', 'module' => 'cash'],
            ['name' => 'Close cash', 'code' => 'cash.close', 'module' => 'cash'],
            ['name' => 'Perform cash count', 'code' => 'cash.count', 'module' => 'cash'],
            ['name' => 'View reports', 'code' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Export reports', 'code' => 'reports.export', 'module' => 'reports'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::query()->updateOrCreate(
                ['code' => $permissionData['code']],
                $permissionData
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
                    'dashboard.view',
                    'inventory.view',
                    'inventory.manage',
                    'purchases.view',
                    'purchases.create',
                    'purchases.receive',
                    'pos.access',
                    'sales.create',
                    'cash.open',
                    'cash.close',
                    'cash.count',
                    'reports.view',
                ],
            ],
            'almacenero' => [
                'name' => 'Almacenero',
                'description' => 'Gestion de stock y compras.',
                'permissions' => [
                    'dashboard.view',
                    'inventory.view',
                    'inventory.manage',
                    'inventory.adjust',
                    'purchases.view',
                    'purchases.create',
                    'purchases.receive',
                ],
            ],
            'cajero' => [
                'name' => 'Cajero',
                'description' => 'Operacion de ventas y caja.',
                'permissions' => [
                    'dashboard.view',
                    'pos.access',
                    'sales.create',
                    'cash.open',
                    'cash.close',
                    'cash.count',
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
                ]
            );

            $permissionIds = Permission::query()
                ->whereIn('code', $roleData['permissions'])
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }

        $adminUser = User::query()->updateOrCreate(
            ['email' => 'admin@pos.local'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
            ]
        );

        $adminRole = Role::query()->where('code', 'admin')->firstOrFail();
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}