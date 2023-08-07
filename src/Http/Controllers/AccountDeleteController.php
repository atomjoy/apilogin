<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\AccountDelete;
use Atomjoy\Apilogin\Events\AccountDeleteError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\UpdateAccountDeleteRequest;
use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountDeleteController extends Controller
{
	/**
	 * Update the specified resource in storage.
	 */
	public function update(UpdateAccountDeleteRequest $request)
	{
		$valid = $request->validated();

		try {
			$user = Auth::user();

			if (Hash::check($valid['current_password'], $user->password)) {

				$user->forceFill([
					'email' => '#del#' . time() . '#' . str_replace('@', '#', $user->email),
					'password' => '#del#' . microtime(),
					'remember_token' => null,
				])->save();

				$profile = Profile::where('user_id', $user->id)->first();

				if ($profile instanceof Profile) {
					$profile->forceFill([
						'username' => '#del#' . time() . '#' . $profile->username,
						'deleted_at' => now()
					])->save();
				}

				AccountDelete::dispatch($user);

				if (config('apilogin.account_delete_logout', true)) {
					Auth::logout();
					$request->session()->invalidate();
					$request->session()->regenerateToken();
				}

				return response()->json([
					'message' => __("apilogin.account.delete.success"),
				], 200);
			}
		} catch (Exception $e) {
			report($e);
			AccountDeleteError::dispatch($valid);
			throw new JsonException(__("apilogin.account.delete.error"), 422);
		}
	}
}
