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

		$response = $this->patchJson(
			'/web/api/profile',
			Profile::factory()->make([
				'name' => "Czereśnia O'Reilly"
			])->toArray()
		);

		$response->assertStatus(200)->assertJson([
			'message' => 'Profile has been updated.',
			'profile' => ['name' => "Czereśnia O'Reilly"]
		]);

		$response = $this->getJson('/web/api/profile');

		$response->assertStatus(200)->assertJson([
			'profile' => ['name' => "Czereśnia O'Reilly"]
		]);

		$this->assertTrue(true);
	}
}
