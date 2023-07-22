<?php

namespace Tests\Dev;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function csrf_session_counter()
	{
		$response = $this->getJson('/web/api/csrf');

		$response->assertStatus(200)->assertJson([
			'message' => 'Csrf token created.',
			'counter' => 1
		]);

		$token = [
			$response->headers->getCookies()[0]->getName() => $response->headers->getCookies()[0]->getValue(),
			$response->headers->getCookies()[1]->getName() => $response->headers->getCookies()[1]->getValue(),
		];

		$response = $this->withCookies($token)->getJson('/web/api/csrf');

		$response->assertStatus(200)->assertJson([
			'message' => 'Csrf token created.',
			'counter' => 2
		]);

		// $cookies = $response->headers->getCookies();
	}

	function test_cookie()
	{
		$response = $this->withCookies([
			'dummy_token' => 'token12345'
		])->getJson('/web/api/csrf');

		$response->assertStatus(200)->assertJson([
			'message' => 'Csrf token created.'
		])->assertJsonStructure([
			'message', 'counter'
		])->assertCookie('dummy_token', 'token5678');
	}
}
