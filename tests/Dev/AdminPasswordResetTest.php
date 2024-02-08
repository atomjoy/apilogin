<?php

namespace Tests\Dev;

use Atomjoy\Apilogin\Mail\PasswordMail;
use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
	use RefreshDatabase;

	public function test_reset_password_user_login_error(): void
	{
		$response = $this->postJson('web/api/password', [
			'email' => 'error@laravel.com',
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'An account with this email address does not exist.',
		]);
	}

	public function test_reset_password_user_login(): void
	{
		Mail::fake();

		$user = Admin::create([
			'name' => 'Alex',
			'email' => uniqid() . '@laravel.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('admins', [
			'name' => $name,
			'email' => $email,
		]);

		$response = $this->postJson('web/api/admin/password', [
			'email' => $email,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'A new password has been sent to the e-mail address provided.',
		]);

		Mail::assertSent(PasswordMail::class, function ($mail) use ($email, $name) {
			$mail->build();
			$html = $mail->render();
			$subject = $mail->subject;
			$password = $this->getPassword($html);

			$this->assertTrue(strpos($html, $name) !== false);
			$this->assertMatchesRegularExpression('/word>[a-zA-Z0-9#]+<\/pass/', $html);
			$this->assertEquals("👋 Your new password.", $subject, 'The subject was not the right one.');

			$res = $this->postJson('/web/api/admin/login', [
				'email' => $email,
				'password' => $password,
			]);

			$res->assertStatus(200)->assertJson([
				'message' => 'Authenticated.'
			]);

			return $mail->hasTo($email) && $mail->hasSubject("👋 Your new password.");
		});
	}

	/**
	 * Register user.
	 */
	public function test_reset_password_user_login_event(): void
	{
		Event::fake([MessageSent::class]);

		$user = Admin::create([
			'name' => 'AlexFlux',
			'email' => uniqid() . '@laravel.com',
			'password' => Hash::make('Password123#'),
			'username' => uniqid('user.'),
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('admins', [
			'name' => $name,
			'email' => $email,
		]);

		$response = $this->postJson('web/api/admin/password', [
			'email' => $email,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'A new password has been sent to the e-mail address provided.',
		]);

		Event::assertDispatched(MessageSent::class, function ($e) use ($email, $name) {
			$html = $e->message->getHtmlBody();
			$subject = $e->message->getSubject();
			$password = $this->getPassword($html);
			$recipient = collect($e->message->getTo())->first()->getAddress();

			$this->assertStringContainsString($name, $html);
			$this->assertMatchesRegularExpression('/word>[a-zA-Z0-9#]+<\/pass/', $html);
			$this->assertEquals("👋 Your new password.", $subject, 'The subject was not the right one.');

			$res = $this->postJson('/web/api/admin/login', [
				'email' => $email,
				'password' => $password,
			]);

			$res->assertStatus(200)->assertJson([
				'message' => 'Authenticated.'
			]);

			return $recipient == $email;
		});
	}

	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9#]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}
}
