<?php

namespace Tests\Dev;

use App\Models\User;
use Atomjoy\Apilogin\Mail\F2aMail;
use Atomjoy\Apilogin\Models\F2acode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class F2aTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Register user.
	 */
	public function test_user_login_f2a(): void
	{
		Auth::logout();

		Mail::fake();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
			'f2a' => 1
		]);

		$name = $user->name;
		$email = $user->email;
		$password = 'Password123#';

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
			'f2a' => 1,
		]);

		$response = $this->postJson('web/api/login', [
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

		// $response->assertRedirect('/login/f2a/test-hash-f2a-123');

		$this->assertDatabaseHas('f2acodes', [
			'hash' => 'test-hash-f2a-123',
			'code' => 888777,
		]);

		// $this->assertNotNull($response['user']);
	}

	/** @test */
	function f2a_method()
	{
		$response = $this->getJson('/web/api/f2a');

		$response->assertStatus(405)->assertJson([
			'message' => 'The GET method is not supported for route web/api/f2a. Supported methods: POST.'
		]);
	}

	/** @test */
	function f2a_auth()
	{
		Auth::logout();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		$user->assignRole('user');

		$hash = 'hash-1234-hash-1234';
		$code = 777888;

		F2acode::create([
			'user_id' => $user->id,
			'hash' => $hash,
			'code' => $code,
		]);

		$this->assertDatabaseHas('f2acodes', [
			'hash' => $hash,
			'code' => $code,
		]);

		$response = $this->postJson('/web/api/f2a', [
			'hash' => $hash,
			'code' => $code,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'user' => [
				'roles' => [
					['name' => 'user'],
				],
				'roles_permissions' => [
					[
						'name' => 'user',
						'permissions' => [
							['name' => 'login_access']
						],
					],
				]
			]
		])->assertJsonStructure([
			'user' => [
				'is_admin', 'f2a',
				'profile', 'address', 'roles', 'roles_permissions'
			],
		])->assertJsonPath('user.email', $user->email);

		$this->assertSoftDeleted('f2acodes', [
			'hash' => $hash,
			'code' => $code,
		]);
	}

	/** @test */
	function f2a_auth_error()
	{
		Auth::logout();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		$hash = 'hash-456-hash-456';
		$code = 666555;

		F2acode::create([
			'user_id' => $user->id,
			'hash' => $hash,
			'code' => $code,
		]);

		$this->assertDatabaseHas('f2acodes', [
			'hash' => $hash,
			'code' => $code,
		]);

		Event::fake();

		for ($i = 0; $i < 7; $i++) {
			$response = $this->postJson('/web/api/f2a', [
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
		Auth::logout();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		Auth::login($user);

		$response = $this->postJson('/web/api/f2a/enable', [
			'password_current' => 'Password123#'
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Updated.'
		]);

		$this->assertDatabaseHas('users', [
			'email' => $user->email,
			'f2a' => 1,
		]);
	}

	/** @test */
	function f2a_disable()
	{
		Auth::logout();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		Auth::login($user);

		$response = $this->postJson('/web/api/f2a/disable', [
			'password_current' => 'Password123#'
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Updated.'
		]);

		$this->assertDatabaseHas('users', [
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
