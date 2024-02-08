<?php

namespace Atomjoy\Apilogin\Http\Controllers\Admin;

use Exception;
use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\PasswordReset;
use Atomjoy\Apilogin\Events\PasswordResetError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\Admin\PasswordResetRequest;
use Atomjoy\Apilogin\Models\Admin;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
	function index(PasswordResetRequest $request)
	{
		$valid = $request->validated();
		$password = uniqid('Wow8#');
		$user = null;

		try {
			$user = Admin::where('email', $valid['email'])->first();

			if (!$user instanceof Admin) {
				throw new Exception(__("apilogin.reset.password.error.user"), 422);
			}
		} catch (Exception $e) {
			report($e);
			PasswordResetError::dispatch($valid);
			throw new JsonException(__("apilogin.reset.password.invalid"), 422);
		}

		try {
			$request->testDatabase();

			if (empty($user->email_verified_at)) {
				$user->email_verified_at = now();
			}
			$user->password = Hash::make($password);
			$user->save();

			PasswordReset::dispatch($user, $password);

			return response()->json([
				'message' => __("apilogin.reset.password.success")
			], 200);
		} catch (Exception $e) {
			report($e);
			PasswordResetError::dispatch($valid);
			throw new JsonException(__("apilogin.reset.password.error"), 422);
		}
	}
}
