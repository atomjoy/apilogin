<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\UploadAvatar;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\UploadAvatarRequest;
use Atomjoy\Apilogin\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Response;

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


	public function show()
	{
		return $this->showAvatar();
	}

	/**
	 *	Show avatar only for logged in users.
	 */
	public function showAvatar($default_avatar = 'js/components/input/profil/avatar.png')
	{
		try {
			$id = Auth::id() ?? 'error';

			$filename = '/avatars/' . $id . '.webp';

			$exists = Storage::disk('public')->exists($filename);

			if ($exists) {
				$mime = Storage::disk('public')->mimeType($filename);

				$content = Storage::disk('public')->get($filename);

				$response = Response::make($content, 200);

				$response->header("Content-Type", $mime);

				return $response;
			} else {
				$default = resource_path($default_avatar);

				if (!file_exists($default)) {
					$default = fake()->image(
						null,
						128,
						128,
						null,
						true,
						true,
						'avatar',
						true,
						'png'
					);
				}

				return response(
					file_get_contents($default)
				)->header('Content-Type', 'image/png');
			}
		} catch (Exception $e) {
			report($e);
			throw new JsonException(__('apilogin.show.avatar.error'), 422);
		}
	}
}
