<?php

namespace Tests\Dev;

use App\Models\User;
use Atomjoy\Apilogin\Events\LoginUser;
use Atomjoy\Apilogin\Events\LoginUserError;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Register user.
	 */
	public function test_user_login(): void
	{
		Auth::logout();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		$user->assignRole(['user']);

		$name = $user->name;
		$email = $user->email;
		$password = 'Password123#';

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		$response = $this->postJson('web/api/login', [
			'email' => $email,
			'password' => $password,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.',
			'redirect' => null,
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

		$this->assertNotNull($response['user']);
	}

	/** @test */
	function login_user_method()
	{
		$res = $this->getJson('/web/api/login');

		$res->assertStatus(405)->assertJson([
			'message' => 'The GET method is not supported for route web/api/login. Supported methods: POST.'
		]);
	}

	/** @test */
	function login_user_email_unverified()
	{
		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
			'email_verified_at' => null
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'Password123#',
			'remember_me' => 1,
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'Your email address is not verified.'
		]);
	}

	/** @test */
	function login_user_rate_limited()
	{
		Event::fake();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		for ($i = 0; $i < 6; $i++) {
			$res = $this->postJson('/web/api/login', [
				'email' => $user->email,
				'password' => 'ErrorPassword123#',
			]);
		}

		$res->assertStatus(422)->assertJson([
			'message' => 'Too many login attempts. Please try again in 60 seconds.'
		]);

		// Test RateLimit events
		Event::assertDispatched(Lockout::class, function ($e) use ($user) {
			return true;
		});
	}

	/** @test */
	function login_user_errors()
	{
		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => '',
			'password' => 'password123',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The email field is required.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email . 'error###',
			'password' => 'password123',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The email field must be a valid email address.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => '',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field is required.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'password',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field must be at least 11 characters.'
		]);
	}

	/** @test */
	function login_user_soft_deleted()
	{
		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#@!'),
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'Password123#@!',
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'user'
		])->assertJsonPath('user.email', $user->email);

		$user->delete(); // Soft deleted users not allowed

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'Password123#@!',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'These credentials do not match our records.'
		]);
	}

	/** @test */
	function login_user_login_events()
	{
		Event::fake();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		$response = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'Password123#',
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'user'
		])->assertJsonPath('user.email', $user->email);

		// Test events
		Event::assertDispatched(LoginUser::class, function ($e) use ($user) {
			return $e->user->email == $user->email;
		});

		// Test event listeners
		// Event::assertListening(
		// 	LoginUser::class,
		// 	LoginUserNotification::class,
		// );
	}

	/** @test */
	function login_user_login_error_events()
	{
		Event::fake();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);
		$password = 'Password123#Error';

		$response = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => $password,
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'These credentials do not match our records.'
		])->assertJsonMissing([
			'user'
		])->assertJsonMissingPath('user.email', $user->email);;

		// Test events (not dispatched)
		Event::assertDispatched(LoginUserError::class, function ($e) use ($user) {
			return $e->valid['email'] == $user->email;
		});
	}

	/**
	 * Super admin login.
	 */
	public function test_admin_login(): void
	{
		Auth::logout();

		$this->actingAs(User::findOrFail(1));

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);
	}

	/**
	 * Worer login.
	 */
	public function test_worker_login(): void
	{
		Auth::logout();

		$this->actingAs(User::findOrFail(2));

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);
	}

	/**
	 * User login error.
	 */
	public function test_user_login_admin_panel(): void
	{
		Auth::logout();

		$user = User::factory()->create([
			'email' => 'dummy@gmail.com',
			'password' => Hash::make('Password123#'),
		]);

		$this->actingAs($user);

		$response = $this->getJson('/web/api/admin/test');

		$response->assertStatus(403)->assertJson([
			'message' => 'User does not have the right roles (is_admin).'
		]);
	}
}
