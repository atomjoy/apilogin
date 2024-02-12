<?php

// User

use Atomjoy\Apilogin\Http\Controllers\AddressController;
use Atomjoy\Apilogin\Http\Controllers\ProfileController;
use Atomjoy\Apilogin\Http\Controllers\ActivateController;
use Atomjoy\Apilogin\Http\Controllers\CsrfController;
use Atomjoy\Apilogin\Http\Controllers\LocaleController;
use Atomjoy\Apilogin\Http\Controllers\LoggedController;
use Atomjoy\Apilogin\Http\Controllers\LoginController;
use Atomjoy\Apilogin\Http\Controllers\LogoutController;
use Atomjoy\Apilogin\Http\Controllers\RegisterController;
use Atomjoy\Apilogin\Http\Controllers\PasswordResetController;
use Atomjoy\Apilogin\Http\Controllers\PasswordChangeController;
use Atomjoy\Apilogin\Http\Controllers\PasswordConfirmController;
use Atomjoy\Apilogin\Http\Controllers\EmailChangeController;
use Atomjoy\Apilogin\Http\Controllers\EmailChangeConfirmController;
use Atomjoy\Apilogin\Http\Controllers\UploadAvatarController;
use Atomjoy\Apilogin\Http\Controllers\AccountDeleteController;
use Atomjoy\Apilogin\Http\Controllers\NotificationsController;
use Atomjoy\Apilogin\Http\Controllers\F2aController;
use Illuminate\Support\Facades\Route;

// Show s3 image url <img src="img/url?path=image/path/here.webp" />
Route::get('img/url', [UploadAvatarController::class, 'getUrl'])->name('img.url');

// User routes
Route::prefix('web/api')->name('web.api.')->middleware([
	'web', 'apilogin'
])->group(function () {
	// Public routes
	Route::get('/activate/{id}/{code}', [ActivateController::class, 'index'])->name('activate');
	Route::post('/login', [LoginController::class, 'index'])->name('login');
	Route::post('/register', [RegisterController::class, 'index'])->name('register');
	Route::post('/password', [PasswordResetController::class, 'index'])->name('password');
	Route::get('/logout', [LogoutController::class, 'index'])->name('logout');
	Route::get('/logged', [LoggedController::class, 'index'])->name('logged');
	Route::get('/csrf', [CsrfController::class, 'index'])->name('csrf');
	Route::get('/locale/{locale}', [LocaleController::class, 'index'])->name('locale');
	Route::post('/f2a', [F2aController::class, 'index'])->name('f2a');

	// Private routes
	Route::middleware(['auth'])->group(function () {
		// 2FA auth on/off
		Route::post('/f2a/enable', [F2aController::class, 'enable'])->name('f2a.enable');
		Route::post('/f2a/disable', [F2aController::class, 'disable'])->name('f2a.disable');
		// Account
		Route::post('/password/confirm', [PasswordConfirmController::class, 'index'])->name('confirm');
		Route::post('/password/change', [PasswordChangeController::class, 'index'])->name('change');
		Route::post('/change/email', [EmailChangeController::class, 'index'])->name('change.email');
		Route::get('/change/email/{id}/{code}', [EmailChangeConfirmController::class, 'index'])->name('change.email.confirm');
		// Resource
		Route::singleton('address', AddressController::class, ['except' => ['edit']]);
		Route::singleton('profile', ProfileController::class, ['except' => ['edit']]);
		Route::singleton('account/delete', AccountDeleteController::class, ['except' => ['edit', 'show']]);
		// Notifications
		Route::get('/notifications/page/{page}', [NotificationsController::class, 'index'])->name('notifications.page');
		Route::get('/notifications/toggle/{id}', [NotificationsController::class, 'toggle'])->name('notifications.toggle');
		Route::get('/notifications/delete/{id}', [NotificationsController::class, 'delete'])->name('notifications.delete');
		Route::get('/notifications/readall', [NotificationsController::class, 'readall'])->name('notifications.readall');
		// Avatar
		Route::post('/upload/avatar', [UploadAvatarController::class, 'index'])->name('upload.avatar');
		Route::post('/remove/avatar', [UploadAvatarController::class, 'remove'])->name('remove.avatar');
		// Show image
		Route::get('/show/avatar', [UploadAvatarController::class, 'show'])->name('show.avatar')->withoutMiddleware('apilogin');
	});
});

// Admin routes
require('admin.php');
