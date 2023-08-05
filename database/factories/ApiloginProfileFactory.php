<?php

namespace Database\Factories;

use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ApiloginProfileFactory extends Factory
{
	protected $model = Profile::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'username' => uniqid("user."),
			'name' => fake()->name(),
			'location' => fake()->city(),
			'bio' => fake()->sentence(10),
			'avatar' => 'https://source.unsplash.com/random/256x256'
			// 'avatar' => $this->faker->imageUrl(256, 256, 'animals', true),
		];
	}
}
