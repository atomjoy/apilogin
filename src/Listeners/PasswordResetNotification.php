<?php

namespace Atomjoy\Apilogin\Listeners;

use Exception;
use App\Models\User;
use Atomjoy\Apilogin\Events\PasswordReset;
use Atomjoy\Apilogin\Events\PasswordResetMail;
use Atomjoy\Apilogin\Events\PasswordResetMailError;
use Atomjoy\Apilogin\Mail\PasswordMail;
use Illuminate\Support\Facades\Mail;

class PasswordResetNotification
{
	public function handle(PasswordReset $event)
	{
		$this->sendEmail($event->user, $event->password);
	}

	public function sendEmail(User $user, $password)
	{
		if (config('apilogin.send_password_email', true)) {
			try {
				Mail::to($user)->locale(app()->getLocale())->send(new PasswordMail($user, $password));
				PasswordResetMail::dispatch($user);
			} catch (Exception $e) {
				report($e);
				PasswordResetMailError::dispatch($user);
				throw new JsonException(__("apilogin.reset.password.email.error"), 422);
			}
		}
	}
}
