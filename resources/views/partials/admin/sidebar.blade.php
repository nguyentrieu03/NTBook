@php
    $menu = config('admin-menu');
    $user = auth()->user();
    $nameParts = preg_split('/\s+/', trim($user?->name ?? ''));
    $initials = count($nameParts) >= 2
        ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1))
        : strtoupper(substr($user?->name ?? 'U', 0, 2));

    $resolveHref = static function (?string $route): string {
        if ($route && \Illuminate\Support\Facades\Route::has($route)) {
            return route($route);
        }

        return '#';
    };
@endphp

<aside class="d-sidebar">
    <div class="brand">
        <div class="brand-logo">
            <svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                <path fill="#fff" d="M6 10h9l3 3 3-3h9v6l-3 3 3 3v2H6v-2l3-3-3-3z" opacity=".9"/>
                <circle cx="18" cy="18" r="3.2" fill="#fff"/>
            </svg>
        </div>
        <div class="brand-text">
            <div class="brand-name">{{ $menu['brand']['name'] }}</div>
            <div class="brand-tag">{{ $menu['brand']['tag'] }}</div>
        </div>
    </div>

    @foreach ($menu['sections'] as $section)
        <nav class="nav-section">
            <div class="nav-label">{{ $section['label'] }}</div>

            @foreach ($section['items'] as $item)
                @php
                    $href = $resolveHref($item['route'] ?? null);
                    $isActive = ($active ?? '') === $item['key'];
                @endphp

                <a @class(['nav-link', 'is-active' => $isActive]) href="{{ $href }}">
                    <svg viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                    <span>{{ $item['text'] }}</span>

                    @if (! empty($item['badge']))
                        <span @class(['nav-badge', $item['badge']['kind']])>{{ $item['badge']['text'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    @endforeach

    <div class="sidebar-footer">
        <div class="workspace">
            <div class="workspace-avatar">{{ $initials }}</div>
            <div class="workspace-text">
                <div class="workspace-name">{{ $user?->name ?? 'Quản trị viên' }}</div>
                <div class="workspace-role">{{ $user?->email ?? 'Admin' }}</div>
            </div>
            <svg class="workspace-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m7 9 5-5 5 5"/>
                <path d="m7 15 5 5 5-5"/>
            </svg>
        </div>
    </div>
</aside>
