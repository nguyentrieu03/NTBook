<x-guest-layout>
    <div @class(['auth-main-top'])>
        <div class="switch-link">Remembered it? <a href="{{ route('admin.login') }}">Sign in</a></div>
    </div>
    <div @class(['auth-card'])>
        <h2>Set new password</h2>
        <p class="sub">Choose a strong password to secure your account.</p>

        <form @class(['auth-form']) method="POST" action="{{ route('admin.password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div @class(['field'])>
                <label @class(['field-label']) for="email">Email</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                    </span>
                    <input id="email" @class(['input']) type="email" name="email" value="{{ old('email', $request->email) }}" autofocus autocomplete="username" placeholder="you@company.com">
                </div>
                <x-input-error :messages="$errors->get('email')" @class(['mt-2']) />
            </div>

            <div @class(['field'])>
                <label @class(['field-label']) for="password">New password</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input id="password" @class(['input']) type="password" name="password" placeholder="••••••••" autocomplete="new-password">
                </div>
                <x-input-error :messages="$errors->get('password')" @class(['mt-2']) />
            </div>

            <div @class(['field'])>
                <label @class(['field-label']) for="password_confirmation">Confirm password</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input id="password_confirmation" @class(['input']) type="password" name="password_confirmation" placeholder="••••••••" autocomplete="new-password">
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" @class(['mt-2']) />
            </div>

            <button @class(['btn', 'btn--primary', 'auth-submit']) type="submit">
                Reset password
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14M13 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>
    <div @class(['auth-main-bottom'])>By signing in you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.</div>
</x-guest-layout>
