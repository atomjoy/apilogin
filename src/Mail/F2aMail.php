<?php

namespace Atomjoy\Apilogin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Atomjoy\Apilogin\Models\Admin;

class F2aMail extends Mailable
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
		return $this->subject(trans('apilogin.email.f2a.subject'))
			->view('apilogin::emails.f2a');
	}
}
