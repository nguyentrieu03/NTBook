<x-guest-layout>
    <div @class(['auth-main-top'])>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="switch-link" style="background:none;border:none;cursor:pointer;padding:0;">
                Sign out
            </button>
        </form>
    </div>
    <div @class(['auth-card'])>
        <h2>Verify your email</h2>
        <p class="sub">Thanks for signing up! Please click the verification link we sent to your email address to activate your account.</p>

        @if (session('status') == 'verification-link-sent')
            <div class="auth-session-status" style="display:flex;align-items:center;gap:7px;padding:10px 14px;border-radius:8px;background:var(--success-soft);border:1px solid color-mix(in oklab,var(--success) 25%,transparent);color:var(--success);font-size:13px;font-weight:500;margin-bottom:16px;">
                <svg style="flex-shrink:0;" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                A new verification link has been sent to your email address.
            </div>
        @endif

        <form @class(['auth-form']) method="POST" action="{{ route('admin.verification.send') }}">
            @csrf
            <button @class(['btn', 'btn--primary', 'auth-submit']) type="submit">
                Resend verification email
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14M13 5l7 7-7 7" />
                </svg>
            </button>
        </form>
    </div>
    <div @class(['auth-main-bottom'])>Need help? <a href="#">Contact support</a>.</div>
</x-guest-layout>
