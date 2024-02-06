<?php

namespace Atomjoy\Apilogin\Contracts;

use Atomjoy\Apilogin\Models\Address;
use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasProfilAddress
{
	/**
	 * Get the profile associated with the user.
	 */
	public function address(): HasOne
	{
		return $this->hasOne(Address::class);
	}

	/**
	 * Get the profile associated with the user.
	 */
	public function profile(): HasOne
	{
		return $this->hasOne(Profile::class);
	}
}
