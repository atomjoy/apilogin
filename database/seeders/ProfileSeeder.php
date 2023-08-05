<?php

namespace Database\Seeders;

use App\Models\User;
use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// User
		$users = User::factory()->count(2)->create();

		// Profile
		$users->each(function ($user) {
			// hasOne
			$a = Profile::factory()->count(1)->make();
			$user->profile()->save($a->first());
			// hasMany
			// $all = Profil::factory()->count(2)->make();
			// $user->addresses()->saveMany($all);
		});
	}
}
