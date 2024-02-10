<?php

use Illuminate\Support\Facades\Route;

use Atomjoy\Apilogin\Http\Controllers\Admin\F2aController as AdminF2aController;
use Atomjoy\Apilogin\Http\Controllers\Admin\LoginController as AdminLoginController;
use Atomjoy\Apilogin\Http\Controllers\Admin\PasswordResetController as AdminPasswordResetController;
use Atomjoy\Apilogin\Http\Controllers\Admin\LoggedController as AdminLoggedController;

// Admin panel
Route::prefix('web/api/admin')->name('web.api.admin')->middleware([
	'web', 'apilogin'
])->group(function () {
	// Public routes
	Route::post('/login', [AdminLoginController::class, 'index'])->name('login');
	Route::post('/password', [AdminPasswordResetController::class, 'index'])->name('password');
	Route::get('/logged', [AdminLoggedController::class, 'index'])->name('logged');
	Route::post('/f2a', [AdminF2aController::class, 'index'])->name('f2a');

	// Private routes (guard admin)
	Route::middleware([
		'auth:admin', 'apilogin_is_admin',
		'role:' . config(
			'apilogin.allowed_admin_roles',
			'super_admin|admin|worker'
		) . ',admin'
	])->group(function () {
		// 2FA auth on/off
		Route::post('/f2a/enable', [AdminF2aController::class, 'enable'])->name('f2a.enable');
		Route::post('/f2a/disable', [AdminF2aController::class, 'disable'])->name('f2a.disable');

		// Admin panel routes
		// ...

		// Test route
		Route::get('/test', function () {
			return response()->json([
				'message' => 'Authenticated.'
			]);
		})->middleware('throttle:20,1'); // 20/min
	});
});
