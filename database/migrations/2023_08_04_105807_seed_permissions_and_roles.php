<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\PermissionsSeeder;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$seeder = new PermissionsSeeder;
		$seeder->run();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		//
	}
};
