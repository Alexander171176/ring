<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role_admin = Role::create(['name' => 'admin']);
        $role_editor = Role::create(['name' => 'editor']);
        $role_user = Role::create(['name' => 'user']);

        $permission_show_users = Permission::create(['name' => 'show-users']);
        $permission_create_users = Permission::create(['name' => 'create-users']);
        $permission_edit_users = Permission::create(['name' => 'edit-users']);
        $permission_delete_users = Permission::create(['name' => 'delete-users']);
        $permission_show_roles = Permission::create(['name' => 'show-roles']);
        $permission_create_roles = Permission::create(['name' => 'create-roles']);
        $permission_edit_roles = Permission::create(['name' => 'edit-roles']);
        $permission_delete_roles = Permission::create(['name' => 'delete-roles']);
        $permission_show_permissions = Permission::create(['name' => 'show-permissions']);
        $permission_create_permissions = Permission::create(['name' => 'create-permissions']);
        $permission_edit_permissions = Permission::create(['name' => 'edit-permissions']);
        $permission_delete_permissions = Permission::create(['name' => 'delete-permissions']);
        $permission_show_rubrics = Permission::create(['name' => 'show-rubrics']);
        $permission_create_rubrics = Permission::create(['name' => 'create-rubrics']);
        $permission_edit_rubrics = Permission::create(['name' => 'edit-rubrics']);
        $permission_delete_rubrics = Permission::create(['name' => 'delete-rubrics']);
        $permission_show_sections = Permission::create(['name' => 'show-sections']);
        $permission_create_sections = Permission::create(['name' => 'create-sections']);
        $permission_edit_sections = Permission::create(['name' => 'edit-sections']);
        $permission_delete_sections = Permission::create(['name' => 'delete-sections']);
        $permission_show_articles = Permission::create(['name' => 'show-articles']);
        $permission_create_articles = Permission::create(['name' => 'create-articles']);
        $permission_edit_articles = Permission::create(['name' => 'edit-articles']);
        $permission_delete_articles = Permission::create(['name' => 'delete-articles']);

        $permissions_admin = [
            $permission_show_users,
            $permission_create_users,
            $permission_edit_users,
            $permission_delete_users,
            $permission_show_roles,
            $permission_create_roles,
            $permission_edit_roles,
            $permission_delete_roles,
            $permission_show_permissions,
            $permission_create_permissions,
            $permission_edit_permissions,
            $permission_delete_permissions,
            $permission_show_rubrics,
            $permission_create_rubrics,
            $permission_edit_rubrics,
            $permission_delete_rubrics,
            $permission_show_sections,
            $permission_create_sections,
            $permission_edit_sections,
            $permission_delete_sections,
            $permission_show_articles,
            $permission_create_articles,
            $permission_edit_articles,
            $permission_delete_articles
        ];

        $permissions_editor = [
            $permission_show_users,
            $permission_show_rubrics,
            $permission_create_rubrics,
            $permission_edit_rubrics,
            $permission_delete_rubrics,
            $permission_show_sections,
            $permission_create_sections,
            $permission_edit_sections,
            $permission_delete_sections,
            $permission_show_articles,
            $permission_create_articles,
            $permission_edit_articles,
            $permission_delete_articles
        ];

        $permissions_user = [
            $permission_show_articles,
            $permission_create_articles,
            $permission_edit_articles,
        ];

        $role_admin->syncPermissions($permissions_admin);
        $role_editor->syncPermissions($permissions_editor);
        $role_user->syncPermissions($permissions_user);
    }
}
