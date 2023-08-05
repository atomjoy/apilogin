<?php

namespace Tests\Dev;

use App\Models\User;
use Atomjoy\Apilogin\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAddressTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function create_model()
	{
		$user = User::factory()->create();

		$this->actingAs($user);

		$response = $this->putJson(
			'/web/api/address',
			Address::factory()->make([
				'city' => 'Warszawa'
			])->toArray()
		);

		$response->assertStatus(200)->assertJson([
			'message' => 'Address has been updated.',
			'address' => ['city' => 'Warszawa']
		]);

		$response = $this->getJson('/web/api/address');

		$response->assertStatus(200)->assertJson([
			'address' => ['city' => 'Warszawa']
		]);

		$this->assertTrue(true);
	}
}
