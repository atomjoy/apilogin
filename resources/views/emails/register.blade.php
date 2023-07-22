<x-proton::email title="{{ __('apilogin.email.register.subject') }}" locale="{{ app()->getlocale() }}">
	<x-slot:style>
		<style>
			.proton-table tr td {
				padding: 0px 40px;
			}

			.apilogin-logo {
				margin: 0px auto;
				margin-bottom: 20px;
				max-height: 100px;
				max-width: 50%;
			}
		</style>
	</x-slot:style>

	<x-proton::row>
		<x-proton::margin />

		<center>
			<img src="{{ config('apilogin.register_image_url', 'https://raw.githubusercontent.com/atomjoy/proton/main/public/proton-default.png') }}" alt="Image">
		</center>

		<h2>@lang('apilogin.email.register.welcome') {{ $user?->name ?? '' }}!</h2>
		<p>@lang('apilogin.email.register.message')</p>
	</x-proton::row>

	<x-proton::row>
		<x-proton::button url="{{ request()->getSchemeAndHttpHost() }}/activate/{{ $user?->id ?? '0' }}/{{ $code ?? 'invalidcode' }}?locale={{ app()->getLocale() }}">
			@lang('apilogin.email.register.button')
		</x-proton::button>
	</x-proton::row>

	<x-proton::row>
		<h3>@lang('apilogin.email.regards')</h3>
		<strong>{{ $user?->name ?? '' }}</strong>
		<p>@lang('apilogin.email.regards_text')</p>
	</x-proton::row>

	<x-proton::row>
		<x-proton::divider />

		<center>
			<span class="proton-rights"> © @lang('apilogin.email.rights') </span>
		</center>

		<br />

		<x-proton::flex>
			<x-proton::social name="twitter" />
			<x-proton::social name="tiktok" />
			<x-proton::social name="facebook" />
			<x-proton::social name="instagram" />
		</x-proton::flex>

		<x-proton::margin />
	</x-proton::row>
</x-proton::email>