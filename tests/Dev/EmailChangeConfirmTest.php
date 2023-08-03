<?php

namespace Tests\Dev;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class EmailChangeConfirmTest extends TestCase
{
	use RefreshDatabase;

	public function test_email_change_confirm_not_logged(): void
	{
		$user = User::factory()->create([
			'name' => 'Alex',
			'email' => uniqid() . '@laravel.com'
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		$response = $this->getJson('web/api/change/email/1/code123', [
			'email' => $email,
		]);

		$response->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.',
		]);
	}

	/** @test */
	function change_user_email_confirm()
	{
		$code = uniqid();

		$user = User::factory()->create([
			'name' => 'Alex',
			'email' => uniqid() . '@gmail.com'
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		$this->actingAs($user);

		Cache::put(
			$this->getCacheKey($user),
			$user->id . '|' . $email . '|' . $code,
			now()->addHour()
		);

		$response = $this->getJson('web/api/change/email/' . $user->id . '/' . $code);

		$response->assertStatus(200)->assertJson([
			'message' => 'Email address has been updated. Refresh panel page.',
		]);
	}

	function getCacheKey($user)
	{
		return 'emailchange_' . md5($user->id);
	}
}
