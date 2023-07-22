<?php

namespace Tests\Dev;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Create user with factory.
	 */
	public function test_create_user_with_factory(): void
	{
		$user = User::factory()->create();

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);
	}
}
