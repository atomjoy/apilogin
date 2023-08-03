<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\User;
use Atomjoy\Apilogin\Events\EmailChangeConfirm;
use Atomjoy\Apilogin\Events\EmailChangeConfirmError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\EmailChangeConfirmRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class EmailChangeConfirmController extends Controller
{
	function index(EmailChangeConfirmRequest $request)
	{
		$valid = $request->validated();
		$user = null;

		try {
			$request->testDatabase();

			$user = Auth::user();

			if (!$user instanceof User) {
				throw new Exception('Invalid change email user.', 422);
			}

			if (!Cache::has($this->getCacheKey())) {
				throw new Exception("Invalid change email code.", 422);
			}

			$str = Cache::get($this->getCacheKey());

			list($id, $email, $code) = explode('|', $str);

			$validator = Validator::make(['email' => $email], [
				'email' => 'required|email:rfc,dns'
			]);

			if ($validator->fails()) {
				throw new Exception("Invalid change email address.", 422);
			}

			if ($code != $valid['code']) {
				throw new Exception("Invalid change email code.", 422);
			}

			if ($id != $user->id) {
				throw new Exception("Invalid change email user id.", 422);
			}

			$user->forceFill([
				'email' => $email
			])->save();

			EmailChangeConfirm::dispatch($user, $code);

			return response()->json([
				'message' => __("apilogin.email.change.confirm.success")
			], 200);
		} catch (Exception $e) {
			report($e);
			EmailChangeConfirmError::dispatch($valid);
			throw new JsonException(__("apilogin.email.change.confirm.error"), 422);
		}
	}

	function getCacheKey()
	{
		return 'emailchange_' . md5(Auth::user()->id);
	}
}
