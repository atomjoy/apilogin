<?php

use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$admin = Admin::create([
			'name' => 'Admin User',
			'email' => Config::get('atomjoy.super_admin_email', 'admin@example.com'),
			'password' => Config::get('apilogin.super_admin_password', 'Password123#'),
			'username' => 'admin',
		]);

		$admin->email_verified_at = now();
		$admin->is_admin = 1;
		$admin->save();

		$admin->assignRole('super_admin');

		$worker = Admin::create([
			'name' => 'Worker User',
			'email' => Config::get('atomjoy.worker_admin_email', 'worker@example.com'),
			'password' => Config::get('apilogin.worker_admin_password', 'Password123#'),
			'username' => 'worker',
		]);

		$worker->email_verified_at = now();
		$worker->is_admin = 1;
		$worker->save();

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
