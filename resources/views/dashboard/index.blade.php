<x-app-layout
  title="Dashboard"
  active="dashboard"
  crumbs="Tổng quan | Dashboard"
  :vite="[
    'resources/css/modules/dashboard/style.css',
    'resources/js/modules/dashboard/script.js',
  ]"
>
  <div @class(['page-head'])>
    <div @class(['page-head-text'])>
      <span @class(['eyebrow'])>Tổng quan vận hành</span>
      <h1 @class(['page-title'])>Chào buổi sáng 👋</h1>
      <p @class(['page-sub'])>Bức tranh toàn cảnh catalog & bán hàng đa tài khoản eBay. Ưu tiên hôm nay: đối soát các listing chưa gắn SKU chuẩn để số liệu bán chạy/bán kém chính xác.</p>
    </div>
    <div @class(['page-actions'])>
      <button @class(['btn', 'btn--ghost']) id="btn-sync"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg> Đồng bộ eBay</button>
      <a @class(['btn', 'btn--primary']) href="../product-form/index.html"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> Thêm sản phẩm</a>
    </div>
  </div>

  <div @class(['alert', 'warning']) id="unmapped-alert">
    <div @class(['ico'])><svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg></div>
    <div @class(['body'])>
      <div @class(['title'])>48 listing đang chờ đối soát SKU</div>
      Những listing này chưa được gắn về SKU chuẩn nên chưa được tính vào thống kê hợp nhất. <a @class(['link']) href="../listings/index.html">Đối soát ngay →</a>
    </div>
    <button @class(['close']) data-dismiss="#unmapped-alert"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
  </div>

  <div @class(['stat-row']) id="stat-row"></div>

  <div @class(['grid'])>
    <section @class(['col-8', 'card'])>
      <div @class(['card-head'])>
        <div @class(['card-title-wrap'])><span @class(['eyebrow'])>Doanh thu hợp nhất</span><h2 @class(['card-title'])>Doanh thu theo ngày</h2></div>
        <div @class(['segment']) id="rev-range">
          <button @class(['is-active']) data-range="7">7 ngày</button>
          <button data-range="14">14 ngày</button>
          <button data-range="30">30 ngày</button>
        </div>
      </div>
      <div @class(['bar-chart']) id="rev-chart"></div>
      <div @class(['chart-meta-row'])>
        <div @class(['chart-meta-cell'])><span @class(['chart-meta-label'])>Tổng doanh thu</span><span @class(['chart-meta-value']) id="rev-total">$0</span></div>
        <div @class(['chart-meta-cell'])><span @class(['chart-meta-label'])>Đơn</span><span @class(['chart-meta-value']) id="rev-orders">0</span></div>
        <div @class(['chart-meta-cell'])><span @class(['chart-meta-label'])>AOV</span><span @class(['chart-meta-value']) id="rev-aov">$0</span></div>
        <div @class(['chart-meta-cell'])><span @class(['chart-meta-label'])>So kỳ trước</span><span @class(['chart-meta-value', 'up']) id="rev-delta">+0%</span></div>
      </div>
    </section>

    <section @class(['col-4', 'card'])>
      <div @class(['card-head'])><div @class(['card-title-wrap'])><span @class(['eyebrow'])>Chất lượng catalog</span><h2 @class(['card-title'])>Tỷ lệ map SKU</h2></div></div>
      <div @class(['donut-wrap'])>
        <div @class(['donut']) id="map-donut"><div @class(['donut-hole'])><span @class(['pct']) id="map-pct">0%</span><span @class(['lbl'])>đã map</span></div></div>
      </div>
      <div @class(['legend-list']) id="map-legend"></div>
    </section>

    <section @class(['col-6', 'card'])>
      <div @class(['card-head'])><div @class(['card-title-wrap'])><span @class(['eyebrow'])>30 ngày qua</span><h2 @class(['card-title'])>Top bán chạy</h2></div><a @class(['card-action']) href="../products/index.html">Xem tất cả <svg viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a></div>
      <div @class(['rank-list']) id="top-sellers"></div>
    </section>

    <section @class(['col-6', 'card'])>
      <div @class(['card-head'])><div @class(['card-title-wrap'])><span @class(['eyebrow'])>Cần chú ý</span><h2 @class(['card-title'])>Bán kém / tồn đọng</h2></div><a @class(['card-action']) href="../products/index.html">Xem tất cả <svg viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a></div>
      <div @class(['rank-list']) id="slow-sellers"></div>
    </section>

    <section @class(['col-12', 'card'])>
      <div @class(['card-head'])><div @class(['card-title-wrap'])><span @class(['eyebrow'])>Realtime</span><h2 @class(['card-title'])>Đơn hàng gần đây</h2></div><a @class(['card-action']) href="../orders/index.html">Tới trang đơn hàng <svg viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a></div>
      <div id="recent-orders"></div>
    </section>
  </div>
</x-app-layout>