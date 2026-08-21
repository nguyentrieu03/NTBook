<x-app-layout   
    title="Sản phẩm · SKU Hub"
    active="products"
    crumbs="Catalog | Sản phẩm"
    :vite="[
        'resources/css/modules/products/style.css',
        'resources/js/modules/products/script.js',
    ]"
>   
    <div class="page-head">
        <div class="page-head-text">
          <span class="eyebrow">Catalog chuẩn</span>
          <h1 class="page-title">Sản phẩm</h1>
          <p class="page-sub">Mỗi dòng là một <strong>sản phẩm gốc (SPU)</strong> — có thể gồm nhiều biến thể (SKU chuẩn) và được list ở nhiều tài khoản eBay khác nhau. Đây là "nguồn sự thật duy nhất" để hợp nhất SKU.</p>
        </div>
        <div class="page-actions">
          <button class="btn btn--ghost" id="btn-import"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg> Import Excel</button>
          <a class="btn btn--primary" href="../product-form/index.html"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> Thêm sản phẩm</a>
        </div>
      </div>

      <div class="stat-row">
        <div class="stat-mini"><div class="ico primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M20 7 12 3 4 7l8 4 8-4z"/><path d="M4 7v10l8 4 8-4V7"/></svg></div><div><div class="stat-mini-label">Tổng SPU</div><div class="stat-mini-value">1,284</div></div></div>
        <div class="stat-mini"><div class="ico info"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 6h16M4 12h16M4 18h16"/></svg></div><div><div class="stat-mini-label">Biến thể (SKU)</div><div class="stat-mini-value">6,910</div></div></div>
        <div class="stat-mini"><div class="ico success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M20 6 9 17l-5-5"/></svg></div><div><div class="stat-mini-label">Đang bán</div><div class="stat-mini-value">1,156</div></div></div>
        <div class="stat-mini"><div class="ico warning"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/></svg></div><div><div class="stat-mini-label">Bản nháp</div><div class="stat-mini-value">128</div></div></div>
      </div>

      <div class="filter-bar">
        <div class="input-icon"><span class="ico"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
          <input class="input" id="q" type="text" placeholder="Tìm theo tên / SPU / SKU chuẩn..."></div>
        <div class="cat-filter-wrap"><select id="f-cat">
          <option value="">Tất cả danh mục</option>
        </select></div>
        <label class="select-wrap"><select class="select" id="f-status">
          <option value="">Mọi trạng thái</option><option value="active">Đang bán</option><option value="draft">Nháp</option><option value="archived">Lưu trữ</option>
        </select></label>
        <span class="spacer"></span>
        <span class="filter-count hide" id="fcount"></span>
        <button class="btn btn--secondary btn--sm" id="reset"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg> Đặt lại</button>
      </div>

      <div class="card" style="padding:16px 18px">
        <div class="view-toolbar">
          <div class="view-toggle">
            <button class="view-btn is-active" id="btn-grid" title="Dạng lưới">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            </button>
            <button class="view-btn" id="btn-table" title="Dạng bảng">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 10h18M3 14h18M3 18h18"/><path d="M8 6v12M16 6v12" stroke-opacity=".4"/></svg>
            </button>
          </div>
        </div>
        <div id="grid-view"></div>
        <div id="tbl" style="display:none"></div>
      </div>
</x-app-layout>