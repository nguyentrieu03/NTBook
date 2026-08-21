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
                <div class="field">
                    <label class="field-label" for="attr-name">Tên thuộc tính <span class="req">*</span></label>
                    <input class="input" id="attr-name" name="name" type="text" placeholder="VD: Kích cỡ, Màu sắc…">
                </div>
                <div class="field">
                    <label class="field-label" for="attr-code">Mã code <span class="req">*</span></label>
                    <input class="input mono" id="attr-code" name="code" type="text" placeholder="size">
                    <span class="field-help">Tự sinh từ tên · unique toàn hệ thống.</span>
                </div>
                <div class="field span-2 am-vals-field">
                    <label class="field-label" for="attr-values">Giá trị trong thư viện</label>
                    <div class="am-vals-s2wrap"><select id="attr-values" name="values[]" multiple></select></div>
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