<?php

namespace Atomjoy\Apilogin\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Atomjoy\Apilogin\Contracts\HasRolesPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;
	use HasRolesPermissions;
	use HasRoles;

	/**
	 * Model table.
	 */
	protected $table = 'admins';

	/**
	 * Auth guard.
	 */
	protected $guard = 'admin';

	/**
	 * Append user relations (optional).
	 */
	protected $with = [];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
		'username',
		'location',
		'avatar',
		'bio',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
		'password' => 'hashed',
	];
}
