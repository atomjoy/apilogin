<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\PasswordChangeRequest;
use Atomjoy\Apilogin\Events\PasswordChange;
use Atomjoy\Apilogin\Events\PasswordChangeError;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class PasswordChangeController extends Controller
{
	function index(PasswordChangeRequest $request)
	{
		$valid = $request->validated();

		if (Auth::check()) {
			$user = Auth::user();

			if (Hash::check($valid['password_current'], $user->password)) {
				try {
					// Tests error
					$request->testDatabase();

					$user->update([
						'password' => Hash::make($valid['password']),
						'ip' => $request->ip()
					]);

					PasswordChange::dispatch($user);
					return response()->json([
						'message' => __('apilogin.change.success')
					], 200);
				} catch (Exception $e) {
					report($e);
					PasswordChangeError::dispatch($valid);
					throw new JsonException(__('apilogin.change.error'), 422);
				}
			} else {
				PasswordChangeError::dispatch($valid);
				throw new JsonException(__('apilogin.change.invalid.current.password'), 422);
			}
		} else {
			PasswordChangeError::dispatch($valid);
			throw new JsonException(__('apilogin.change.unauthenticated.'), 422);
		}
	}
}
