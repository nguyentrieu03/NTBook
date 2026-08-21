<x-guest-layout>
    <div @class(['auth-main-top'])>
        <div class="switch-link">Not you? <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('confirm-logout-form').submit();">Sign out</a></div>
        <form id="confirm-logout-form" method="POST" action="{{ route('admin.logout') }}" style="display:none;">@csrf</form>
    </div>
    <div @class(['auth-card'])>
        <h2>Confirm password</h2>
        <p class="sub">This is a secure area. Please re-enter your password to continue.</p>

        <form @class(['auth-form']) method="POST" action="{{ route('admin.password.confirm') }}">
            @csrf

            <div @class(['field'])>
                <label @class(['field-label']) for="password">Password</label>
                <div @class(['input-icon'])>
                    <span @class(['ico'])>
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input id="password" @class(['input']) type="password" name="password" placeholder="••••••••" autocomplete="current-password">
                </div>
                <x-input-error :messages="$errors->get('password')" @class(['mt-2']) />
            </div>

            <button @class(['btn', 'btn--primary', 'auth-submit']) type="submit">
                Confirm
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14M13 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>
    <div @class(['auth-main-bottom'])>By signing in you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.</div>
</x-guest-layout>
