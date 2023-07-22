<?php

namespace Atomjoy\Apilogin\Listeners;

use App\Models\User;
use Atomjoy\Apilogin\Events\RegisterUser;
use Atomjoy\Apilogin\Events\RegisterUserMail;
use Atomjoy\Apilogin\Events\RegisterUserMailError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Mail\RegisterMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegisterUserNotification
{
	public function handle(RegisterUser $event)
	{
		$this->sendEmail($event->user, $event->token);
	}

	public function sendEmail(User $user, $token = '')
	{
		if (config('apilogin.send_register_email', true)) {
			try {
				Mail::to($user)->locale(app()->getLocale())->send(
					new RegisterMail(
						$user,
						$this->createReserPasswordToken($user, $token)
					)
				);

				RegisterUserMail::dispatch($user);
			} catch (Exception $e) {
				report($e);
				RegisterUserMailError::dispatch($user);
				throw new JsonException(__("apilogin.register.email.error"), 422);
			}
		}
	}

	public function createReserPasswordToken(User $user, $token)
	{
		if (empty($token)) {
			$token = uniqid();
		}

		DB::table(config('auth.passwords.users.table'))->updateOrInsert([
			'email' => $user->email,
		], [
			'token' => $token,
		]);

		return $token;
	}
}
