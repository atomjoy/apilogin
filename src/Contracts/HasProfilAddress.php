<?php

namespace Atomjoy\Apilogin\Contracts;

use App\Models\User;
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

	/**
	 * The "booted" method of the model events.
	 */
	// protected static function booted(): void
	// {
	// 	parent::boot();

	// 	static::created(function (User $user) {
	// 		$user->address()->sync([]);
	// 		$user->profile()->sync([
	// 			'name' => ucfirst($user->nane) ?? config('apilogin.dafault.user.name', 'Guest ' . time()),
	// 			'username' => uniqid('user.'),
	// 		]);
	// 	});
	// }
}
