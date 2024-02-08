<?php

namespace Atomjoy\Apilogin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Atomjoy\Apilogin\Models\Admin;

class PasswordMail extends Mailable
{
	use Queueable, SerializesModels;

	public $user;
	public $password;

	public function __construct(User|Admin $user, $password)
	{
		$this->user = $user;
		$this->password = $password;
	}

	public function build()
	{
		return $this->subject(trans('apilogin.email.password.subject'))
			->view('apilogin::emails.password');
	}
}
