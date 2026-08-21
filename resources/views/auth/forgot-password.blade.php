<x-guest-layout>
    <div @class(['auth-main-top'])>
        <div class="switch-link">Remembered it? <a href="{{ route('admin.login') }}">Sign in</a></div>
    </div>
    <div @class(['auth-card'])>
        <h2>Reset password</h2>
        <p class="sub">Enter your email address and we'll send you a link to reset your password.</p>

        @if (session('status'))
            <x-auth-session-status :status="session('status')" />
        @elseif ($errors->has('email'))
            <x-auth-session-status :status="$errors->first('email')" variant="error" />
        @endif

        <form @class(['auth-form']) method="POST" action="{{ route('admin.password.email') }}">
            @csrf

            <div @class(['field'])>
                <label @class(['field-label']) for="email">Email</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                    </span>
                    <input id="email" @class(['input']) type="email" name="email" value="{{ old('email') }}" autofocus placeholder="you@company.com">
                </div>
            </div>

            <button @class(['btn', 'btn--primary', 'auth-submit']) type="submit">
                Send reset link
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14M13 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>
    <div @class(['auth-main-bottom'])>By signing in you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.</div>
</x-guest-layout>
