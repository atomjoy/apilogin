<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\LoginUser;
use Atomjoy\Apilogin\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
	function index(LoginRequest $request)
	{
		$request->validated();

		$request->authenticate();

		$request->session()->regenerate();

		if (Auth::check()) {
			LoginUser::dispatch(Auth::user());

			if (Auth::user()->f2a == 1) {
				return redirect('/login/f2a/' . $request->f2a());
			}

			return response()->json([
				'message' => __('apilogin.login.authenticated'),
				'user' => Auth::user()->fresh([
					'profile', 'address'
				])
			], 200);
		} else {
			return response()->json([
				'message' => __('apilogin.login.unauthenticated'),
				'user' => null
			], 422);
		}
	}
}
