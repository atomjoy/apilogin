<?php

namespace Atomjoy\Apilogin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\LoginUser;
use Atomjoy\Apilogin\Http\Requests\Admin\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
	function __construct()
	{
		Auth::shouldUse('admin'); // Default guard
	}

	function index(LoginRequest $request)
	{
		$request->validated();

		$request->authenticate();

		$request->session()->regenerate();

		if (Auth::check()) {
			LoginUser::dispatch(Auth::user());

			if (Auth::user()->f2a == 1) {
				return response()->json([
					'message' => __('apilogin.login.authenticated'),
					'user' => null,
					'redirect' => '/admin/login/f2a/' . $request->f2a(),
				], 200);
			}

			return response()->json([
				'message' => __('apilogin.login.authenticated'),
				'user' => Auth::user()->fresh([
					'roles', 'roles_permissions',
				]),
				'redirect' => null,
			], 200);
		} else {
			return response()->json([
				'message' => __('apilogin.login.unauthenticated'),
				'user' => null,
				'redirect' => null,
			], 422);
		}
	}
}
