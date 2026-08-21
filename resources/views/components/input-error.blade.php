@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'field-errors']) }} style="list-style:none;margin:1px 0 0;padding:0;display:flex;flex-direction:column;gap:4px;">
        @foreach ((array) $messages as $message)
            <li style="display:flex;align-items:center;gap:5px;font-size:12.5px;font-weight:500;color:#ef4444;line-height:1.4;">
                <svg style="flex-shrink:0;color:#ef4444;" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
