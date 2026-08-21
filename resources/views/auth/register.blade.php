<x-guest-layout>
    <div @class(['auth-main-top'])>
        <div class="switch-link">{{ __('Already registered?') }} <a href="{{ route('admin.login') }}">{{ __('Sign in') }}</a></div>
    </div>

    <div @class(['auth-card'])>
        <h2>{{ __('Create your account') }}</h2>

        <form @class(['auth-form']) method="POST" action="{{ route('admin.register') }}">
            @csrf

            <div @class(['field'])>
                <label @class(['field-label']) for="name">{{ __('Full name') }}</label>
                <input id="name" @class(['input']) type="text" name="name" value="{{ old('name') }}"
                    placeholder="Jane Doe" required autofocus autocomplete="name">
                <x-input-error :messages="$errors->get('name')" @class(['mt-2']) />
            </div>

            <div @class(['field'])>
                <label @class(['field-label']) for="email">{{ __('Email') }}</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                    </span>
                    <input id="email" @class(['input']) type="email" name="email" value="{{ old('email') }}"
                        placeholder="you@company.com" required autocomplete="username">
                </div>
                <x-input-error :messages="$errors->get('email')" @class(['mt-2']) />
            </div>

            <div @class(['field'])>
                <label @class(['field-label']) for="password">{{ __('Password') }}</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input id="password" @class(['input']) type="password" name="password"
                        placeholder="••••••••" required autocomplete="new-password">
                </div>
                <x-input-error :messages="$errors->get('password')" @class(['mt-2']) />
            </div>

            <div @class(['field'])>
                <label @class(['field-label']) for="password_confirmation">{{ __('Confirm Password') }}</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input id="password_confirmation" @class(['input']) type="password"
                        name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" @class(['mt-2']) />
            </div>

            <button @class(['btn', 'btn--primary', 'auth-submit']) type="submit">
                {{ __('Register') }}
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14M13 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>

    <div @class(['auth-main-bottom'])>
        {{ __('By signing up you agree to our') }}
        <a href="#">{{ __('Terms') }}</a> {{ __('and') }} <a href="#">{{ __('Privacy Policy') }}</a>.
    </div>
</x-guest-layout>
