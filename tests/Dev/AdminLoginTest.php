<?php

namespace Tests\Dev;

use App\Models\User;
use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Register user.
	 */
	public function test_user_login(): void
	{
		Auth::shouldUse('admin');

		Auth::logout();

		$user = Admin::create([
			'name' => 'Dummy Admin',
			'email' => 'dummy_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$user->assignRole(['super_admin']);

		$name = $user->name;
		$email = $user->email;
		$password = 'Password123#';

		$this->assertDatabaseHas('admins', [
			'name' => $name,
			'email' => $email,
		]);

		$response = $this->postJson('web/api/admin/login', [
			'email' => $email,
			'password' => $password,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'redirect' => null,
			'user' => [
				'roles' => [
					['name' => 'super_admin'],
				],
				'roles_permissions' => [
					[
						'name' => 'super_admin',
						'permissions' => [
							['name' => 'super_admin_access'],
							['name' => 'admin_access'],
							['name' => 'login_access'],
						],
					],
				]
			]
		])->assertJsonStructure([
			'user' => [
				'is_admin', 'f2a', 'roles', 'roles_permissions',
				'email', 'name', 'username', 'location', 'bio',
			],
		])->assertJsonPath('user.email', $user->email);

		$this->assertNotNull($response['user']);
	}

	/**
	 * Super admin login.
	 */
	public function test_admin_login(): void
	{
		Auth::guard('admin')->logout();

		$user = Admin::create([
			'name' => 'Admin',
			'username' => 'super_admin',
			'email' => 'super_admin@example.com',
			'password' => Hash::make('Password123#'),
		]);

		$user->is_admin = 1;
		$user->save();

		$user->assignRole('super_admin');

		$this->actingAs($user, 'admin');

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);
	}

	/**
	 * Worer login.
	 */
	public function test_worker_login(): void
	{
		Auth::guard('admin')->logout();

		$user = Admin::create([
			'name' => 'Worker',
			'username' => 'worker_admin',
			'email' => 'worker_admin@example.com',
			'password' => Hash::make('Password123#'),
		]);

		$user->is_admin = 1;
		$user->save();

		$user->assignRole('worker');

		$this->actingAs($user, 'admin');

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);
	}

	/**
	 * Admin ban login error.
	 */
	public function test_login_banned_admin_panel(): void
	{
		Auth::guard('admin')->logout();

		$user = Admin::create([
			'name' => 'Author',
			'username' => 'author_admin',
			'email' => 'author_admin@example.com',
			'password' => Hash::make('Password123#'),
		]);

		$user->is_admin = 0; // ban admin
		$user->save();

		$user->assignRole('worker');

		$this->actingAs($user, 'admin');

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(403)->assertJson([
			'message' => 'User does not have the right roles (is_admin).'
		]);
	}

	/**
	 * User login error.
	 */
	public function test_login_user_admin_panel(): void
	{
		Auth::logout();

		$user = User::create([
			'name' => 'User_test',
			'username' => 'user_test',
			'email' => 'user_test@example.com',
			'password' => Hash::make('Password123#'),
		]);

		$user->is_admin = 1;
		$user->save();

		$this->actingAs($user);

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);
	}
}
