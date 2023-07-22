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

		$this->actingAs($user);

		$response = $this->getJson('/web/api/logged');
		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);

		$response = $this->withCookies([
			'dummy_token' => 'token12345'
		])->getJson('/web/api/logged');
		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'message', 'user'
		])->assertJsonPath('user.email', $user->email)->assertCookie('dummy_token', 'token5678');
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
