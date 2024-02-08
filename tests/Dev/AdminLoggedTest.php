<?php

namespace Tests\Dev;

use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoggedTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function check_is_user_logged()
	{
		$user = Admin::create([
			'name' => 'Dummy Admin',
			'email' => 'dummy_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$user->assignRole(['super_admin']);

		$this->actingAs($user, 'admin');

		$response = $this->getJson('/web/api/admin/logged');
		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'user' => [
				'roles' => [
					['name' => 'super_admin'],
				],
				'roles_permissions' => [
					[
						'name' => 'super_admin',
						'permissions' => [
							['name' => 'admin_access'],
							['name' => 'login_access']
						],
					],
				]
			]
		])->assertJsonStructure([
			'user' => [
				'is_admin', 'f2a', 'roles', 'roles_permissions',
				'email', 'name', 'username', 'location', 'bio',
			],
		]);
	}

	/** @test */
	function logged_not_authenticated()
	{
		Auth::guard('admin')->logout();

		$response = $this->getJson('/web/api/admin/logged');

		$response->assertStatus(422)->assertJson([
			'message' => 'Unauthenticated.'
		])->assertJsonStructure(['user'])->assertJsonPath('user', null);
	}
}
