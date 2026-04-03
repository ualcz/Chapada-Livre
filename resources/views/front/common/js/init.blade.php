@php
	$authUser = auth()->check() ? auth()->user() : null;
	$isLoggedUser = !empty($authUser) ? 'true' : 'false';
	$isLoggedAdmin = doesUserHavePermission($authUser, \App\Models\Permission::getStaffPermissions()) ? 'true' : 'false';
@endphp
<script>
	{{-- Init. Root Vars --}}
	var siteUrl = '{{ url('/') }}';
	var languageCode = '{{ config('app.locale') }}';
	var isLoggedUser = {{ $isLoggedUser }};
	var isLoggedAdmin = {{ $isLoggedAdmin }};
	var isAdminPanel = {{ isAdminPanelRoute() ? 'true' : 'false' }};
	var demoMode = {{ isDemoDomain() ? 'true' : 'false' }};
	var demoMessage = '{{ addcslashes(trans('global.demo_mode_message'), "'") }}';
	
	{{-- Cookie Parameters --}}
	var cookieParams = {
		expires: {{ (int)config('settings.other.cookie_expiration') }},
		path: "{{ config('session.path', '/') }}",
		domain: "{{ !empty(config('session.domain')) ? config('session.domain') : getCookieDomain() }}", {{-- Need to be removed to solve some issues --}}
		secure: {{ config('session.secure') ? 'true' : 'false' }},
		sameSite: "{{ config('session.same_site') }}"
	};
	
	{{-- Init. Translation Vars --}}
	var langLayout = {
		loading: "{{ trans('global.loading_wd') }}",
		errorFound: "{{ trans('global.error_found') }}",
		refresh: "{{ trans('global.refresh') }}",
		confirm: {
			button: {
				yes: "{{ trans('global.confirm_button_yes') }}",
				no: "{{ trans('global.confirm_button_no') }}",
				ok: "{{ trans('global.confirm_button_ok') }}",
				cancel: "{{ trans('global.confirm_button_cancel') }}"
			},
			message: {
				question: "{{ trans('global.confirm_message_question') }}",
				success: "{{ trans('global.confirm_message_success') }}",
				error: "{{ trans('global.confirm_message_error') }}",
				errorAbort: "{{ trans('global.confirm_message_error_abort') }}",
				cancel: "{{ trans('global.confirm_message_cancel') }}"
			}
		},
		waitingDialog: {
			loading: {
				title: "{{ trans('global.waitingDialog_loading_title') }}",
				text: "{{ trans('global.waitingDialog_loading_text') }}"
			},
			complete: {
				title: "{{ trans('global.waitingDialog_complete_title') }}",
				text: "{{ trans('global.waitingDialog_complete_text') }}"
			}
		},
		hideMaxListItems: {
			moreText: "{{ trans('global.View More') }}",
			lessText: "{{ trans('global.View Less') }}"
		},
		select2: {
			errorLoading: function () {
				return "{!! trans('global.The results could not be loaded') !!}"
			},
			inputTooLong: function (e) {
				let t = e.input.length - e.maximum, n = "{!! trans('global.Please delete X character') !!}";
				n = n.replace('{charsLength}', t.toString());
				
				return t != 1 && (n += 's'), n
			},
			inputTooShort: function (e) {
				let t = e.minimum - e.input.length, n = "{!! trans('global.Please enter X or more characters') !!}";
				n = n.replace('{minCharsLength}', t.toString());
				
				return n
			},
			loadingMore: function () {
				return "{!! trans('global.Loading more results') !!}"
			},
			maximumSelected: function (e) {
				let maxItems = e.maximum;
				let t = "{!! trans('global.You can only select N item') !!}";
				t = t.replace('{maxItems}', maxItems.toString());
				
				return maxItems != 1 && (t += 's'), t
			},
			noResults: function () {
				return "{!! trans('global.no_results') !!}"
			},
			searching: function () {
				return "{!! trans('global.Searching') !!}"
			}
		},
		themePreference: {
			light: "{{ trans('global.theme_preference_light') }}",
			dark: "{{ trans('global.theme_preference_dark') }}",
			system: "{{ trans('global.theme_preference_system') }}",
			success: "{{ trans('global.theme_preference_success') }}",
			empty: "{{ trans('global.theme_preference_empty') }}",
			error: "{{ trans('global.theme_preference_error') }}",
		},
		location: {
			area: "{{ trans('global.area') }}"
		},
		autoComplete: {
			searchCities: "{{ trans('global.search_cities') }}",
			enterMinimumChars: (threshold) => `{{ trans('global.enter_minimum_chars') }}`,
			noResultsFor: (query) => {
				query = `<strong>${query}</strong>`;
				return `{{ trans('global.no_results_for') }}`
			},
		},
		payment: {
			submitBtnLabel: {
				pay: "{{ trans('global.Pay') }}",
				submit: "{{ trans('global.submit') }}",
			},
		},
		unsavedFormGuard: {
			error_form_not_found: "{{ trans('global.unsaved_form_guard.error_form_not_found') }}",
			unsaved_changes_prompt: "{{ trans('global.unsaved_form_guard.unsaved_changes_prompt') }}",
		},
	};
	
	const formValidateOptions = {
		formErrorMessage: "{{ trans('global.formValidation.formErrorMessage') }}",
		defaultErrors: {
			required: "{{ trans('global.formValidation.defaultErrors.required') }}",
			validator: "{{ trans('global.formValidation.defaultErrors.validator') }}",
		},
		errors: {
			alphanumeric: "{{ trans('global.formValidation.errors.alphanumeric') }}",
			numeric: "{{ trans('global.formValidation.errors.numeric') }}",
			email: "{{ trans('global.formValidation.errors.email') }}",
			url: "{{ trans('global.formValidation.errors.url') }}",
			username: "{{ trans('global.formValidation.errors.username') }}",
			password: "{{ trans('global.formValidation.errors.password') }}",
			date: "{{ trans('global.formValidation.errors.date') }}",
			time: "{{ trans('global.formValidation.errors.time') }}",
			cardExpiry: "{{ trans('global.formValidation.errors.cardExpiry') }}",
			cardCvc: "{{ trans('global.formValidation.errors.cardCvc') }}",
		},
	};
</script>
