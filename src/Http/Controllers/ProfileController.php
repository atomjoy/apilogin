<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\ProfileUser;
use Atomjoy\Apilogin\Events\ProfileUserError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\UpdateProfileRequest;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
	/**
	 * Display the specified resource.
	 */
	public function show()
	{
		$user = User::with('profile')->findOrFail(Auth::id());

		return response()->json([
			'profile' => $user->profile
		], 200);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(UpdateProfileRequest $request)
	{
		$valid = $request->validated();

		try {
			$user = User::with('profile')->findOrFail(Auth::id());

			$user->profile()->updateOrCreate([
				'user_id' => $user->id
			], $valid);

			$user->forceFill([
				'name' => $valid['name']
			])->save();

			ProfileUser::dispatch($user);

			return response()->json([
				'message' => __("apilogin.profile.success"),
				'profile' => $user->fresh(['profile'])->profile
			], 200);
		} catch (Exception $e) {
			report($e);
			ProfileUserError::dispatch($valid);
			throw new JsonException(__("apilogin.profile.error"), 422);
		}
	}
}
