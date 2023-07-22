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

		LoginUser::dispatch(Auth::user());

		return response()->json([
			'message' => __('apilogin.login.authenticated'),
			'user' => Auth::user(),
		], 200);
	}
}
