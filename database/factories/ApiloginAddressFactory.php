<?php

namespace Database\Factories;

use Atomjoy\Apilogin\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class ApiloginAddressFactory extends Factory
{
	protected $model = Address::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'country' => fake()->country(),
			'state' => fake()->state(), // 'California',
			'city' => fake()->city(),
			'street' => fake()->streetAddress(),
			'postal_code' => fake()->postcode(),
		];
	}
}
