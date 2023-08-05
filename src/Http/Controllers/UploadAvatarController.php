<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use Exception;
use App\Models\User;
use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\UploadAvatar;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\UploadAvatarRequest;
use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadAvatarController extends Controller
{
	function index(UploadAvatarRequest $request)
	{
		try {
			$user =  Auth::user();

			$filename = $user->id . '.webp';

			// $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');

			$path = Storage::disk('public')
				->putFileAs(
					'avatars',
					$request->file('avatar'),
					$filename
				);

			$data = ['avatar' => $path];

			$profile = $user->fresh(['profile'])->profile;

			if ($profile == null) {
				$data = [
					'avatar' => $path,
					'name' => $user->name ?? 'Czereśniak',
					'username' => uniqid('user.'),
				];
			}

			Profile::updateOrCreate([
				'user_id' => $user->id
			], $data);

			UploadAvatar::dispatch(Auth::user(), $path);

			return response()->json([
				'message' => __('apilogin.upload.avatar.success'),
				'avatar' => $path,
			], 200);
		} catch (Exception $e) {
			report($e);
			throw new JsonException(__('apilogin.upload.avatar.error'), 422);
		}
	}

	function remove(Request $request)
	{
		try {

			$filename = 'avatars/' . Auth::id() . '.webp';

			if (Storage::disk('public')->exists($filename)) {
				Storage::disk('public')->delete($filename);
				Auth::user()->profile()->update(['avatar' => null]);
			}

			return response()->json([
				'message' => __('apilogin.remove.avatar.success'),
			], 200);
		} catch (Exception $e) {
			report($e);
			throw new JsonException(__('apilogin.remove.avatar.error'), 422);
		}
	}
}
