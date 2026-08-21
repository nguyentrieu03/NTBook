@php
    $shell = config('admin-shell');
    $user = auth()->user();
    $nameParts = preg_split('/\s+/', trim($user?->name ?? ''));
    $initials = count($nameParts) >= 2
        ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1))
        : strtoupper(substr($user?->name ?? 'U', 0, 2));

    $crumbParts = array_values(array_filter(array_map('trim', explode('|', $crumbs ?? ''))));

    $resolveHref = static function (?string $route): string {
        if ($route && \Illuminate\Support\Facades\Route::has($route)) {
            return route($route);
        }

        return '#';
    };
@endphp

<header class="d-topbar">
    <div class="crumbs">
        <button class="hamburger" data-drawer-open aria-label="Mở menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        @foreach ($crumbParts as $index => $part)
            @if ($index > 0)
                <svg class="sep" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            @endif
            <span @class(['current' => $index === count($crumbParts) - 1])>{{ $part }}</span>
        @endforeach
    </div>

    <div class="topbar-actions">
        {{-- <div class="ac-acc-switch">
            <label class="select-wrap">
                <select class="select" id="ac-acc-select" aria-label="Chọn tài khoản eBay">
                    @foreach ($shell['ebay_accounts'] as $account)
                        <option value="{{ $account['value'] }}">{{ $account['label'] }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <button class="icon-btn" data-ac-search aria-label="Tìm kiếm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="11" cy="11" r="7"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
        </button>

        <div class="dd-wrap">
            <button class="icon-btn" data-dropdown aria-label="Thông báo">
                <svg viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                @if (($shell['notifications']['count'] ?? 0) > 0)
                    <span class="count danger">{{ $shell['notifications']['count'] }}</span>
                @endif
            </button>

            <div class="dd-menu" role="menu">
                <div class="dd-head">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    Thông báo
                </div>

                <div class="dd-list">
                    @foreach ($shell['notifications']['items'] as $notification)
                        <a class="dd-item" href="{{ $resolveHref($notification['route'] ?? null) }}">
                            <div @class(['dd-avatar', $notification['avatar']])>{{ $notification['avatar_text'] }}</div>
                            <div class="dd-body">
                                <div class="dd-text">{!! $notification['text'] !!}</div>
                                <div class="dd-time">{{ $notification['time'] }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <a class="dd-footer" href="{{ $resolveHref($shell['notifications']['footer']['route'] ?? null) }}">
                    {{ $shell['notifications']['footer']['text'] }}
                </a>
            </div>
        </div> --}}

        <button class="icon-btn" id="themeToggle" aria-label="Đổi giao diện"></button>

        <div class="dd-wrap">
            <div class="avatar" data-dropdown tabindex="0" role="button">{{ $initials }}</div>

            <div class="dd-menu dd-profile" role="menu">
                <div class="dd-profile-head">
                    <div class="dd-profile-name">{{ $user?->name ?? 'Quản trị viên' }}</div>
                    <div class="dd-profile-email">{{ $user?->email ?? '' }}</div>
                </div>

                @foreach ($shell['profile_menu'] as $item)
                    <a class="dd-menu-item" href="{{ $resolveHref($item['route'] ?? null) }}">
                        <svg viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        {{ $item['text'] }}
                    </a>
                @endforeach

                <div class="dd-divider"></div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="dd-menu-item danger">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
