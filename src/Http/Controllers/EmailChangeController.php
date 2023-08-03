<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\User;
use Atomjoy\Apilogin\Events\EmailChange;
use Atomjoy\Apilogin\Events\EmailChangeError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\EmailChangeRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EmailChangeController extends Controller
{
	function index(EmailChangeRequest $request)
	{
		$valid = $request->validated();
		$code = md5(uniqid() . microtime());
		$user = null;

		try {
			$request->testDatabase();

			$user = Auth::user();

			if (!$user instanceof User) {
				throw new Exception('Invalid change email user.');
			}

			Cache::put(
				$this->getCacheKey(),
				$user->id . '|' . $valid['email'] . '|' . $code,
				now()->addHour()
			);

			EmailChange::dispatch($user, $code);

			return response()->json([
				'message' => __("apilogin.email.change.success")
			], 200);
		} catch (Exception $e) {
			report($e);
			EmailChangeError::dispatch($valid);
			throw new JsonException(__("apilogin.email.change.error"), 422);
		}
	}

	function getCacheKey()
	{
		return 'emailchange_' . md5(Auth::user()->id);
	}
}
