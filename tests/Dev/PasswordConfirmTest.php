<?php

namespace Tests\Dev;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordConfirmTest extends TestCase
{
	/** @test */
	function confirm_logged_user_password()
	{
		$user = User::factory()->create([
			'password' => Hash::make('Password123#')
		]);

		$this->actingAs($user);

		$res = $this->postJson('/web/api/password/confirm', [
			'password' => 'Password123##'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'Invalid current password.'
		]);

		$res = $this->postJson('/web/api/password/confirm', [
			'password' => 'Password123#'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Confirmed.'
		]);

		Auth::logout();

		$res = $this->postJson('/web/api/password/confirm', [
			'password' => 'Password123#'
		]);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);
	}
}
