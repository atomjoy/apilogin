<?php

namespace Tests\Dev;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ActivateTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function invalid_activation_user_id()
	{
		$token = uniqid();

		$user = User::factory()->create([
			'email_verified_at' => null,
			'remember_token' => $token,
		]);

		$this->assertDatabaseHas('users', [
			'email' => $user->email,
			'remember_token' => $token,
		]);

		// min:1
		$res = $this->getJson('/web/api/activate/0/' . $token);

		// Only numbers
		$res->assertStatus(422)->assertJson([
			'message' => 'The id field must be at least 1.'
		]);

		// Invalid number id
		$res = $this->getJson('/web/api/activate/error123/' . $token);

		// Only numbers
		$res->assertStatus(422)->assertJson([
			'message' => 'The id field must be a number.'
		]);

		// Invalid user id
		$res = $this->getJson('/web/api/activate/123/' . $token);

		$res->assertStatus(422)->assertJson([
			'message' => 'Email has not been confirmed.'
		]);
	}

	/** @test */
	function invalid_activation_user_code()
	{
		$token = uniqid();

		$user = User::factory()->create([
			'email_verified_at' => null,
			'remember_token' => $token,
		]);

		$this->assertModelExists($user);

		$this->assertDatabaseHas('users', [
			'email' => $user->email,
			'remember_token' => $token,
		]);

		// min:6
		$res = $this->getJson('/web/api/activate/' . $user->id . '/er123');

		$res->assertStatus(422)->assertJson([
			'message' => 'The code field must be at least 6 characters.'
		]);

		// max:30
		$res = $this->getJson('/web/api/activate/' . $user->id . '/' . md5('tolongcode'));

		$res->assertStatus(422)->assertJson([
			'message' => 'The code field must not be greater than 30 characters.'
		]);

		// Code valid but not exists
		$res = $this->getJson('/web/api/activate/' . $user->id . '/errorcode123');

		$res->assertStatus(422)->assertJson([
			'message' => 'Invalid activation code.'
		]);
	}

	/** @test */
	function activate_user()
	{
		$token = uniqid();

		$user = User::factory()->create([
			'email_verified_at' => null,
			'remember_token' => $token,
		]);

		$this->assertModelExists($user);

		$this->assertDatabaseHas('users', [
			'email' => $user->email,
			'remember_token' => $token,
		]);

		// Activated
		$res = $this->getJson('/web/api/activate/' . $user->id . '/' . $token);

		$res->assertStatus(200)->assertJson([
			'message' => 'Email has been confirmed.'
		]);

		// Exists
		$res = $this->getJson('/web/api/activate/' . $user->id . '/' . $token);

		$res->assertStatus(200)->assertJson([
			'message' => 'The email address has already been confirmed.'
		]);

		// Is Activated
		$db_user = User::where('email', $user->email)->first();

		$this->assertNotNull($db_user->email_verified_at);
	}
}
