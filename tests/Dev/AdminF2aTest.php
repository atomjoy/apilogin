<?php

namespace Tests\Dev;

use Atomjoy\Apilogin\Mail\F2aMail;
use Atomjoy\Apilogin\Models\Admin;
use Atomjoy\Apilogin\Models\AdminF2acode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminF2aTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Register user.
	 */
	public function test_user_login_f2a(): void
	{
		Auth::shouldUse('admin'); // Change guard

		Auth::logout();

		Mail::fake();

		$user = Admin::create([
			'name' => 'f2a Admin',
			'email' => 'f2a_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
			'is_admin' => 1,
		]);

		$user->assignRole('super_admin');

		$user->f2a = 1;
		$user->save();

		$name = $user->name;
		$email = $user->email;
		$password = 'Password123#';

		$this->assertDatabaseHas('admins', [
			'name' => $name,
			'email' => $email,
			'f2a' => 1,
		]);

		$response = $this->postJson('web/api/admin/login', [
			'email' => $email,
			'password' => $password,
		]);

		Mail::assertSent(F2aMail::class, function ($mail) use ($email, $name) {
			$mail->build();
			$html = $mail->render();
			$subject = $mail->subject;

			$this->assertTrue(strpos($html, '<ul>8</ul>') !== false);
			$this->assertTrue(strpos($html, $name) !== false);
			$this->assertMatchesRegularExpression('/ul>[a-zA-Z0-9#]+<\/ul/', $html);

			return $mail->hasTo($email) && $mail->hasSubject("Authentication code.");
		});

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'user' => null
		])->assertJsonStructure([
			'redirect', 'user'
		]);

		// $response->assertRedirect('/admin/login/f2a/test-hash-f2a-123');

		$this->assertDatabaseHas('adminf2acodes', [
			'hash' => 'test-hash-f2a-123',
			'code' => 888777,
		]);

		// $this->assertNotNull($response['user']);
	}

	/** @test */
	function f2a_method()
	{
		$response = $this->getJson('/web/api/admin/f2a');

		$response->assertStatus(405)->assertJson([
			'message' => 'The GET method is not supported for route web/api/admin/f2a. Supported methods: POST.'
		]);
	}

	/** @test */
	function f2a_auth()
	{
		Auth::shouldUse('admin');

		Auth::logout();

		$user = Admin::create([
			'name' => 'Dummy Admin',
			'email' => 'dummy_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$user->f2a = 1;
		$user->is_admin = 1;
		$user->save();


		$user->assignRole('super_admin');

		$hash = 'hash-1234-hash-1234';
		$code = 777888;

		AdminF2acode::create([
			'user_id' => $user->id,
			'hash' => $hash,
			'code' => $code,
		]);

		$this->assertDatabaseHas('adminf2acodes', [
			'hash' => $hash,
			'code' => $code,
		]);

		$response = $this->postJson('/web/api/admin/f2a', [
			'hash' => $hash,
			'code' => $code,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'user' => [
				'roles' => [
					['name' => 'super_admin'],
				],
				'roles_permissions' => [
					[
						'name' => 'super_admin',
						'permissions' => [
							['name' => 'admin_access'],
							['name' => 'login_access']
						],
					],
				]
			]
		])->assertJsonStructure([
			'user' => [
				'is_admin', 'f2a',
				'roles', 'roles_permissions'
			],
		])->assertJsonPath('user.email', $user->email);

		$this->assertSoftDeleted('adminf2acodes', [
			'hash' => $hash,
			'code' => $code,
		]);
	}

	/** @test */
	function f2a_auth_error()
	{
		Auth::shouldUse('admin'); // Change guard

		Auth::logout();

		$user = Admin::create([
			'name' => 'Dummy Admin',
			'email' => 'dummy_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$user->assignRole('super_admin');

		$hash = 'hash-456-hash-456';
		$code = 666555;

		AdminF2acode::create([
			'user_id' => $user->id,
			'hash' => $hash,
			'code' => $code,
		]);

		$this->assertDatabaseHas('adminf2acodes', [
			'hash' => $hash,
			'code' => $code,
		]);

		Event::fake();

		for ($i = 0; $i < 7; $i++) {
			$response = $this->postJson('/web/api/admin/f2a', [
				'hash' => $hash,
				'code' => 'err123',
			]);
		}

		$response->assertStatus(422)->assertJson([
			'message' => 'Too many login attempts. Please try again in 60 seconds.'
		]);
	}

	/** @test */
	function f2a_enable()
	{
		Auth::shouldUse('admin'); // Change guard

		Auth::logout();

		$user = Admin::create([
			'name' => 'Dummy Admin',
			'email' => 'dummy_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$user->f2a = 0;
		$user->is_admin = 1;
		$user->save();

		$user->assignRole('super_admin');

		Auth::login($user);

		$response = $this->postJson('/web/api/admin/f2a/enable', [
			'password_current' => 'Password123#'
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Updated.'
		]);

		$this->assertDatabaseHas('admins', [
			'email' => $user->email,
			'f2a' => 1,
		]);
	}

	/** @test */
	function f2a_disable()
	{
		Auth::shouldUse('admin'); // Change guard

		Auth::logout();

		$user = Admin::create([
			'name' => 'Dummy Admin',
			'email' => 'dummy_super_admin@gmail.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$user->f2a = 1;
		$user->is_admin = 1;
		$user->save();

		$user->assignRole('super_admin');

		Auth::login($user);

		$response = $this->postJson('/web/api/admin/f2a/disable', [
			'password_current' => 'Password123#'
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Updated.'
		]);

		$this->assertDatabaseHas('admins', [
			'email' => $user->email,
			'f2a' => 0,
		]);
	}

	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9#]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}
}
