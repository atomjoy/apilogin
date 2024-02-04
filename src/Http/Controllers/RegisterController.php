<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\RegisterUser;
use Atomjoy\Apilogin\Events\RegisterUserError;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Atomjoy\Apilogin\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
	function index(RegisterRequest $request)
	{
		$valid = $request->validated();

		try {
			$request->testDatabase();

			$user = User::create([
				'name' => $valid['name'],
				'email' => $valid['email'],
				'password' => Hash::make($valid['password']),
			]);

			$user->forceFill([
				'remember_token' => $this->createPasswordToken(),
			])->save();

			$user->profile()->updateOrCreate([
				'user_id' => $user->id
			], [
				'name' => $valid['name'],
				'username' => uniqid('user.'),
			]);

			$user->address()->updateOrCreate([
				'user_id' => $user->id
			], []);

			$user->assignRole('User');

			RegisterUser::dispatch($user);

			return response()->json([
				'message' => __("apilogin.register.success"),
				'created' => true
			], 201);
		} catch (Exception $e) {
			report($e);
			RegisterUserError::dispatch($valid);
			throw new JsonException(__("apilogin.register.error"), 422);
		}
	}

	public function createPasswordToken()
	{
		return substr(uniqid(md5(microtime())), 0, rand(16, 21));
	}
}
