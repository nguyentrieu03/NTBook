<div class="ac-modal-backdrop cat-modal-backdrop" id="cat-modal" hidden>
    <div class="ac-modal" role="dialog" aria-modal="true">
        <div class="modal-head">
            <div class="modal-title" id="cat-modal-title">Thêm danh mục</div>
            <button type="button" class="icon-btn js-cat-modal-close" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="cat-form" action="{{ route('admin.categories.store', absolute: false) }}">
            @csrf
            <input type="hidden" name="_method" id="cat-form-method" value="POST">
            <input type="hidden" name="form" value="category">
            <input type="hidden" name="entity_id" id="cat-entity-id" value="">
            <input type="hidden" name="parent_id" id="cat-parent-id" value="">
            <input type="hidden" name="parent_name" id="cat-parent-name-hidden" value="">
            <div class="modal-body stack g12">
                @if (old('form') === 'category' && session('error'))
                    <div class="modal-form-errors" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                @if (old('form') === 'category' && $errors->any())
                    <div class="modal-form-errors" role="alert">
                        <strong>Không lưu được. Vui lòng kiểm tra các trường bên dưới.</strong>
                    </div>
                @endif
                <p class="field-help js-cat-parent-note" hidden style="margin:0">
                    Danh mục con của <strong id="cat-parent-note-name"></strong>.
                </p>
                <div class="field">
                    <label class="field-label" for="cat-name">Tên danh mục <span class="req">*</span></label>
                    <input class="input @if(old('form') === 'category' && $errors->has('name')) is-invalid @endif" id="cat-name" name="name" type="text" placeholder="VD: Áo bóng đá" value="{{ old('form') === 'category' ? old('name') : '' }}">
                    @if (old('form') === 'category')
                        <x-input-error :messages="$errors->get('name')" />
                    @endif
                </div>
                <div class="field">
                    <label class="field-label" for="cat-slug">Slug (URL)</label>
                    <input class="input mono @if(old('form') === 'category' && $errors->has('slug')) is-invalid @endif" id="cat-slug" name="slug" type="text" placeholder="ao-bong-da" value="{{ old('form') === 'category' ? old('slug') : '' }}">
                    @if (old('form') === 'category')
                        <x-input-error :messages="$errors->get('slug')" />
                    @endif
                    <span class="field-help">Tự sinh từ tên. Nếu slug trùng, hệ thống tự thêm <span class="mono">-2</span>, <span class="mono">-3</span>…</span>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--secondary js-cat-modal-close">Huỷ</button>
                <button type="submit" class="btn btn--primary" id="cat-modal-submit">Thêm</button>
            </div>
        </form>
    </div>
</div>
