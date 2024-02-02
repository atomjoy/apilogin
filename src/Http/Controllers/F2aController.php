<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\F2aRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class F2aController extends Controller
{
	function index(F2aRequest $request)
	{
		$request->validated();

		$request->authenticate();

		if (Auth::check()) {
			return response()->json([
				'message' => __('apilogin.login.authenticated'),
				'user' => Auth::user()->fresh([
					'profile', 'address'
				])
			], 200);
		} else {
			throw new JsonException(__('apilogin.login.f2a_error'), 422);
		}
	}

	function enable()
	{
		if (Auth::check() && Hash::check(
			request('password_current'),
			Auth::user()->password
		)) {
			$user = Auth::user();
			$user->f2a = 1;
			$user->save();

			return response()->json([
				'message' => __('apilogin.updated'),
			], 200);
		} else {
			return response()->json([
				'message' => __('apilogin.confirm.invalid.current.password'),
			], 422);
		}
	}

	function disable()
	{
		if (Auth::check() && Hash::check(
			request('password_current'),
			Auth::user()->password
		)) {
			$user = Auth::user();
			$user->f2a = 0;
			$user->save();

			return response()->json([
				'message' => __('apilogin.updated'),
			], 200);
		} else {
			return response()->json([
				'message' => __('apilogin.confirm.invalid.current.password'),
			], 422);
		}
	}
}
