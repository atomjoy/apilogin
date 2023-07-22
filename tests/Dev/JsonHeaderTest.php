<?php

namespace Tests\Dev;


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class JsonHeaderTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Check json header (get)
	 */
	public function test_csrf_json_accept_header(): void
	{
		$response = $this->get('web/api/csrf');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header (get)
	 */
	public function test_logout_json_accept_header(): void
	{
		Auth::login(User::factory()->create());
		$response = $this->get('web/api/logout');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header (get)
	 */
	public function test_logged_json_accept_header(): void
	{
		$response = $this->get('web/api/logged');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);

		Auth::login(User::factory()->create());

		$response = $this->get('web/api/logged');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header (get)
	 */

	public function test_activate_json_accept_header(): void
	{
		$response = $this->get('web/api/activate/1/code1');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header
	 */
	public function test_login_json_accept_header(): void
	{
		$response = $this->post('web/api/login');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header
	 */
	public function test_register_json_accept_header(): void
	{
		$response = $this->post('web/api/register');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header
	 */
	public function test_password_reset_json_accept_header(): void
	{
		$response = $this->post('web/api/password');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header
	 */
	public function test_password_change_json_accept_header(): void
	{
		Auth::login(User::factory()->create());
		$response = $this->post('web/api/password/change');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}

	/**
	 * Check json header
	 */
	public function test_password_confirm_json_accept_header(): void
	{
		Auth::login(User::factory()->create());
		$response = $this->post('web/api/password/confirm');
		$response->assertStatus(406)->assertJson([
			'message' => 'Not Acceptable.',
		]);
	}
}
