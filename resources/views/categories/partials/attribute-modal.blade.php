@php
    $attributeValueErrors = old('form') === 'attribute'
        ? collect($errors->messages())->filter(fn ($_, $key) => str_starts_with((string) $key, 'values'))->flatten()->all()
        : [];
@endphp
<div class="ac-modal-backdrop attr-modal-backdrop" id="attr-modal" hidden>
    <div class="ac-modal wide" role="dialog" aria-modal="true">
        <div class="modal-head">
            <div class="modal-title" id="attr-modal-title">Tạo thuộc tính mới</div>
            <button type="button" class="icon-btn js-attr-modal-close" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="attr-form" action="{{ route('admin.attributes.store', absolute: false) }}">
            @csrf
            <input type="hidden" name="_method" id="attr-form-method" value="POST">
            <input type="hidden" name="form" value="attribute">
            <input type="hidden" name="entity_id" id="attr-entity-id" value="">
            <div class="modal-body stack g12">
                @if (old('form') === 'attribute' && session('error'))
                    <div class="modal-form-errors" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                @if (old('form') === 'attribute' && $errors->any())
                    <div class="modal-form-errors" role="alert">
                        <strong>Không lưu được. Vui lòng kiểm tra các trường bên dưới.</strong>
                    </div>
                @endif
                <div class="field">
                    <label class="field-label" for="attr-name">Tên thuộc tính <span class="req">*</span></label>
                    <input class="input @if(old('form') === 'attribute' && $errors->has('name')) is-invalid @endif" id="attr-name" name="name" type="text" placeholder="VD: Kích cỡ, Màu sắc…" value="{{ old('form') === 'attribute' ? old('name') : '' }}">
                    @if (old('form') === 'attribute')
                        <x-input-error :messages="$errors->get('name')" />
                    @endif
                </div>
                <div class="field">
                    <label class="field-label" for="attr-code">Mã code <span class="req">*</span></label>
                    <input class="input mono @if(old('form') === 'attribute' && $errors->has('code')) is-invalid @endif" id="attr-code" name="code" type="text" placeholder="size" value="{{ old('form') === 'attribute' ? old('code') : '' }}">
                    @if (old('form') === 'attribute')
                        <x-input-error :messages="$errors->get('code')" />
                    @endif
                    <span class="field-help">Tự sinh từ tên · unique toàn hệ thống.</span>
                </div>
                <div class="field span-2 am-vals-field">
                    <label class="field-label" for="attr-values">Giá trị trong thư viện</label>
                    <div class="am-vals-s2wrap @if(old('form') === 'attribute' && $attributeValueErrors !== []) is-invalid @endif"><select id="attr-values" name="values[]" multiple></select></div>
                    @if (old('form') === 'attribute')
                        <x-input-error :messages="$attributeValueErrors" />
                    @endif
                    <span class="field-help">Bấm để chọn, hoặc nhập rồi Enter để thêm giá trị mới. Trùng trong cùng thuộc tính thì gộp (không phân biệt hoa/thường).</span>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--secondary js-attr-modal-close">Huỷ</button>
                <button type="submit" class="btn btn--primary" id="attr-modal-submit">Tạo thuộc tính</button>
            </div>
        </form>
    </div>
</div>
