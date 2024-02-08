<?php

namespace Atomjoy\Apilogin\Http\Requests\Admin;

use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Models\AdminF2acode;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class F2aRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return true; // Allow all
	}

	public function rules()
	{
		return [
			'hash' => 'required|min:6|max:64',
			'code' => 'required|min:3|max:32',
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
			collect(request()->json()->all())->only(['hash', 'code', 'remember_me'])->toArray()
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

		if (!Auth::guard('admin')->check()) {
			RateLimiter::hit(
				$this->throttleKey(),
				config('apilogin.ratelimit_login_time', 300)
			);

			$f2a = AdminF2acode::where($this->only('hash', 'code'))
				->whereTime(
					'created_at',
					'>=',
					now()->subMinutes(5)->toDateTimeString()
				)->first();

			if ($f2a instanceof AdminF2acode) {
				Auth::guard('admin')->login($f2a->user, $this->boolean('remember_me'));

				if (Auth::guard('admin')->check()) {
					$f2a->delete();
				}
			} else {
				throw new JsonException(__('apilogin.login.f2a_error'), 422);
			}
		}

		RateLimiter::clear($this->throttleKey());
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
		return Str::transliterate(Str::lower($this->input('hash')));
	}
}
