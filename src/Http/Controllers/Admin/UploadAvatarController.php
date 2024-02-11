<?php

namespace Atomjoy\Apilogin\Http\Controllers\Admin;

use Exception;
use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\UploadAvatar;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\UploadAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Response;

class UploadAvatarController extends Controller
{
	protected $disk = 's3';

	function index(UploadAvatarRequest $request)
	{
		try {
			Auth::shouldUse('admin');

			$user =  Auth::user();

			$filename = $user->id . '.webp';

			// $path = $request->file('avatar')->storeAs('avatars/admin', $filename, 'public');

			$path = Storage::disk($this->disk)
				->putFileAs(
					'avatars/admin',
					$request->file('avatar'),
					$filename
				);

			$user->avatar = $path;
			$user->save();

			UploadAvatar::dispatch($user, $path);

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
			Auth::shouldUse('admin');

			$filename = 'avatars/admin/' . Auth::id() . '.webp';

			if (Storage::disk($this->disk)->exists($filename)) {
				Storage::disk($this->disk)->delete($filename);
				Auth::user()->update(['avatar' => null]);
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
			Auth::shouldUse('admin');

			$id = Auth::id() ?? 'error';

			$filename = '/avatars/admin/' . $id . '.webp';

			$exists = Storage::disk($this->disk)->exists($filename);

			if ($exists) {
				$mime = Storage::disk($this->disk)->mimeType($filename);

				$content = Storage::disk($this->disk)->get($filename);

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

	/**
	 *	Show s3 file url.
	 */
	public function showUrl()
	{
		$path = stripslashes(request('path'));

		if (Storage::disk($this->disk)->exists($path)) {
			return Storage::disk($this->disk)->url($path);
			// return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes(60));
		}

		return config(
			'apilogin.error_file_placeholder',
			'https://placehold.co/256x256?font=roboto&text=Invalid\nImage'
		);
		// 'https://picsum.photos/256/256.webp?grayscale&blur=2'
	}
}
