/* Products — grid / table dual-view with variant modal. */
(function ($) {
  "use strict";

  /* ── Category tree Select2 ─────────────────────────────── */
  function buildCatOptions(nodes, depth) {
    var html = "";
    (nodes || []).forEach(function (n) {
      html += '<option value="' + AC.esc(n.name) + '" data-depth="' + depth + '">' + AC.esc(n.name) + "</option>";
      if (n.children && n.children.length) html += buildCatOptions(n.children, depth + 1);
    });
    return html;
  }

  function catTemplateResult(item) {
    if (!item.id) return item.text;
    var depth = parseInt($(item.element).data("depth") || 0, 10);
    if (depth === 0) return $("<span>").text(item.text);
    var prefix = "";
    for (var i = 0; i < depth - 1; i++) prefix += "|\u00a0\u00a0\u00a0";
    prefix += "|--- ";
    var $w = $('<span class="cat-tree-item">');
    $w.append($('<span class="cat-tree-prefix">').text(prefix));
    $w.append($("<span>").text(item.text));
    return $w;
  }

  /* ── SVG icons ─────────────────────────────────────────── */
  var jerseyIco = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3 4 6l2 3 2-1v10h8V8l2 1 2-3-4-3-2 2H10z"/></svg>';
  var copyIco  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
  var editIco  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/></svg>';
  var delIco   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>';

  /* ── Demo images (deterministic placeholder + icon fallback) ── */
  function prodImgUrl(r) { return "https://picsum.photos/seed/spu" + r.id + "/400/400"; }
  function variantImgUrl(r, idx) { return "https://picsum.photos/seed/spu" + r.id + "v" + idx + "/200/200"; }
  function coverImg(url, alt) {
    return '<img class="cover-img" src="' + url + '" alt="' + AC.esc(alt || "") + '" loading="lazy" onerror="this.style.display=\'none\'">';
  }

  /* ── Status ────────────────────────────────────────────── */
  var STATUS = {
    active:   { label: "Đang bán", cls: "success" },
    draft:    { label: "Nháp",     cls: "gray"    },
    archived: { label: "Lưu trữ", cls: "warning" }
  };

  /* ── Raw data ──────────────────────────────────────────── */
  // [name, spu, cat, brand, variants, accounts, unmapped, status]
  var raw = [
    ["Áo bóng đá Team USA sân nhà 2025",  "SPU-USA-HOME",  "Áo bóng đá",        "Nike",           12, 6, 0, "active"],
    ["Áo Argentina sân khách #10 Messi",   "SPU-ARG-AWAY",  "Áo bóng đá",        "Adidas",          9, 4, 2, "active"],
    ["Áo Brazil retro 2002 vô địch WC",    "SPU-BRA-RETRO", "Áo retro",           "Nike",            8, 5, 1, "active"],
    ["Áo Real Madrid sân nhà #7 2025",     "SPU-RM-HOME",   "Áo bóng đá",        "Adidas",         10, 3, 0, "active"],
    ["Áo Lakers #23 City Edition 2025",    "SPU-LAL-CITY",  "Áo bóng rổ (NBA)",  "Nike",            7, 4, 3, "active"],
    ["Áo Golden State Warriors #30",        "SPU-GSW-30",    "Áo bóng rổ (NBA)",  "Nike",            6, 2, 0, "active"],
    ["Áo Yankees MLB sân nhà #99",          "SPU-NYY-99",    "Áo bóng chày (MLB)","Nike",            5, 3, 0, "active"],
    ["Áo Juventus sân khách 2019 retro",   "SPU-JUV-AW19",  "Áo retro",           "Adidas",          6, 2, 4, "draft"  ],
    ["Áo Chelsea thủ môn vàng 2021",        "SPU-CHE-GK21",  "Áo bóng đá",        "Nike",            4, 1, 1, "active"],
    ["Áo tuyển Đức retro 1996",             "SPU-GER-96",    "Áo retro",           "Adidas",          8, 2, 2, "active"],
    ["Áo PSG sân nhà 2020 #7 Mbappe",      "SPU-PSG-20",    "Áo bóng đá",        "Nike",            7, 1, 0, "archived"],
    ["Áo tuyển Ý sân khách 2018 #21",      "SPU-ITA-AW18",  "Áo bóng đá",        "Puma",            5, 1, 0, "draft"  ],
    ["Nón snapback Lakers chính hãng",      "SPU-LAL-CAP",   "Phụ kiện",           "Mitchell & Ness", 3, 4, 0, "active"],
    ["Áo Manchester City sân nhà 2025",    "SPU-MCI-HOME",  "Áo bóng đá",        "Puma",           11, 5, 1, "active"],
    ["Áo Boston Celtics #0 Tatum",          "SPU-BOS-0",     "Áo bóng rổ (NBA)",  "Nike",            6, 3, 0, "active"],
    ["Áo Liverpool sân nhà 2025 #11",      "SPU-LIV-HOME",  "Áo bóng đá",        "Nike",            9, 4, 2, "active"],
    ["Áo Barcelona retro 2011",             "SPU-BAR-11",    "Áo retro",           "Nike",            7, 2, 0, "active"],
    ["Áo Dodgers MLB #17 Ohtani",           "SPU-LAD-17",    "Áo bóng chày (MLB)","Nike",            5, 3, 1, "active"],
    ["Khăn cổ vũ Team USA (set 2)",         "SPU-USA-SCARF", "Phụ kiện",           "No-brand",        2, 3, 0, "draft"  ],
    ["Áo tuyển Pháp sân nhà 2024 #10",     "SPU-FRA-HOME",  "Áo bóng đá",        "Nike",           10, 5, 0, "active"]
  ].map(function (r, i) {
    return { id: i + 1, name: r[0], spu: r[1], cat: r[2], brand: r[3], variants: r[4], accounts: r[5], unmapped: r[6], status: r[7] };
  });

  /* ── Mock variant generator ────────────────────────────── */
  var SIZES = ["S", "M", "L", "XL", "2XL", "3XL"];
  var NUMS  = ["7", "9", "10", "11", "23", "30"];

  function genVariants(product) {
    var isAccessory = product.cat === "Phụ kiện";
    var out = [];
    for (var i = 0; i < product.variants; i++) {
      var size = SIZES[i % SIZES.length];
      var num  = NUMS[Math.floor(i / SIZES.length) % NUMS.length];
      var sku  = product.spu + "-" + size + (isAccessory ? "" : "-" + num);
      var attrs = [{ name: "Size", value: size }];
      if (!isAccessory) attrs.push({ name: "Số áo", value: "#" + num });
      out.push({ sku: sku, attrs: attrs });
    }
    return out;
  }

  /* ── Copy helper ───────────────────────────────────────── */
  function copyText(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(function () { AC.toast("Đã sao chép!", "success"); });
    } else {
      var $t = $("<textarea>").val(text).css({ position: "fixed", top: 0, left: 0, opacity: 0 }).appendTo("body");
      $t[0].select(); document.execCommand("copy"); $t.remove();
      AC.toast("Đã sao chép!", "success");
    }
  }

  /* ── Variant modal ─────────────────────────────────────── */
  function openVariantModal(product) {
    var variants = genVariants(product);
    var s = STATUS[product.status];

    var cards = variants.map(function (v, idx) {
      return '<div class="sku-card">' +
        '<div class="sku-card-img">' + jerseyIco + coverImg(variantImgUrl(product, idx), v.sku) + '</div>' +
        '<div class="sku-card-body">' +
          '<div class="sku-card-code">' +
            '<span>' + AC.esc(v.sku) + '</span>' +
            '<button class="copy-btn" data-copy="' + AC.esc(v.sku) + '" title="Sao chép SKU">' + copyIco + '</button>' +
          '</div>' +
          '<div class="sku-card-attrs">' +
            v.attrs.map(function (a) {
              return '<span class="attr-chip">' +
                '<span class="attr-chip-name">' + AC.esc(a.name) + '</span>' +
                '<span class="attr-chip-val">'  + AC.esc(a.value) + '</span>' +
              '</span>';
            }).join("") +
          '</div>' +
        '</div>' +
      '</div>';
    }).join("");

    var header = '<div class="sku-modal-meta">' +
      '<span class="badge">' + AC.esc(product.cat) + '</span>' +
      '<span class="pill ' + s.cls + '">' + s.label + '</span>' +
      '<span class="var-count">' + product.variants + ' biến thể</span>' +
    '</div>';

    AC.modal({
      title: product.name,
      size: "wide",
      body: header + '<div class="sku-grid">' + cards + '</div>',
      buttons: [{ text: "Đóng", variant: "secondary" }],
      onReady: function (m) {
        m.$body.on("click", ".copy-btn", function (e) {
          e.stopPropagation();
          copyText($(this).data("copy"));
        });
      }
    });
  }

  /* ── Grid renderer ─────────────────────────────────────── */
  var GRID_PAGE_SIZE = 36;
  var gridState = { page: 1, filtered: [] };
  var currentView = "grid";
  var term = "";
  var gFilterFn = null;
  var dt = null;

  function archiveProduct(id) {
    var p = raw.find(function (b) { return b.id === id; });
    if (!p) return;
    AC.confirm({
      title: "Lưu trữ sản phẩm?",
      message: 'Lưu trữ "' + p.name + '"? Listing đang chạy vẫn giữ nguyên; sản phẩm chỉ bị ẩn khỏi catalog đang hoạt động.',
      okText: "Lưu trữ", danger: true,
      onOk: function () {
        var i = raw.findIndex(function (b) { return b.id === id; });
        raw.splice(i, 1);
        if (dt) dt.setRows(raw);
        gridState.filtered = getFiltered();
        if (currentView === "grid") renderGrid();
        AC.toast("Đã lưu trữ sản phẩm.", "success");
      }
    });
  }

  function makeGridCard(r) {
    return '<div class="prod-card" data-open-id="' + r.id + '">' +
      '<div class="prod-card-img">' + jerseyIco + coverImg(prodImgUrl(r), r.name) +
        '<div class="prod-card-actions">' +
          '<a class="card-act-btn" href="../product-form/index.html" title="Sửa">' + editIco + '</a>' +
          '<button type="button" class="card-act-btn card-act-btn--danger" data-del="' + r.id + '" title="Lưu trữ">' + delIco + '</button>' +
        '</div>' +
      '</div>' +
      '<div class="prod-card-body">' +
        '<p class="prod-card-name">' + AC.esc(r.name) + '</p>' +
        '<div class="prod-card-meta">' +
          '<span class="prod-card-spu">' +
            '<span class="mono">' + AC.esc(r.spu) + '</span>' +
            '<button class="copy-btn" data-copy="' + AC.esc(r.spu) + '" title="Sao chép SPU">' + copyIco + '</button>' +
          '</span>' +
          '<span class="var-count">' + r.variants + ' SKU</span>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  function renderGridPager(page, pages, total) {
    if (pages <= 1) return "";
    var start = (page - 1) * GRID_PAGE_SIZE;
    var prevSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>';
    var nextSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>';
    var mkBtn = function (label, p, disabled, active) {
      return '<button class="pager-btn' + (active ? " is-active" : "") + '" data-gpage="' + p + '"' + (disabled ? " disabled" : "") + ">" + label + "</button>";
    };
    var btns = mkBtn(prevSvg, page - 1, page === 1, false);
    for (var p = 1; p <= pages; p++) {
      if (pages > 7 && p !== 1 && p !== pages && Math.abs(p - page) > 2) {
        if (p === 2 || p === pages - 1) btns += '<span class="pager-btn" style="pointer-events:none">\u2026</span>';
        continue;
      }
      btns += mkBtn(p, p, false, p === page);
    }
    btns += mkBtn(nextSvg, page + 1, page === pages, false);
    return '<div class="data-foot">' +
      '<div class="data-foot-info"><span>Hiển thị ' + (total ? start + 1 : 0) + '\u2013' + Math.min(start + GRID_PAGE_SIZE, total) + ' / ' + total + '</span></div>' +
      '<div class="pager">' + btns + '</div>' +
    '</div>';
  }

  function renderGrid() {
    var data = gridState.filtered;
    var pages = Math.max(1, Math.ceil(data.length / GRID_PAGE_SIZE));
    if (gridState.page > pages) gridState.page = pages;
    var start = (gridState.page - 1) * GRID_PAGE_SIZE;
    var slice = data.slice(start, start + GRID_PAGE_SIZE);
    var $gv = $("#grid-view");
    if (!data.length) {
      $gv.html('<div class="grid-empty">Không có sản phẩm nào khớp bộ lọc.</div>');
      return;
    }
    $gv.html('<div class="prod-grid">' + slice.map(makeGridCard).join("") + '</div>' + renderGridPager(gridState.page, pages, data.length));
  }

  function getFiltered() {
    return raw.filter(function (r) {
      if (gFilterFn && !gFilterFn(r)) return false;
      if (!term) return true;
      var q = term.toLowerCase();
      return ["name", "spu", "brand"].some(function (k) {
        return String(r[k] || "").toLowerCase().indexOf(q) !== -1;
      });
    });
  }

  /* ── Main init ─────────────────────────────────────────── */
  $(function () {
    gridState.filtered = raw.slice();

    /* DataTable — table view */
    dt = AC.DataTable({
      mount: "#tbl",
      pageSize: 10,
      search: ["name", "spu", "brand"],
      empty: "Không có sản phẩm nào khớp bộ lọc.",
      rowClass: function (r) { return r.unmapped > 0 ? "row-alert" : ""; },
      columns: [
        {
          label: "Sản phẩm (SPU)", sortable: true, sortKey: "name",
          render: function (r) {
            return '<div class="cell-thumb"><div class="thumb">' + jerseyIco + coverImg(prodImgUrl(r), r.name) + '</div>' +
              '<div class="meta"><div class="name">' + AC.highlight(r.name, term) + '</div>' +
              '<div class="sub"><span class="sku master">' + AC.highlight(r.spu, term) + '</span> · ' + AC.esc(r.brand) + '</div></div></div>';
          }
        },
        { label: "Danh mục", render: function (r) { return '<span class="badge">' + AC.esc(r.cat) + '</span>'; } },
        { label: "Biến thể", className: "center", sortable: true, sortKey: "variants", render: function (r) { return '<span class="var-count">' + r.variants + ' SKU</span>'; } },
        { label: "Trạng thái", className: "center", render: function (r) { var s = STATUS[r.status]; return '<span class="pill ' + s.cls + '">' + s.label + '</span>'; } },
        {
          label: "", className: "center",
          render: function (r) {
            return '<div class="data-cell-actions">' +
              '<a class="btn--icon" href="../product-form/index.html" title="Sửa"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/></svg></a>' +
              '<button class="btn--icon" data-del="' + r.id + '" title="Lưu trữ"><svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>' +
            "</div>";
          }
        }
      ],
      rows: raw
    });

    /* Category select — Select2 with tree */
    var $fCat = $("#f-cat");
    $fCat.append(buildCatOptions(AC.categories.tree(), 0));
    $fCat.select2({
      placeholder: "Tất cả danh mục",
      allowClear: true,
      width: "style",
      dropdownAutoWidth: true,
      dropdownCssClass: "ac-filter-drop",
      dropdownParent: $(".filter-bar"),
      templateResult: catTemplateResult
    });

    /* Initial render */
    renderGrid();

    /* View toggle */
    $("#btn-grid").on("click", function () {
      if (currentView === "grid") return;
      currentView = "grid";
      $(this).addClass("is-active"); $("#btn-table").removeClass("is-active");
      $("#tbl").hide(); $("#grid-view").show();
    });
    $("#btn-table").on("click", function () {
      if (currentView === "table") return;
      currentView = "table";
      $(this).addClass("is-active"); $("#btn-grid").removeClass("is-active");
      $("#grid-view").hide(); $("#tbl").show();
    });

    /* Filters */
    function applyFilters() {
      var cat = $fCat.val(), status = $("#f-status").val();
      var active = [cat, status].filter(Boolean).length;
      $("#fcount").toggleClass("hide", !active).text(active + " bộ lọc");
      gFilterFn = (cat || status) ? function (r) { return (!cat || r.cat === cat) && (!status || r.status === status); } : null;
      dt.setFilter(gFilterFn);
      gridState.page = 1;
      gridState.filtered = getFiltered();
      if (currentView === "grid") renderGrid();
    }

    /* Search */
    $("#q").on("input", AC.debounce(function () {
      term = this.value.trim();
      dt.search(term);
      gridState.page = 1; gridState.filtered = getFiltered();
      if (currentView === "grid") renderGrid();
    }, 250));

    $fCat.on("change", applyFilters);
    $("#f-status").on("change", applyFilters);

    /* Reset */
    $("#reset").on("click", function () {
      $("#q").val(""); $fCat.val("").trigger("change"); $("#f-status").val("");
      term = ""; gFilterFn = null;
      dt.search(""); dt.setFilter(null);
      gridState.page = 1; gridState.filtered = raw.slice();
      if (currentView === "grid") renderGrid();
      AC.toast("Đã đặt lại bộ lọc.", "info");
    });

    /* Grid — open modal */
    $("#grid-view").on("click", ".prod-card", function (e) {
      if ($(e.target).closest(".copy-btn, .card-act-btn, .prod-card-actions").length) return;
      var id = Number($(this).data("open-id"));
      var p = raw.find(function (r) { return r.id === id; });
      if (p) openVariantModal(p);
    });

    /* Grid — copy SPU */
    $("#grid-view").on("click", ".copy-btn", function (e) {
      e.stopPropagation();
      copyText($(this).data("copy"));
    });

    /* Grid — edit / archive */
    $("#grid-view").on("click", "a.card-act-btn", function (e) {
      e.stopPropagation();
    });
    $("#grid-view").on("click", ".card-act-btn[data-del]", function (e) {
      e.preventDefault();
      e.stopPropagation();
      archiveProduct(Number($(this).data("del")));
    });

    /* Grid — pagination */
    $("#grid-view").on("click", ".pager-btn[data-gpage]", function () {
      if ($(this).is(":disabled")) return;
      gridState.page = Number($(this).data("gpage"));
      renderGrid();
    });

    /* Table — open modal on row click */
    $("#tbl").on("click", "tbody tr", function (e) {
      if ($(e.target).closest("a, [data-del]").length) return;
      var id = Number($(this).find("[data-del]").data("del"));
      var p = raw.find(function (r) { return r.id === id; });
      if (p) openVariantModal(p);
    });

    /* Table — archive */
    $("#tbl").on("click", "[data-del]", function (e) {
      e.stopPropagation();
      archiveProduct(Number($(this).data("del")));
    });

    /* Import */
    $("#btn-import").on("click", function () {
      AC.modal({
        title: "Import sản phẩm từ Excel",
        body:
          '<p class="muted" style="margin-top:0">Quy trình: tải file mẫu → điền dữ liệu (SPU, tên, danh mục, thuộc tính biến thể) → upload → xem preview → xác nhận.</p>' +
          '<div class="dropzone"><div class="ico"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg></div>' +
          '<p><strong>Chọn file</strong> hoặc kéo thả .xlsx vào đây</p></div>' +
          '<a href="#" class="link" style="display:inline-block;margin-top:12px;font-size:12.5px">\u2193 Tải file mẫu import_products.xlsx</a>',
        buttons: [
          { text: "Huỷ", variant: "secondary" },
          { text: "Bắt đầu import", variant: "primary", onClick: function () { AC.toast("Import: 24 sản phẩm & 142 biến thể thành công.", "success", { title: "Hoàn tất" }); } }
        ]
      });
    });
  });
})(window.jQuery);
