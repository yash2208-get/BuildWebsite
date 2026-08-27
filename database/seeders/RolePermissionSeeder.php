<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::catalogue() as $group => $items) {
            foreach ($items as $name => $label) {
                Permission::updateOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['group' => $group, 'label' => $label],
                );
            }
        }

        foreach (RoleType::cases() as $type) {
            $role = Role::updateOrCreate(
                ['name' => $type->value, 'guard_name' => 'web'],
                [
                    'label' => $type->label(),
                    'color' => $type->color(),
                    'is_system' => true,
                    'description' => match ($type) {
                        RoleType::SuperAdmin => 'Unrestricted access to every area of the platform.',
                        RoleType::Admin => 'Manages platform content, users and support — but not system settings.',
                        RoleType::User => 'Builds and manages their own websites.',
                    },
                ],
            );

            $role->syncPermissions(Permissions::forRole($type));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
