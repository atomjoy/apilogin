<?php

namespace Atomjoy\Apilogin\Http\Requests;

use Atomjoy\Apilogin\Events\LoginUserError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return true; // Allow all
	}

	public function rules()
	{
		$email = 'email:rfc,dns';
		if (env('APP_DEBUG') == true) {
			$email = 'email';
		}

		return [
			'email' => ['required', $email, 'max:191'],
			'password' => 'required|min:11|max:50',
			'remember_me' => 'sometimes|boolean'
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new ValidationException($validator, response()->json([
			'message' => $validator->errors()->first()
		], 422));
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only(['email', 'password', 'remember_me'])->toArray()
		);
	}

	/**
	 * Attempt to authenticate the request's credentials.
	 *
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function authenticate(): void
	{
		$this->ensureIsNotRateLimited();

		if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember_me'))) {
			RateLimiter::hit(
				$this->throttleKey(),
				config('apilogin.ratelimit_login', 300)
			);

			LoginUserError::dispatch($this->only('email'));

			throw new JsonException(__('apilogin.login.failed'), 422);
		}

		if (empty(Auth::user()->email_verified_at)) {
			throw new JsonException(__('apilogin.login.unverified'), 422);
		}

		RateLimiter::clear($this->throttleKey());
	}

	public function ensureIsNotRateLimited(): void
	{
		if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
			return;
		}

		event(new Lockout($this));

		$seconds = RateLimiter::availableIn($this->throttleKey());

		if (env('APP_ENV') == 'testing') {
			$seconds = 60;
		}

		throw new JsonException(__('apilogin.login.throttle', [
			'seconds' => $seconds,
			'minutes' => ceil($seconds / 60),
		]), 422);
	}

	public function throttleKey(): string
	{
		return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
	}
}
