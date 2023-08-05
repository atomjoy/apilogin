<?php

namespace Tests\Dev;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadAvatarTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function upload_avatar()
	{
		Storage::fake('public');

		$user = User::factory()->create([
			'name' => 'Adelajda Brzęczyszczykiewicz',
			'email' => uniqid() . '@gmail.com'
		]);

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$this->actingAs($user);

		$response = $this->postJson('/web/api/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.webp'),
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'The avatar field has invalid image dimensions.',
		]);

		$response = $this->postJson('/web/api/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.png'),
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'The avatar field must be a file of type: webp.',
		]);

		$response = $this->postJson('/web/api/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.webp', 200, 200),
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Avatar has been uploaded.',
			'avatar' => 'avatars/' . $user->id . '.webp'
		]);

		$this->assertDatabaseHas('profiles', [
			'avatar' => 'avatars/' . $user->id . '.webp',
		]);

		Storage::disk('public')->assertExists('avatars/' . $user->id . '.webp');
	}
}
