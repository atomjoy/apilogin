<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$admin = User::create([
			'name' => 'Admin User',
			'email' => Config::get('atomjoy.super_admin_email', 'admin@atom.joy'),
			'password' => 'Password123#',
		]);

		$admin->email_verified_at = now();
		$admin->is_admin = 1;
		$admin->save();

		$admin->profile()->create([
			'name' => 'Admin User',
			'username' => 'admin',
		]);

		$admin->address()->create([]);

		$admin->assignRole('user');
		$admin->assignRole('super_admin');

		$worker = User::create([
			'name' => 'Worker User',
			'email' => Config::get('atomjoy.worker_admin_email', 'worker@atom.joy'),
			'password' => 'Password123#',
		]);

		$worker->profile()->create([
			'name' => 'Worker User',
			'username' => 'worker',
		]);

		$worker->address()->create([]);

		$worker->email_verified_at = now();
		$worker->is_admin = 1;
		$worker->save();

		$worker->assignRole('user');
		$worker->assignRole('worker');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		//
	}
};
