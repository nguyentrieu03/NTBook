@props(['status', 'variant' => 'success'])

@if ($status)
    @php
        $isError = $variant === 'error';
    @endphp

    <div
        {{ $attributes->merge(['class' => 'auth-session-status auth-session-status--' . $variant]) }}
        style="display:flex;align-items:center;gap:7px;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:500;margin-bottom:16px;{{ $isError
            ? 'background:var(--danger-soft);border:1px solid color-mix(in oklab,var(--danger) 25%,transparent);color:var(--danger);'
            : 'background:var(--success-soft);border:1px solid color-mix(in oklab,var(--success) 25%,transparent);color:var(--success);' }}"
        role="alert"
    >
        @if ($isError)
            <svg style="flex-shrink:0;" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
        @else
            <svg style="flex-shrink:0;" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        @endif
        <span>{{ $status }}</span>
    </div>
@endif
