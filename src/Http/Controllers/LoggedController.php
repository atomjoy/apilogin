<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\LoggedUser;
use Atomjoy\Apilogin\Events\LoggedUserError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoggedController extends Controller
{
	function index(Request $request)
	{
		if (Auth::check()) {
			LoggedUser::dispatch(Auth::user());

			return response()->json([
				'message' => __('apilogin.logged.authenticated'),
				'user' => Auth::user()->fresh([
					'profile', 'address', 'roles', 'permissions'
				]),
			], 200);
		} else {
			LoggedUserError::dispatch();

			return response()->json([
				'message' => __('apilogin.logged.unauthenticated'),
				'user' => null
			], 422);
		}
	}
}
