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
		// Create permissions
		$permissions = [
			'super_admin_access',
			'admin_access',
			'worker_access',
			'writer_access',
			'user_access',
			'login_access',
			'password_access',
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

		// Super Admin role
		// Gets all permissions via Gate::before rule
		// in AuthServiceProvider or from Policy before method
		$superadmin = Role::create([
			'name' => 'super_admin',
			'guard_name' => 'admin'
		]);

		// Add permissions to role
		$superadmin->givePermissionTo([
			'super_admin_access',
			'admin_access',
			'login_access',
			'password_access',
		]);

		// Admin role
		$admin = Role::create([
			'name' => 'admin',
			'guard_name' => 'admin'
		]);

		// Add permissions to role
		$admin->givePermissionTo([
			'admin_access',
			'login_access',
			'password_access',
		]);

		// Worker role
		$worker = Role::create([
			'name' => 'worker',
			'guard_name' => 'admin'
		]);

		// Add permissions to role
		$worker->givePermissionTo([
			'worker_access',
			'login_access',
			'password_access',
		]);

		// User role

		// User role (guard web)
		$user = Role::create([
			'name' => 'user',
			'guard_name' => 'web'
		]);

		// Add permissions to role
		$user->givePermissionTo([
			'user_access',
			'login_access',
			'password_access',
		]);

		// Writer role

		// Writer role (guard web)
		$user = Role::create([
			'name' => 'writer',
			'guard_name' => 'web'
		]);

		// Add permissions to role
		$user->givePermissionTo([
			'writer_access',
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
