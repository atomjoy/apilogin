<?php

namespace Tests\Dev;

use App\Models\User;
use Atomjoy\Apilogin\Mail\ChangeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailChangeTest extends TestCase
{
	use RefreshDatabase, WithFaker;

	public function test_email_change_not_logged(): void
	{
		$user = User::factory()->create([
			'name' => 'Alex',
			'email' => uniqid() . '@gmail.com'
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		$response = $this->postJson('web/api/change/email', [
			'email' => $email,
		]);

		$response->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.',
		]);
	}

	public function test_email_change_logged(): void
	{
		Mail::fake();

		$user = User::factory()->create([
			'name' => 'Alex',
			'email' => uniqid() . '@gmail.com'
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		$this->actingAs($user);

		$new_email = 'new_' . $email;

		$response = $this->postJson('web/api/change/email', [
			'email' => $new_email,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'The e-mail with the code has been sent.',
		]);

		$cache = Cache::get('emailchange_' . md5($user->id));
		$needle = $user->id . '|' . $new_email . '|';

		$this->assertNotEmpty($cache);
		$this->assertStringContainsString($needle, $cache);

		Mail::assertSent(ChangeMail::class, function ($mail) use ($email, $name) {
			$mail->build();
			$html = $mail->render();
			$subject = $mail->subject;

			$this->assertTrue(strpos($html, $name) !== false);
			$this->assertEquals("👋 Account e-mail change.", $subject, 'The subject was not the right one.');
			$this->assertMatchesRegularExpression('/\/change\/email\/[0-9]+\/[a-z0-9]+\?locale=[a-z]{2}"/i', $html);

			return $mail->hasTo($email) && $mail->hasSubject("👋 Account e-mail change.");
		});
	}

	/**
	 * Register user.
	 */
	public function test_email_change_event(): void
	{
		Event::fake([MessageSent::class]);

		$user = User::factory()->create([
			'name' => 'Alex',
			'email' => uniqid() . '@gmail.com'
		]);

		$name = $user->name;
		$email = $user->email;

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		$this->actingAs($user);

		$response = $this->postJson('web/api/change/email', [
			'email' => $email,
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'It is your current email address.',
		]);

		$new_email = 'new_' . $email;

		$response = $this->postJson('web/api/change/email', [
			'email' => $new_email,
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'The e-mail with the code has been sent.',
		]);

		$cache = Cache::get('emailchange_' . md5($user->id));
		$needle = $user->id . '|' . $new_email . '|';

		$this->assertNotEmpty($cache);
		$this->assertStringContainsString($needle, $cache);

		Event::assertDispatched(MessageSent::class, function ($e) use ($email, $name) {
			$html = $e->message->getHtmlBody();
			$subject = $e->message->getSubject();
			$recipient = collect($e->message->getTo())->first()->getAddress();

			$this->assertStringContainsString($name, $html);
			$this->assertEquals("👋 Account e-mail change.", $subject, 'The subject was not the right one.');
			$this->assertMatchesRegularExpression('/\/change\/email\/[0-9]+\/[a-z0-9]+\?locale=[a-z]{2}"/i', $html);

			return $recipient == $email;
		});
	}
}
