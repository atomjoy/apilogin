<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class CsrfController extends Controller
{
	function index(Request $request)
	{
		$request->session()->regenerateToken();

		session(['apilogin_counter' => session('apilogin_counter') + 1]);

		Event::dispatch('apilogin.csrf');
		return response()->json([
			'message' => trans('Csrf token created.'),
			'counter' => session('apilogin_counter'),
		]);
	}
}
