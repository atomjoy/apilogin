<?php

namespace Atomjoy\Apilogin\Listeners;

use App\Models\User;
use Atomjoy\Apilogin\Events\ActivateUser;
use Illuminate\Support\Facades\DB;

class ActivateUserNotification
{
	public function handle(ActivateUser $event)
	{
		$this->deletePasswordToken($event->user);
	}

	public function deletePasswordToken(User $user)
	{
		DB::table(config('auth.passwords.users.table'))
			->where('email', $user->email)
			->delete();
	}
}
