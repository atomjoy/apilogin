<?php

namespace Atomjoy\Apilogin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class RegisterMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public User $user,
		public $code = 'invalid',
	) {
	}

	public function build()
	{
		return $this->subject(trans('apilogin.email.register.subject'))
			->view('apilogin::emails.register');
	}
}
