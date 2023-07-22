<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\PasswordConfirm;
use Atomjoy\Apilogin\Events\PasswordConfirmError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\PasswordConfirmRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordConfirmController extends Controller
{
	function index(PasswordConfirmRequest $request)
	{
		$valid = $request->validated();

		if (Auth::check()) {
			$user = Auth::user();

			// dd($valid['password']);

			if (Hash::check($valid['password'], $user->password)) {
				PasswordConfirm::dispatch($user);
				return response()->json(['message' => __('apilogin.confirm.confirmed')], 200);
			} else {
				// PasswordConfirmError::dispatch($valid);
				throw new JsonException(__('apilogin.confirm.invalid.current.password'), 422);
			}
		} else {
			PasswordConfirmError::dispatch($valid);
			throw new JsonException(__('apilogin.confirm.unauthenticated'), 422);
		}
	}
}
