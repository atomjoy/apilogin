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
		Schema::create('profiles', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->unique();
			$table->string('username', 50)->unique();
			$table->string('name', 50)->nullable();
			$table->string('location', 50)->nullable();
			$table->string('avatar')->nullable();
			$table->text('bio')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('user_id')
				->references('id')
				->on('users')
				->cascadeOnUpdate()
				->cascadeOnDelete();
			// ->nullOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('profiles');
	}
};
