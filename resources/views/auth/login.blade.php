<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status @class(['mb-4']) :status="session('status')" />
    <div @class(['auth-main-top'])>
</div>
<div @class(['auth-card'])>
    <h2>Welcome back</h2>
    <form @class(['auth-form']) method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div @class(['field'])><label @class(['field-label']) for="email">Email</label>
            <div @class(['input-icon'])><span @class(['ico'])><svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m3 7 9 6 9-6" />
                    </svg></span><input id="email" @class(['input']) type="email" name="email" value="{{ old('email') }}" autofocus placeholder="you@company.com"></div>
                    <x-input-error :messages="$errors->get('email')" @class(['mt-2']) />
        </div>
        <div @class(['field'])>
            <div @class(['field-row'])><label @class(['field-label']) for="password">Password</label> 
                @if (Route::has('admin.password.request'))
                <a href="{{ route('admin.password.request') }}">Forgot?</a>
                @endif
            </div>
            <div @class(['input-icon'])><span @class(['ico'])><svg viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg></span><input id="password" @class(['input']) type="password" name="password" placeholder="••••••••"
                    autocomplete="current-password"></div>
                    <x-input-error :messages="$errors->get('password')" @class(['mt-2']) />
        </div>
        <label @class(['check'])><input type="checkbox" checked="checked" name="remember"> <span @class(['box'])></span> Keep me signed in for 30 days</label> 
            <button @class(['btn', 'btn--primary', 'auth-submit']) type="submit">Signin <svg viewBox="0 0 24 24">
                <path d="M5 12h14M13 5l7 7-7 7" />
            </svg></button>
    </form>
</div>
<div @class(['auth-main-bottom'])>By signing in you agree to our <a href="#">Terms</a> and <a href="#">Privacy
        Policy</a>.</div>
</x-guest-layout>
