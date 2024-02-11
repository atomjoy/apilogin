<?php

namespace Tests\Dev;

use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUploadAvatarTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function upload_avatar()
	{
		$disk = 's3';

		Storage::fake($disk);

		$user = Admin::create([
			'name' => 'Adelajda Brzęczyszczykiewicz',
			'email' => uniqid() . '@gmail.com',
			'username' => 'benbax',
			'password' => 'invalidpass',
		]);

		$user->is_admin = 1;
		$user->save();

		$user->assignRole('worker');

		$this->assertDatabaseHas('admins', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$this->actingAs($user, 'admin');

		$response = $this->postJson('/web/api/admin/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.webp'),
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'The avatar field has invalid image dimensions.',
		]);

		$response = $this->postJson('/web/api/admin/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.png'),
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'The avatar field must be a file of type: webp.',
		]);

		$response = $this->postJson('/web/api/admin/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.webp', 200, 200),
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Avatar has been uploaded.',
			'avatar' => 'avatars/admin/' . $user->id . '.webp'
		]);

		$this->assertDatabaseHas('admins', [
			'avatar' => 'avatars/admin/' . $user->id . '.webp',
		]);

		Storage::disk($disk)->assertExists('avatars/admin/' . $user->id . '.webp');
	}
}
