<?php

use Illuminate\Support\Facades\Route;

use Atomjoy\Apilogin\Http\Controllers\Admin\F2aController as AdminF2aController;
use Atomjoy\Apilogin\Http\Controllers\Admin\LoginController as AdminLoginController;
use Atomjoy\Apilogin\Http\Controllers\Admin\PasswordResetController as AdminPasswordResetController;
use Atomjoy\Apilogin\Http\Controllers\Admin\LoggedController as AdminLoggedController;
use Atomjoy\Apilogin\Http\Controllers\Admin\LogoutController as AdminLogoutController;
use Atomjoy\Apilogin\Http\Controllers\Admin\PasswordChangeController as AdminPasswordChangeController;
use Atomjoy\Apilogin\Http\Controllers\Admin\UploadAvatarController as AdminUploadAvatarController;

// Show s3 image url ?path=file/path/here.webp
Route::get('web/api/admin/image/url', [AdminUploadAvatarController::class, 'showUrl'])->name('web.api.admin.image.url');

// Admin panel
Route::prefix('web/api/admin')->name('web.api.admin')->middleware([
	'web', 'apilogin'
])->group(function () {
	// Public routes
	Route::post('/login', [AdminLoginController::class, 'index'])->name('login');
	Route::post('/password', [AdminPasswordResetController::class, 'index'])->name('password');
	Route::get('/logout', [AdminLogoutController::class, 'index'])->name('logout');
	Route::get('/logged', [AdminLoggedController::class, 'index'])->name('logged');
	Route::post('/f2a', [AdminF2aController::class, 'index'])->name('f2a');

	// Private admin, worker routes (guard admin)
	Route::middleware([
		'auth:admin', 'apilogin_is_admin',
		'role:' . config(
			'apilogin.allowed_worker_roles',
			'super_admin|admin|worker'
		) . ',admin'
	])->group(function () {
		// Admin, worker routes
		Route::post('/password/change', [AdminPasswordChangeController::class, 'index'])->name('change');
		Route::post('/f2a/enable', [AdminF2aController::class, 'enable'])->name('f2a.enable');
		Route::post('/f2a/disable', [AdminF2aController::class, 'disable'])->name('f2a.disable');
		Route::post('/upload/avatar', [AdminUploadAvatarController::class, 'index'])->name('upload.avatar');
		Route::post('/remove/avatar', [AdminUploadAvatarController::class, 'remove'])->name('remove.avatar');

		Route::get('/test', function () {
			return response()->json([
				'message' => 'Authenticated.'
			]);
		})->middleware('throttle:20,1'); // 20/min
	});

	// Private admin routes (guard admin)
	Route::middleware([
		'auth:admin', 'apilogin_is_admin',
		'role:' . config(
			'apilogin.allowed_admin_roles',
			'super_admin|admin'
		) . ',admin'
	])->group(function () {
		// Admin only routes
		Route::get('/test/admin', function () {
			return response()->json([
				'message' => 'Authenticated.'
			]);
		})->middleware('throttle:20,1'); // 20/min
	});
});
