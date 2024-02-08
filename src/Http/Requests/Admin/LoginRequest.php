<?php

namespace Atomjoy\Apilogin\Http\Requests\Admin;

use Atomjoy\Apilogin\Events\LoginUserError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Atomjoy\Apilogin\Mail\F2aMail;
use Atomjoy\Apilogin\Models\AdminF2acode;
use Illuminate\Support\Facades\Mail;

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

		if (!Auth::guard('admin')->attempt($this->only('email', 'password'), $this->boolean('remember_me'))) {
			RateLimiter::hit(
				$this->throttleKey(),
				config('apilogin.ratelimit_login_time', 300)
			);

			LoginUserError::dispatch($this->only('email'));

			throw new JsonException(__('apilogin.login.failed'), 422);
		}

		if (empty(Auth::guard('admin')->user()->email_verified_at)) {
			throw new JsonException(__('apilogin.login.unverified'), 422);
		}

		RateLimiter::clear($this->throttleKey());
	}

	/**
	 * 2FA Authentication
	 */
	public function f2a()
	{
		$hash = Str::uuid();
		$code = random_int(123123, 999999);

		if (Auth::guard('admin')->user()->f2a == 1) {
			$user = Auth::guard('admin')->user();

			if (app()->runningUnitTests()) {
				$hash = 'test-hash-f2a-123';
				$code = 888777;
			}

			try {
				Mail::to($user)
					->locale(app()->getLocale())
					->send(new F2aMail($user, $code));
			} catch (Exception $e) {
				report($e);
				Auth::guard('admin')->logout();
				throw new JsonException(__("apilogin.login.f2a_email_error"), 422);
			}

			AdminF2acode::create([
				'user_id' => Auth::guard('admin')->id(),
				'code' => $code,
				'hash' => $hash
			]);

			Auth::guard('admin')->logout();

			return $hash;
		}
	}

	public function ensureIsNotRateLimited(): void
	{
		if (!RateLimiter::tooManyAttempts($this->throttleKey(), config('apilogin.ratelimit_login_count', 5))) {
			return;
		}

		event(new Lockout($this));

		$seconds = RateLimiter::availableIn($this->throttleKey());

		if (app()->runningUnitTests()) {
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
