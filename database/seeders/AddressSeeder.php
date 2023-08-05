<?php

namespace Database\Seeders;

use App\Models\User;
use Atomjoy\Apilogin\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// User
		$users = User::factory()->count(2)->create();

		// Address
		$users->each(function ($user) {
			// hasOne
			$a = Address::factory()->count(1)->make();
			$user->address()->save($a->first());
			// hasMany
			// $all = Address::factory()->count(2)->make();
			// $user->addresses()->saveMany($all);
		});
	}
}
