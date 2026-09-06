@php
    $shell = config('admin-shell.footer');
    $copyright = str_replace(
        [':year', ':app'],
        [date('Y'), config('app.name', 'SKU Hub')],
        $shell['copyright']
    );
@endphp

<footer class="d-footer">
    <div>{{ $copyright }}</div>
    <div class="d-footer-meta">
        @foreach ($shell['meta'] as $meta)
            <span>{{ $meta }}</span>
        @endforeach
    </div>
</footer>
