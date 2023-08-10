<?php

namespace Atomjoy\Apilogin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
	public function user()
	{
		return $this->notifiable();
	}
}
