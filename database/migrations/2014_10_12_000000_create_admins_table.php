<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('admins', function (Blueprint $table) {
			$table->id();
			$table->string('name')->nullable();
			$table->string('email')->unique();
			$table->string('password');
			$table->string('username')->unique();
			$table->string('location', 50)->nullable();
			$table->string('avatar')->nullable();
			$table->text('bio')->nullable();
			$table->boolean('f2a')->nullable()->default(0);
			$table->boolean('is_admin')->nullable()->default(1);
			$table->timestamp('email_verified_at')->nullable()->useCurrent();
			$table->rememberToken();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('admins');
	}
};
