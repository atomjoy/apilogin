<?php

namespace Tests\Dev;

use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordChangeTest extends TestCase
{
	/** @test */
	function change_logged_user_password()
	{
		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'Password1234X',
			'password' => 'Password1234#',
			'password_confirmation' => 'Password1234#'
		]);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);

		// Auth user
		$user = Admin::create([
			'name' => 'Adelajda Brzęczysz',
			'email' => uniqid() . '@gmail.com',
			'username' => 'benbaxboo',
			'password' => Hash::make('Password123#'),
		]);

		$user->is_admin = 1;
		$user->save();

		$user->assignRole('worker');

		$this->assertDatabaseHas('admins', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$this->actingAs($user, 'admin');

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'password123',
			'password' => 'password',
			'password_confirmation' => 'password123'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field must be at least 11 characters.',
		]);

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'password123',
			'password' => 'password1234',
			'password_confirmation' => 'password'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field must contain at least one uppercase and one lowercase letter.'
		]);

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'password123',
			'password' => 'Password1234',
			'password_confirmation' => 'Password1234'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field must contain at least one symbol.'
		]);

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'password123',
			'password' => 'Passwordoooo#',
			'password_confirmation' => 'Passwordoooo#'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field must contain at least one number.'
		]);

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'password123',
			'password' => 'Password1234#',
			'password_confirmation' => 'Password1234#1'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field confirmation does not match.'
		]);

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'Password123#',
			'password' => 'Password1234##',
			'password_confirmation' => 'Password1234##'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Password has been updated.'
		]);

		Auth::guard('admin')->logout();

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'Password1234#',
			'password' => 'Password123#',
			'password_confirmation' => 'Password123#'
		]);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);

		$res = $this->postJson('/web/api/admin/login', [
			'email' => $user->email,
			'password' => 'Password1234##'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'user'
		]);

		$this->assertNotNull($res['user']);
	}

	/** @test */
	function dont_allow_change_not_logged_user_password()
	{
		Auth::guard('admin')->logout();

		$res = $this->postJson('/web/api/admin/password/change', [
			'password_current' => 'password123',
			'password' => 'password1234',
			'password_confirmation' => 'password1234'
		]);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);
	}

	/** @test */
	function user_logout()
	{
		$user = Admin::create([
			'name' => 'Adelajda Brzęcz',
			'email' => uniqid() . '@gmail.com',
			'username' => 'benbaxboox',
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

		$res = $this->getJson('/web/api/admin/logout');

		$res->assertStatus(200)->assertJson([
			'message' => 'Logged out.'
		]);
	}
}
