<?php

namespace Atomjoy\Apilogin\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UploadAvatarRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return Auth::check(); // Allow logged
	}

	public function rules()
	{
		return [
			'avatar' => [
				'required',
				'mimes:webp',
				Rule::dimensions()
					->minWidth(config('apilogin.avatar_min_pixels', 64))
					->minHeight(config('apilogin.avatar_min_pixels', 64)),
				Rule::dimensions()
					->maxWidth(config('apilogin.avatar_max_pixels', 1025))
					->maxHeight(config('apilogin.avatar_max_pixels', 1025)),
				Rule::file()->types(['webp'])
					->max(config('apilogin.max_upload_size_mb', 5) * 1024),
			]
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new ValidationException($validator, response()->json([
			'message' => $validator->errors()->first()
		], 422));
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only(['avatar'])->toArray()
		);
	}
}
