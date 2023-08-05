<?php

namespace Atomjoy\Apilogin\Models;

use App\Models\User;
use Database\Factories\ApiloginAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
	use HasFactory;

	protected $with = [];

	protected $guarded = [];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	protected static function newFactory()
	{
		if (config('apilogin.force_user_factory', false)) {
			return \Database\Factories\AddressFactory::new();
		}

		return ApiloginAddressFactory::new();
	}

	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
