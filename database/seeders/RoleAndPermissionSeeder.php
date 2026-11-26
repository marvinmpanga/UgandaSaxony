<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'pages.view',
            'pages.create',
            'pages.edit',
            'pages.publish',
            'pages.delete',
            'media.view',
            'media.upload',
            'media.edit',
            'media.delete',
            'users.view',
            'users.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $rolePermissions = [
            'Super Admin' => $permissions,
            'Website Administrator' => [
                'dashboard.view',
                'pages.view',
                'pages.create',
                'pages.edit',
                'pages.publish',
                'pages.delete',
                'media.view',
                'media.upload',
                'media.edit',
                'media.delete',
                'users.view',
                'settings.manage',
            ],
            'Content Editor' => [
                'dashboard.view',
                'pages.view',
                'pages.create',
                'pages.edit',
                'media.view',
                'media.upload',
                'media.edit',
            ],
            'Content Publisher' => [
                'dashboard.view',
                'pages.view',
                'pages.edit',
                'pages.publish',
                'media.view',
            ],
            'Media Manager' => [
                'dashboard.view',
                'media.view',
                'media.upload',
                'media.edit',
                'media.delete',
            ],
            'Viewer' => [
                'dashboard.view',
                'pages.view',
                'media.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($assignedPermissions);
        }

        if (app()->isProduction() && blank(env('ADMIN_SEED_PASSWORD'))) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_SEED_EMAIL', 'admin@ugandasaxony.org')],
            [
                'name' => 'Partnership Administrator',
                'password' => Hash::make((string) env('ADMIN_SEED_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['Super Admin']);
    }
}
