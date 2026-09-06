<x-app-layout
    title="Catalog | Danh mục & Thuộc tính"
    active="categories"
    crumbs="Catalog | Danh mục & Thuộc tính"
    :vite="[
        'resources/css/modules/categories/style.css',
        'resources/js/modules/categories/script.js',
    ]"
>

<div class="page-head">
    <div class="page-head-text">
        <span class="eyebrow">Catalog chuẩn</span>
        <h1 class="page-title">Danh mục &amp; Thuộc tính</h1>
        <p class="page-sub">Danh mục (<span class="mono">categories</span>) phân loại sản phẩm theo cây; thuộc tính (<span class="mono">attributes</span>) định nghĩa các trục tạo nên biến thể như size, số áo, màu — dùng để sinh SKU chuẩn.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert success" style="margin-bottom:16px">
        <div class="ico"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="body">{{ session('success') }}</div>
    </div>
@endif

@if ($errors->any() && ! in_array(old('form'), ['category', 'attribute'], true))
    <div class="alert danger" style="margin-bottom:16px">
        <div class="ico"><svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg></div>
        <div class="body">
            <strong>Không lưu được.</strong>
            <ul style="margin:6px 0 0;padding-left:18px">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="cat-grid">
    <section class="panel">
        <div class="panel-head">
            <div><span class="eyebrow">Phân loại</span><h3>Cây danh mục</h3></div>
            <button type="button" class="btn btn--soft-primary btn--sm" id="btn-add-cat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Thêm
            </button>
        </div>
        <div class="panel-body">
            <div class="tree" id="tree">
                @if ($categoryTree->isEmpty())
                    <div class="attr-empty">Chưa có danh mục nào. Bấm <strong>Thêm</strong> để tạo danh mục gốc.</div>
                @else
                    @include('categories.partials.tree-node', ['nodes' => $categoryTree])
                @endif
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div><span class="eyebrow">Trục biến thể</span><h3>Thuộc tính</h3></div>
            <button type="button" class="btn btn--soft-primary btn--sm" id="btn-add-attr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Thêm thuộc tính
            </button>
        </div>
        <div class="panel-body stack g12">
            <div class="attr-toolbar">
                <div class="input-icon attr-search">
                    <span class="ico"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
                    <input class="input" id="attr-search" type="search" placeholder="Tìm theo tên hoặc code…" autocomplete="off">
                </div>
            </div>
            <div id="attr-list">
                @include('categories.partials.attr-table', ['attributes' => $attributes])
            </div>
        </div>
    </section>
</div>

@include('categories.partials.attribute-modal')
@include('categories.partials.category-modal')

{{-- Data island: Blade chỉ xuất DỮ LIỆU, mọi logic nằm trong Vite module. --}}
@php
    $categoriesConfig = [
        'reorderUrl' => route('admin.categories.reorder', absolute: false),
        'storeUrl' => route('admin.categories.store', absolute: false),
        'updateUrlBase' => route('admin.categories.index', absolute: false),
        'attrStoreUrl' => route('admin.attributes.store', absolute: false),
        'attrUpdateUrlBase' => '/admin/attributes',
        'openModal' => old('form'),
        'old' => [
            'editId' => old('entity_id'),
            'name' => old('name'),
            'slug' => old('slug'),
            'parentId' => old('parent_id'),
            'parentName' => old('parent_name'),
            'code' => old('code'),
            'values' => old('values'),
        ],
    ];
@endphp

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script type="application/json" id="ssp-categories-config">
        {!! json_encode($categoriesConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
</x-app-layout>
