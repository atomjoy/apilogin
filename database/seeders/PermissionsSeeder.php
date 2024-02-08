<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Create all permissions
		$permissions = [
			'admin_access',
			'worker_access',
			'login_access',
			'user_management_access',
			'permission_create',
			'permission_edit',
			'permission_show',
			'permission_delete',
			'permission_access',
			'role_create',
			'role_edit',
			'role_show',
			'role_delete',
			'role_access',
			'user_create',
			'user_edit',
			'user_show',
			'user_delete',
			'user_access',
			'article_create',
			'article_edit',
			'article_show',
			'article_delete',
			'article_access',
			'article_category_create',
			'article_category_edit',
			'article_category_show',
			'article_category_delete',
			'article_category_access',
			'article_comment_create',
			'article_comment_edit',
			'article_comment_show',
			'article_comment_delete',
			'article_comment_access',
			'article_tag_create',
			'article_tag_edit',
			'article_tag_show',
			'article_tag_delete',
			'article_tag_access',
			'profile_create',
			'profile_edit',
			'profile_show',
			'profile_delete',
			'profile_access',
		];

		foreach ($permissions as $permission) {
			Permission::create([
				'name' => $permission,
				'guard_name' => 'web',
			]);

			Permission::create([
				'name' => $permission,
				'guard_name' => 'admin',
			]);
		}

		// Admin role
		// Gets all permissions via Gate::before rule
		// in AuthServiceProvider or from Policy before method
		$admin = Role::create([
			'name' => 'super_admin', 'guard_name' => 'admin'
		]);

		// Add permissions to role
		$admin->givePermissionTo([
			'admin_access', 'login_access',
		]);

		// Admin worker role
		$worker = Role::create([
			'name' => 'worker', 'guard_name' => 'admin'
		]);

		// Add permissions to role
		$worker->givePermissionTo([
			'worker_access', 'login_access',
		]);

		// User role

		// User role (guard web)
		$user = Role::create([
			'name' => 'user', 'guard_name' => 'web'
		]);

		// Add permissions to role
		$user->givePermissionTo([
			'login_access',
			'profile_create',
			'profile_edit',
			'profile_show',
			'profile_delete',
			'profile_access',
			'article_create',
			'article_edit',
			'article_show',
			'article_delete',
			'article_access',
			'article_category_create',
			'article_category_edit',
			'article_category_show',
			'article_category_delete',
			'article_category_access',
			'article_comment_create',
			'article_comment_edit',
			'article_comment_show',
			'article_comment_delete',
			'article_comment_access',
			'article_tag_create',
			'article_tag_edit',
			'article_tag_show',
			'article_tag_delete',
			'article_tag_access',
		]);
	}

	function add_user_role_permissions()
	{
		$permissions = [
			'menu_access',
			'menu_create',
			'menu_show',
			'menu_edit',
			'menu_delete'
		];

		// Web guard

		$user = Role::findByName('user');

		foreach ($permissions as $permission) {
			Permission::create([
				'name' => $permission,
				'guard_name' => 'web',
			]);

			$user->givePermissionTo($permission);
		}

		// Admin guard

		$worker = Role::findByName('worker');

		foreach ($permissions as $permission) {
			Permission::create([
				'name' => $permission,
				'guard_name' => 'admin',
			]);

			$worker->givePermissionTo($permission);
		}

		// Admin guard

		$admin = Role::findByName('admin');

		foreach ($permissions as $permission) {
			Permission::create([
				'name' => $permission,
				'guard_name' => 'admin',
			]);

			$admin->givePermissionTo($permission);
		}
	}
}
