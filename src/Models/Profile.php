<?php

namespace Atomjoy\Apilogin\Models;

use App\Models\User;
use Database\Factories\ApiloginProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
	use HasFactory, SoftDeletes;

	protected $with = [];

	protected $guarded = [];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	protected static function newFactory()
	{
		if (config('apilogin.force_user_factory', false)) {
			return \Database\Factories\ProfileFactory::new();
		}

		return ApiloginProfileFactory::new();
	}

	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
