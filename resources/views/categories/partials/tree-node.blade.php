@foreach ($nodes as $node)
    @php
        $hasKids = $node->childrenTree->isNotEmpty();
    @endphp
    <div class="tree-node {{ $hasKids ? 'is-open' : '' }}" data-id="{{ $node->id }}">
        <div class="tree-row">
            <span class="tree-drag-handle" title="Kéo để sắp xếp">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/>
                    <circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/>
                </svg>
            </span>
            <span class="tree-toggle {{ $hasKids ? '' : 'leaf' }}">
                <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
            </span>
            <span class="tree-ico">
                <svg viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-7L10 4H5a2 2 0 0 0-2 2z"/></svg>
            </span>
            <span class="tree-name">{{ $node->name }}</span>
            <span class="tree-slug mono">{{ $node->slug }}</span>
            <span class="tree-count">{{ number_format($node->products_count ?? 0) }}</span>
            <span class="tree-actions">
                <button type="button" class="btn--icon js-cat-add-child"
                    data-id="{{ $node->id }}"
                    data-name="{{ $node->name }}"
                    title="Thêm danh mục con">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
                <button type="button" class="btn--icon js-cat-edit"
                    data-id="{{ $node->id }}"
                    data-name="{{ $node->name }}"
                    data-slug="{{ $node->slug }}"
                    title="Sửa">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.categories.destroy', $node, absolute: false) }}" class="js-cat-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn--icon" title="Xoá"
                        data-name="{{ $node->name }}">
                        <svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                    </button>
                </form>
            </span>
        </div>
        <div class="tree-children">
            @if ($hasKids)
                @include('categories.partials.tree-node', ['nodes' => $node->childrenTree])
            @endif
        </div>
    </div>
@endforeach
