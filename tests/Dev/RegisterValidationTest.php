<?php

namespace Tests\Dev;

use Mockery;
use Mockery\MockInterface;
use App\Models\User;
use Atomjoy\Apilogin\Events\RegisterUserError;
use Atomjoy\Apilogin\Http\Controllers\RegisterController;
use Atomjoy\Apilogin\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function email_database_error()
	{
		Event::fake(RegisterUserError::class);

		$user = User::factory()->make([
			'email' => uniqid() . '@gmail.com',
			'password' => Hash::make('Password123#')
		]);

		// Invalid User::create() validation data without password and password_confirmation
		// will throw error event and exception from controller index method
		$valid = [
			'name' => $user->name,
			'email' => $user->email,
			// Throw error
			// 'password' => 'Password123#',
			// 'password_confirmation' => 'Password123#',
		];

		$request = null;

		// Test throw error from controller
		putenv('TEST_DATABASE=true');
		$response = $this->postJson('web/api/register', [
			'name' => $user->name,
			'email' => $user->email,
			'password' => 'Password123#',
			'password_confirmation' => 'Password123#',
		]);
		$response->assertStatus(422)->assertJson([
			'message' => 'Something went wrong, please try again later.',
		]);
		putenv('TEST_DATABASE=false');

		try {
			// Mock validation request
			$request = $this->instance(
				RegisterRequest::class,
				Mockery::mock(RegisterRequest::class, static function (MockInterface $mock) use ($valid) {
					// Add all methods used in controller from RegisterRequest
					$mock->shouldReceive('validated')->andReturn($valid);
					$mock->shouldReceive('testDatabase')->andReturn(null);
					// Throw exception from controller
					// $mock->shouldReceive('testDatabase')->andThrow(new Exception());
				})
			);

			// Build controller
			$controller = $this->controller();

			// Call custom controller method
			$response = $this->app->call([$controller, 'index'], [
				'request' => $request,
			]);
		} catch (Exception $e) {
			// Catch exception
			$this->assertEquals($e->getMessage(), 'Something went wrong, please try again later.');
		}

		// Then catch event
		Event::assertDispatched(RegisterUserError::class, function ($e) use ($valid) {
			return $valid == $e->valid;
		});

		try {
			// Mock partial
			$request = $this->partialMock(RegisterRequest::class, static function (MockInterface $mock) use ($valid) {
				// Add only updated methods
				$mock->shouldReceive('validated')->andReturn($valid);
				// Throw exception from controller
				// $mock->shouldReceive('testDatabase')->andThrow(new Exception());
			});

			// Build controller
			$controller = $this->controller();

			// Call custom controller method
			$response = $this->app->call([$controller, 'index'], [
				'request' => $request,
			]);
		} catch (Exception $e) {
			// Catch exception
			$this->assertEquals($e->getMessage(), 'Something went wrong, please try again later.');
		}

		// Then catch event
		Event::assertDispatched(RegisterUserError::class, function ($e) use ($valid) {
			return $valid == $e->valid;
		});

		// Call anonymous controller method
		$response = $this->app->call($controller, [
			'request' => $request,
		]);
		$this->assertSame($valid, $request->validated());
		$this->assertSame($valid, $response);
	}

	protected function controller(): RegisterController
	{
		return new class extends RegisterController
		{
			public function __invoke(RegisterRequest $request): array
			{
				return $request->validated();
			}
		};
	}
}
