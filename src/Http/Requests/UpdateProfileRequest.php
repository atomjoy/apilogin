<?php

namespace Atomjoy\Apilogin\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return Auth::check(); // logged only
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array
	{
		return [
			'username' => [
				'required',
				'string',
				'min:6',
				'max:30',
				'regex:/^[a-zA-Z0-9]([._](?![._])|[a-zA-Z0-9]){6,20}[a-zA-Z0-9]$/',
				'unique:profiles,username,' . Auth::user()->profile?->id ?? null,
				// Rule::unique('profiles')->ignore(Auth::user()->profile?->id ?? null),
			],
			'name' => [
				'required', 'min:3', 'max:50',
			],
			'location' => 'sometimes|max:50',
			'bio' => 'sometimes|max:500',
			'avatar' => 'sometimes|max:254',
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
		$this->merge([
			collect(request()->json()->all())->only([
				'username', 'name', 'location', 'bio', 'avatar'
			])->toArray()
		]);
	}
}
