<x-app-layout   
    title="Thêm / Sửa sản phẩm · SKU Hub"
    active="product-form"
    crumbs="Catalog | Sản phẩm | Thêm mới"
    :vite="[
        'resources/css/modules/products/form.css',
        'resources/js/modules/products/form.js',
    ]"
>
<a @class(['back-link']) href="../products/index.html"><svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg> Quay lại danh sách</a>

<div @class(['page-head'])>
  <div @class(['page-head-text'])>
    <span @class(['eyebrow'])>Catalog chuẩn</span>
    <h1 @class(['page-title'])>Thêm sản phẩm</h1>
    <p @class(['page-sub'])>Định nghĩa 1 sản phẩm gốc và sinh các biến thể (SKU chuẩn) từ thuộc tính. SKU chuẩn ở đây sẽ là đích để gom các "SKU loạn" từ nhiều tài khoản eBay về.</p>
  </div>
  <div @class(['page-actions'])>
    <button @class(['btn', 'btn--ghost']) id="btn-cancel">Huỷ</button>
    <button @class(['btn', 'btn--secondary']) id="btn-draft">Lưu nháp</button>
    <button @class(['btn', 'btn--primary']) id="btn-save"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Lưu sản phẩm</button>
  </div>
</div>

<div @class(['pf-grid'])>
  <div @class(['pf-main'])>

    <section @class(['panel'])>
      <div @class(['panel-head'])><h3>Thông tin cơ bản</h3></div>
      <div @class(['panel-body'])>
        <div @class(['form-grid'])>
          <div @class(['field', 'span-2'])>
            <label @class(['field-label'])>Tên sản phẩm <span @class(['req'])>*</span></label>
            <input @class(['input']) id="p-name" type="text" placeholder="VD: Áo bóng đá Team USA sân nhà 2025">
            <span @class(['field-help'])>Tên mô tả sản phẩm gốc, không kèm size/số áo (những cái đó là biến thể).</span>
          </div>
          <div @class(['field'])>
            <label @class(['field-label'])>Mã sản phẩm (SPU) <span @class(['req'])>*</span></label>
            <div @class(['input-group'])><span @class(['addon'])>SPU-</span><input @class(['input']) id="p-spu" type="text" placeholder="USA-HOME"></div>
            <span @class(['field-help'])>Tự sinh từ tên, có thể sửa. Là gốc để đặt SKU biến thể.</span>
          </div>
          <div @class(['field', 'pf-hidden'])>
            <label @class(['field-label'])>Thương hiệu</label>
            <label @class(['select-wrap', 'w-100'])><select @class(['select']) id="p-brand"><option value="nike">Nike</option><option value="adidas">Adidas</option><option value="puma">Puma</option><option value="mitchell-ness">Mitchell &amp; Ness</option><option value="no-brand" selected>No-brand</option></select></label>
          </div>
          <div @class(['field'])>
            <label @class(['field-label'])>Danh mục <span @class(['req'])>*</span></label>
            <label @class(['select-wrap', 'w-100'])><select @class(['select']) id="p-cat"><option>Áo bóng đá</option><option>Áo bóng rổ (NBA)</option><option>Áo bóng chày (MLB)</option><option>Áo retro</option><option>Phụ kiện</option></select></label>
          </div>
          <div @class(['field', 'pf-hidden'])>
            <label @class(['field-label'])>Xuất xứ / Đội</label>
            <input @class(['input']) id="p-team" type="text" placeholder="VD: Team USA">
          </div>
          <div @class(['field', 'span-2'])>
            <label @class(['field-label'])>Mô tả</label>
            <textarea @class(['textarea']) id="p-desc" placeholder="Chất liệu, phom dáng, mùa giải... (dùng chung cho mọi listing)"></textarea>
          </div>
        </div>
      </div>
    </section>

    <section @class(['panel'])>
      <div @class(['panel-head'])>
        <div><span @class(['eyebrow'])>Cấu hình biến thể</span><h3>Thuộc tính &amp; Biến thể (SKU chuẩn)</h3></div>
        <button @class(['btn', 'btn--soft-primary', 'btn--sm']) id="btn-gen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Sinh biến thể</button>
      </div>
      <div @class(['panel-body'])>
        <div id="attr-groups"></div>
        <div @class(['attr-picker-row'])>
          <select id="attr-picker"></select>
          <span @class(['attr-picker-sep'])>hoặc</span>
          <button @class(['btn', 'btn--soft-primary', 'btn--sm']) id="btn-new-attr">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>
            Tạo thuộc tính mới
          </button>
        </div>

        <hr @class(['divider'])>

        <div @class(['row', 'between']) style="margin-bottom:12px">
          <div><span @class(['eyebrow'])>Ma trận biến thể</span><div @class(['muted']) style="font-size:12.5px" id="var-summary">Chưa có biến thể nào.</div></div>
          <div @class(['row', 'g8'])>
            <button @class(['btn', 'btn--ghost', 'btn--sm', 'hide', 'pf-hidden']) id="btn-bulk-price">Đặt giá hàng loạt</button>
          </div>
        </div>
        <div @class(['table-scroll']) id="var-wrap"></div>
      </div>
    </section>

    <section @class(['panel'])>
      <div @class(['panel-head'])><h3>Hình ảnh</h3></div>
      <div @class(['panel-body'])>
        <input type="file" id="dz-input" accept="image/*" multiple hidden>
        <div @class(['dropzone']) id="dz"><div @class(['ico'])><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg></div><p><strong>Chọn ảnh</strong> hoặc kéo thả vào đây · PNG, JPG, WebP · tối đa 5MB/ảnh</p></div>
        <div @class(['thumb-grid']) id="thumbs"></div>
      </div>
    </section>

  </div>

  <aside @class(['pf-side'])>
    <section @class(['panel'])>
      <div @class(['panel-head'])><h3>Xuất bản</h3></div>
      <div @class(['panel-body', 'stack', 'g16'])>
        <div @class(['field'])>
          <label @class(['field-label'])>Trạng thái</label>
          <label @class(['select-wrap', 'w-100'])><select @class(['select']) id="p-status"><option value="active">Đang bán</option><option value="draft" selected>Nháp</option><option value="archived">Lưu trữ</option></select></label>
        </div>
        <label @class(['switch', 'pf-hidden'])><input type="checkbox" id="p-active" checked><span @class(['track'])></span> Cho phép đồng bộ lên eBay</label>
        <div @class(['info-rows'])>
          <div @class(['info-row'])><span @class(['k'])>Số biến thể</span><span @class(['v']) id="s-var">0</span></div>
          <div @class(['info-row', 'pf-hidden'])><span @class(['k'])>Khoảng giá</span><span @class(['v']) id="s-price">—</span></div>
          <div @class(['info-row'])><span @class(['k'])>Cập nhật</span><span @class(['v'])>Vừa xong</span></div>
        </div>
      </div>
    </section>

    <section @class(['panel', 'pf-hidden'])>
      <div @class(['panel-head'])><h3>Gợi ý đối soát</h3></div>
      <div @class(['panel-body'])>
        <div @class(['alert', 'info']) style="margin-bottom:0">
          <div @class(['ico'])><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg></div>
          <div @class(['body'])>Hệ thống tìm thấy <strong>7 listing</strong> từ 3 tài khoản có tên gần giống. Sau khi lưu, bạn có thể map chúng về SKU chuẩn tại màn <a @class(['link']) href="../listings/index.html">Đối soát Listing/SKU</a>.</div>
        </div>
      </div>
    </section>
  </aside>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush
</x-app-layout>