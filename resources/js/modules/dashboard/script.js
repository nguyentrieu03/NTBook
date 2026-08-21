/* Dashboard — mock analytics wired through the AC helper API. */
(function ($) {
  "use strict";

  var jerseyIco = '<svg viewBox="0 0 24 24"><path d="M8 3 4 6l2 3 2-1v10h8V8l2 1 2-3-4-3-2 2H10z"/></svg>';

  /* ---- Headline stats (canonical / channel / transaction layers) ---- */
  var STATS = [
    { ico: "primary", label: "Sản phẩm chuẩn", value: "1,284", sub: "SPU trong catalog", svg: '<path d="M20 7 12 3 4 7l8 4 8-4z"/><path d="M4 7v10l8 4 8-4V7"/>' },
    { ico: "info", label: "Biến thể (SKU chuẩn)", value: "6,910", sub: "product_variants", svg: '<path d="M4 6h16M4 12h16M4 18h16"/>' },
    { ico: "success", label: "Listing đang bán", value: "18,432", sub: "trên 24 tài khoản", svg: '<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>' },
    { ico: "warning", label: "Chờ đối soát SKU", value: "48", sub: "listing_items chưa map", svg: '<path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>' },
    { ico: "purple", label: "Tài khoản eBay", value: "24", sub: "22 hoạt động · 2 lỗi token", svg: '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>' },
    { ico: "danger", label: "Doanh thu 30 ngày", value: "$142.8K", sub: "8,204 đơn", svg: '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>' }
  ];

  function renderStats() {
    $("#stat-row").html(STATS.map(function (s) {
      return '<div class="stat-mini"><div class="ico ' + s.ico + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">' + s.svg + '</svg></div>' +
        '<div><div class="stat-mini-label">' + s.label + '</div><div class="stat-mini-value">' + s.value + '</div><div class="stat-mini-sub">' + s.sub + '</div></div></div>';
    }).join(""));
  }

  /* ---- Revenue bar chart ---- */
  function seededSeries(days) {
    var out = [], seed = 42;
    for (var i = 0; i < days; i++) {
      seed = (seed * 9301 + 49297) % 233280;
      var base = 3200 + (seed / 233280) * 3400;
      out.push(Math.round(base + (i / days) * 1500));
    }
    return out;
  }

  function renderRevenue(days) {
    var data = seededSeries(days);
    var max = Math.max.apply(null, data);
    var today = new Date();
    var html = data.map(function (v, i) {
      var d = new Date(today); d.setDate(d.getDate() - (days - 1 - i));
      var label = (d.getMonth() + 1) + "/" + d.getDate();
      return '<div class="bar-col"><div class="bar" style="height:' + (v / max * 100).toFixed(1) + '%" data-val="' + AC.usd(v) + '"></div>' +
        '<span class="lbl">' + label + '</span></div>';
    }).join("");
    $("#rev-chart").html(html);
    requestAnimationFrame(function () { $("#rev-chart .bar-col").each(function (i, el) { setTimeout(function () { el.classList.add("is-in"); }, i * 22); }); });

    var total = data.reduce(function (a, b) { return a + b; }, 0);
    var orders = Math.round(total / 17.4);
    $("#rev-total").text(AC.usd(total));
    $("#rev-orders").text(AC.number(orders));
    $("#rev-aov").text(AC.usd(total / orders));
  }

  /* ---- Map-quality donut ---- */
  function renderDonut() {
    var seg = [
      { name: "Đã map SKU chuẩn", val: 91, color: "var(--success)" },
      { name: "Chờ đối soát", val: 6, color: "var(--warning)" },
      { name: "Bỏ qua / lỗi", val: 3, color: "var(--t-light)" }
    ];
    var acc = 0, stops = seg.map(function (s) {
      var from = acc; acc += s.val;
      return s.color + " " + from + "% " + acc + "%";
    }).join(",");
    var $d = $("#map-donut");
    $d.css("background", "conic-gradient(" + stops + ")");
    $("#map-pct").text(seg[0].val + "%");
    $("#map-legend").html(seg.map(function (s) {
      return '<div class="legend-row"><span class="sw" style="background:' + s.color + '"></span>' +
        '<span class="name">' + s.name + '</span><span class="val">' + s.val + '%</span></div>';
    }).join(""));
  }

  /* ---- Best / worst sellers (unified across accounts) ---- */
  var TOP = [
    ["Áo Mỹ sân nhà #9 (Team USA)", "SPU-USA-HOME · 6 acc", 842, "$41.2K"],
    ["Áo Argentina #10 sân khách", "SPU-ARG-AWAY · 4 acc", 611, "$33.9K"],
    ["Áo Brazil retro 2002", "SPU-BRA-RETRO · 5 acc", 508, "$28.4K"],
    ["Áo Real Madrid #7 2025", "SPU-RM-HOME · 3 acc", 470, "$30.1K"],
    ["Áo Lakers #23 city edition", "SPU-LAL-CITY · 4 acc", 388, "$22.7K"]
  ];
  var SLOW = [
    ["Áo Juventus sân khách 2019", "SPU-JUV-AW19 · 2 acc", 3, "tồn 214"],
    ["Áo Chelsea GK vàng 2021", "SPU-CHE-GK21 · 1 acc", 2, "tồn 156"],
    ["Áo Đức retro 1996 size 4XL", "SPU-GER-96 · 2 acc", 1, "tồn 98"],
    ["Áo PSG sân nhà 2020 #7", "SPU-PSG-20 · 1 acc", 1, "tồn 77"],
    ["Áo Ý sân khách 2018 #21", "SPU-ITA-AW18 · 1 acc", 0, "tồn 63"]
  ];

  function rankRow(r, i, metricClass) {
    return '<div class="rank-item"><div class="rank-no">' + (i + 1) + '</div>' +
      '<div class="rank-thumb">' + jerseyIco + '</div>' +
      '<div class="rank-body"><div class="rank-name">' + AC.esc(r[0]) + '</div><div class="rank-sub">' + AC.esc(r[1]) + '</div></div>' +
      '<div class="rank-metric"><div class="n">' + AC.number(r[2]) + '</div><div class="l ' + (metricClass || "") + '">' + AC.esc(r[3]) + '</div></div></div>';
  }
  function renderRanks() {
    $("#top-sellers").html(TOP.map(function (r, i) { return rankRow(r, i); }).join(""));
    $("#slow-sellers").html(SLOW.map(function (r, i) { return rankRow(r, i); }).join(""));
  }

  /* ---- Recent orders table ---- */
  var ORDERS = [
    { id: "17-12934-88210", acc: "us-sport-jerseys", item: "Áo Mỹ sân nhà #9 · L", sku: "USA-HOME-9-L", total: 49.9, status: "paid", when: "2 phút trước" },
    { id: "26-12934-77120", acc: "usa-fan-store", item: "Áo Argentina #10 · M", sku: "ARG-AWAY-10-M", total: 54.5, status: "shipped", when: "16 phút trước" },
    { id: "09-11020-55410", acc: "topgear-outlet", item: "Áo Real Madrid #7 · XL", sku: "RM-HOME-7-XL", total: 63.0, status: "paid", when: "38 phút trước" },
    { id: "33-99120-11002", acc: "jersey-empire-us", item: "Áo Lakers #23 · L", sku: "LAL-CITY-23-L", total: 58.0, status: "unmapped", when: "51 phút trước" },
    { id: "17-88120-33019", acc: "us-sport-jerseys", item: "Áo Brazil retro · M", sku: "BRA-RETRO-M", total: 47.0, status: "cancelled", when: "1 giờ trước" }
  ];
  var OST = {
    paid: { cls: "success", label: "Đã thanh toán" },
    shipped: { cls: "info", label: "Đã gửi" },
    unmapped: { cls: "warning", label: "SKU chưa map" },
    cancelled: { cls: "danger", label: "Đã huỷ" }
  };

  function renderOrders() {
    AC.DataTable({
      mount: "#recent-orders",
      pageSize: 5,
      columns: [
        { label: "Mã đơn eBay", render: function (r) { return '<span class="sku">' + AC.esc(r.id) + '</span>'; } },
        { label: "Tài khoản", render: function (r) { return '<span class="badge">' + AC.esc(r.acc) + '</span>'; } },
        { label: "Sản phẩm", render: function (r) { return '<div class="cell-thumb"><div class="thumb">' + jerseyIco + '</div><div class="meta"><div class="name">' + AC.esc(r.item) + '</div><div class="sub">SKU: ' + AC.esc(r.sku) + '</div></div></div>'; } },
        { label: "Giá trị", className: "num", render: function (r) { return AC.usd(r.total); } },
        { label: "Trạng thái", className: "center", render: function (r) { var s = OST[r.status]; return '<span class="pill ' + s.cls + '">' + s.label + '</span>'; } },
        { label: "Thời gian", className: "num", render: function (r) { return '<span class="t-light" style="font-size:12px">' + AC.esc(r.when) + '</span>'; } }
      ],
      rows: ORDERS
    });
  }

  $(function () {
    renderStats();
    renderRevenue(7);
    renderDonut();
    renderRanks();
    renderOrders();

    $("#rev-range").on("click", "button", function () {
      $("#rev-range button").removeClass("is-active");
      $(this).addClass("is-active");
      renderRevenue(Number($(this).data("range")));
    });

    $("#btn-sync").on("click", function () {
      var $b = $(this).prop("disabled", true).html('<span class="spinner sm"></span> Đang kéo dữ liệu...');
      setTimeout(function () {
        $b.prop("disabled", false).html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/></svg> Đồng bộ eBay');
        AC.toast("Đã kéo 32 đơn mới và 210 listing từ 24 tài khoản.", "success", { title: "Đồng bộ hoàn tất" });
      }, 1600);
    });
  });
})(window.jQuery);
