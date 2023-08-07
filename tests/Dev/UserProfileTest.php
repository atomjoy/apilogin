<?php

namespace Tests\Dev;

use App\Models\User;
use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function create_model()
	{
		$user = User::factory()->create();

		$this->actingAs($user);

		// Create
		$response = $this->patchJson(
			'/web/api/profile',
			Profile::factory()->make([
				'name' => "Czereśnia O'Reilly",
				'username' => 'user123456',
			])->toArray()
		);

		$response->assertStatus(200)->assertJson([
			'message' => 'Profile has been updated.',
			'profile' => [
				'name' => "Czereśnia O'Reilly",
				'username' => 'user123456',
			]
		]);

		// Update
		$response = $this->patchJson(
			'/web/api/profile',
			Profile::factory()->make([
				'name' => "Czereśnia O'Reilly",
				'username' => 'user.123456',
				'location' => 'Polska',
			])->toArray()
		);

		$response->assertStatus(200)->assertJson([
			'message' => 'Profile has been updated.',
			'profile' => [
				'name' => "Czereśnia O'Reilly",
				'username' => 'user.123456',
				'location' => 'Polska',
			]
		]);

		// Show
		$response = $this->getJson('/web/api/profile');

		$response->assertStatus(200)->assertJson([
			'profile' => ['name' => "Czereśnia O'Reilly"]
		]);

		$this->assertTrue(true);
	}
}
