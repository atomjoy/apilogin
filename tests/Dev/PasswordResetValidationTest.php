<?php

namespace Tests\Dev;

use Mockery;
use Mockery\MockInterface;
use App\Models\User;
use Atomjoy\Apilogin\Events\PasswordResetError;
use Atomjoy\Apilogin\Http\Controllers\PasswordResetController;
use Atomjoy\Apilogin\Http\Requests\PasswordResetRequest;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PasswordResetValidationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function email_database_error()
	{
		Event::fake(PasswordResetError::class);

		$user = User::factory()->create();

		$valid = [
			'email' => $user->email,
		];

		$request = null;

		// Test error
		putenv('TEST_DATABASE=true');
		$response = $this->postJson('web/api/password', [
			'email' => $user->email,
		]);
		$response->assertStatus(422)->assertJson([
			'message' => 'Password has not been updated.',
		]);
		putenv('TEST_DATABASE=false');

		try {
			$request = $this->partialMock(PasswordResetRequest::class, static function (MockInterface $mock) use ($valid) {
				// Add only updated methods
				$mock->shouldReceive('validated')->andReturn($valid);
				$mock->shouldReceive('testDatabase')->andThrow(new Exception());
			});

			// Build controller
			$controller = $this->controller();

			// Call custom controller method
			$response = $this->app->call([$controller, 'index'], [
				'request' => $request,
			]);
		} catch (Exception $e) {
			// Catch exception
			$this->assertEquals($e->getMessage(), 'Password has not been updated.');
		}

		// Then catch event
		Event::assertDispatched(PasswordResetError::class, function ($e) use ($valid) {
			return $valid == $e->valid;
		});

		// Call anonymous controller method
		$response = $this->app->call($controller, [
			'request' => $request,
		]);
		$this->assertSame($valid, $request->validated());
		$this->assertSame($valid, $response);
	}

	protected function controller(): PasswordResetController
	{
		return new class extends PasswordResetController
		{
			public function __invoke(PasswordResetRequest $request): array
			{
				return $request->validated();
			}
		};
	}
}
