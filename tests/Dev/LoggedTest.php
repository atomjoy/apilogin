<?php

namespace Tests\Dev;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoggedTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function check_is_user_logged()
	{
		$user = User::factory()->create();

		$user->assignRole(['user']);

		$this->actingAs($user);

		$response = $this->getJson('/web/api/logged');
		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'user' => [
				'roles' => [
					['name' => 'user'],
				],
				'roles_permissions' => [
					[
						'name' => 'user',
						'permissions' => [
							['name' => 'user_access'],
							['name' => 'login_access']
						],
					],
				]
			]
		])->assertJsonStructure([
			'user' => [
				'is_admin', 'f2a',
				'profile', 'address', 'roles', 'roles_permissions'
			],
		]);
	}

	/** @test */
	function logged_not_authenticated()
	{
		Auth::logout();

		$response = $this->getJson('/web/api/logged');

		$response->assertStatus(422)->assertJson([
			'message' => 'Unauthenticated.'
		])->assertJsonStructure(['user'])->assertJsonPath('user', null);
	}
}
