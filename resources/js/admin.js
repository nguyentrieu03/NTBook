/*!
 * admin.js — Shared runtime for the eBay Catalog & Order Management screens (screens2).
 *
 * Responsibilities
 *  - Inject the admin shell (sidebar / topbar / footer) reusing the theme's
 *    native classes (style.css) so every screen stays visually consistent.
 *  - Expose a small, dependency-light helper API on `window.AC` (toast, modal,
 *    confirm, drawer, money formatting, debounce, DataTable, ...).
 *
 * Depends on: jQuery 3.7+ and the theme stylesheet (style.css) + admin.css.
 * Screens declare their context via <body data-active="..." data-crumbs="A | B">.
 *
 * Domain note: this system unifies products/SKUs across many eBay accounts.
 *   Canonical layer  -> products / product_variants (master SKU)
 *   Channel layer    -> marketplace_accounts / listings / listing_items
 *   Transaction layer-> orders / order_items
 */
(function (window, $) {
  "use strict";

  if (!$) {
    console.error("[admin] jQuery is required but was not found.");
    return;
  }

  /* ------------------------------------------------------------------ *
   * Navigation model — single source of truth for the admin sidebar.
   * `href` values resolve relative to /template/admin/screens2/<slug>/.
   * ------------------------------------------------------------------ */
  var NAV = [
    {
      label: "Tổng quan",
      items: [
        { key: "dashboard", text: "Dashboard", href: "dashboard", icon: '<path d="M3 12 12 3l9 9"/><path d="M5 10v10h14V10"/>' }
      ]
    },
    {
      label: "Catalog",
      items: [
        { key: "products", text: "Sản phẩm", href: "products", icon: '<path d="M20 7 12 3 4 7l8 4 8-4z"/><path d="M4 7v10l8 4 8-4V7"/><path d="M12 11v10"/>' },
        { key: "product-form", text: "Thêm / Sửa sản phẩm", href: "product-form", icon: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/>' },
        { key: "categories", text: "Danh mục & Thuộc tính", href: "categories", icon: '<path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-7L10 4H5a2 2 0 0 0-2 2z"/>' }
      ]
    },
    // {
    //   label: "Kênh eBay",
    //   items: [
    //     { key: "accounts", text: "Tài khoản eBay", href: "accounts", icon: '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>' },
    //     { key: "listings", text: "Đối soát Listing / SKU", href: "listings", badge: { kind: "hot", text: "48" }, icon: '<path d="M4 6h16M4 12h16M4 18h10"/><circle cx="19" cy="18" r="2.4"/>' }
    //   ]
    // },
    // {
    //   label: "Bán hàng",
    //   items: [
    //     { key: "orders", text: "Đơn hàng", href: "orders", icon: '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>' },
    //     { key: "analytics", text: "Thống kê bán chạy", href: "dashboard", icon: '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-4 4"/>' }
    //   ]
    // },
    // {
    //   label: "Hệ thống",
    //   items: [
    //     { key: "users", text: "Người dùng & Phân quyền", href: "users", icon: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/>' }
    //   ]
    // }
  ];

  var BRAND_LOGO =
    '<svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M6 10h9l3 3 3-3h9v6l-3 3 3 3v2H6v-2l3-3-3-3z" opacity=".9"/><circle cx="18" cy="18" r="3.2" fill="#fff"/></svg>';

  /* ------------------------------------------------------------------ *
   * Utility helpers
   * ------------------------------------------------------------------ */
  var USD = new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" });
  var NUM = new Intl.NumberFormat("en-US");

  var AC = {
    /** Format a number as USD. usd(19.9) -> "$19.90" */
    usd: function (n) { return USD.format(Number(n) || 0); },
    /** Format a plain number with thousand separators. */
    number: function (n) { return NUM.format(Number(n) || 0); },
    /** Debounce a function by `wait` ms (default 300). */
    debounce: function (fn, wait) {
      var t;
      wait = wait == null ? 300 : wait;
      return function () {
        var ctx = this, args = arguments;
        clearTimeout(t);
        t = setTimeout(function () { fn.apply(ctx, args); }, wait);
      };
    },
    /** Escape a string for safe HTML interpolation. */
    esc: function (s) {
      return String(s == null ? "" : s)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
    },
    /** Highlight occurrences of `term` inside `text` (returns safe HTML). */
    highlight: function (text, term) {
      var safe = AC.esc(text);
      if (!term) return safe;
      try {
        return safe.replace(new RegExp("(" + term.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + ")", "gi"), '<mark class="hl">$1</mark>');
      } catch (e) { return safe; }
    },
    /** Deterministic gradient class (grad-1..6) from a seed string. */
    gradClass: function (seed) {
      var s = String(seed || ""), h = 0;
      for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
      return "grad-" + ((h % 6) + 1);
    },
    /** Initials from a name/handle: "Nguyễn Văn A" -> "NA". */
    initials: function (name) {
      var parts = String(name || "").trim().split(/\s+/);
      if (!parts[0]) return "?";
      if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
  };

  /* ---- Toast notifications ---------------------------------------- */
  var ICONS = {
    success: '<path d="M20 6 9 17l-5-5"/>',
    danger: '<circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>',
    warning: '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    primary: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>'
  };

  AC.toast = function (message, type, opts) {
    type = type || "success";
    opts = opts || {};
    var $host = $("#ac-toasts");
    if (!$host.length) $host = $('<div id="ac-toasts" class="ac-toast-host"></div>').appendTo("body");
    var $t = $(
      '<div class="ac-toast ' + type + '" role="status">' +
        '<span class="ac-toast-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' + (ICONS[type] || ICONS.info) + "</svg></span>" +
        '<div class="ac-toast-body">' +
          (opts.title ? '<div class="ac-toast-title">' + AC.esc(opts.title) + "</div>" : "") +
          '<div class="ac-toast-text">' + AC.esc(message) + "</div>" +
        "</div>" +
        '<button class="ac-toast-x" aria-label="Đóng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>' +
      "</div>"
    ).appendTo($host);
    requestAnimationFrame(function () { $t.addClass("show"); });
    var close = function () {
      $t.removeClass("show");
      setTimeout(function () { $t.remove(); }, 260);
    };
    $t.find(".ac-toast-x").on("click", close);
    if (opts.duration !== 0) setTimeout(close, opts.duration || 3600);
    return { close: close };
  };

  /* ---- Modal / confirm -------------------------------------------- */
  AC.modal = function (opts) {
    opts = opts || {};
    var $back = $(
      '<div class="ac-modal-backdrop">' +
        '<div class="ac-modal ' + (opts.size || "") + '" role="dialog" aria-modal="true">' +
          '<div class="modal-head"><div class="modal-title">' + AC.esc(opts.title || "") + "</div>" +
            '<button class="icon-btn ac-modal-x" aria-label="Đóng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>' +
          "</div>" +
          '<div class="modal-body">' + (opts.body || "") + "</div>" +
          '<div class="modal-foot"></div>' +
        "</div>" +
      "</div>"
    ).appendTo("body");

    var api = {
      $el: $back,
      $body: $back.find(".modal-body"),
      close: function () {
        $back.removeClass("show");
        setTimeout(function () { $back.remove(); }, 220);
      }
    };

    var $foot = $back.find(".modal-foot");
    (opts.buttons || [{ text: "Đóng", variant: "secondary", close: true }]).forEach(function (b) {
      var $b = $('<button class="btn btn--' + (b.variant || "secondary") + '">' + AC.esc(b.text) + "</button>");
      $b.on("click", function () {
        var keepOpen = b.onClick && b.onClick(api) === false;
        if (b.close !== false && !keepOpen) api.close();
      });
      $foot.append($b);
    });

    $back.on("mousedown", function (e) { if (e.target === $back[0] && opts.dismissible !== false) api.close(); });
    $back.find(".ac-modal-x").on("click", api.close);
    requestAnimationFrame(function () { $back.addClass("show"); });
    if (opts.onReady) opts.onReady(api);
    return api;
  };

  AC.confirm = function (opts) {
    opts = opts || {};
    return AC.modal({
      title: opts.title || "Xác nhận",
      body: '<p style="margin:0;color:var(--t-muted);line-height:1.6;white-space:pre-line">' + AC.esc(opts.message || "Bạn có chắc chắn?") + "</p>",
      buttons: [
        { text: opts.cancelText || "Huỷ", variant: "secondary" },
        { text: opts.okText || "Đồng ý", variant: opts.danger ? "danger" : "primary", onClick: function (m) { if (opts.onOk) opts.onOk(m); } }
      ]
    });
  };

  /* ---- Slide-over drawer (right) ---------------------------------- */
  AC.drawer = function (opts) {
    opts = opts || {};
    var $back = $(
      '<div class="ac-drawer-backdrop">' +
        '<aside class="ac-drawer" role="dialog" aria-modal="true">' +
          '<div class="ac-drawer-head"><h3>' + AC.esc(opts.title || "") + "</h3>" +
            '<button class="icon-btn ac-drawer-x" aria-label="Đóng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>' +
          "</div>" +
          '<div class="ac-drawer-body">' + (opts.body || "") + "</div>" +
          (opts.footer ? '<div class="ac-drawer-foot">' + opts.footer + "</div>" : "") +
        "</aside>" +
      "</div>"
    ).appendTo("body");
    var api = {
      $el: $back,
      $body: $back.find(".ac-drawer-body"),
      $foot: $back.find(".ac-drawer-foot"),
      close: function () { $back.removeClass("show"); setTimeout(function () { $back.remove(); }, 300); }
    };
    $back.on("mousedown", function (e) { if (e.target === $back[0]) api.close(); });
    $back.find(".ac-drawer-x").on("click", api.close);
    requestAnimationFrame(function () { $back.addClass("show"); });
    if (opts.onReady) opts.onReady(api);
    return api;
  };

  /* ------------------------------------------------------------------ *
   * Shell rendering
   * ------------------------------------------------------------------ */
  function crumbsHtml(str) {
    var parts = String(str || "").split("|").map(function (s) { return s.trim(); }).filter(Boolean);
    return parts.map(function (p, i) {
      var sep = i > 0 ? '<svg class="sep" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>' : "";
      return sep + "<span" + (i === parts.length - 1 ? ' class="current"' : "") + ">" + AC.esc(p) + "</span>";
    }).join("");
  }

  function sidebarHtml(active) {
    var sections = NAV.map(function (sec) {
      var links = sec.items.map(function (it) {
        var cls = "nav-link" + (it.key === active ? " is-active" : "");
        var badge = it.badge ? '<span class="nav-badge ' + it.badge.kind + '">' + it.badge.text + "</span>" : "";
        return '<a class="' + cls + '" href="../' + it.href + '/index.html">' +
          '<svg viewBox="0 0 24 24">' + it.icon + "</svg><span>" + it.text + "</span>" + badge + "</a>";
      }).join("");
      return '<nav class="nav-section"><div class="nav-label">' + sec.label + "</div>" + links + "</nav>";
    }).join("");

    return '<aside class="d-sidebar">' +
      '<div class="brand"><div class="brand-logo">' + BRAND_LOGO + "</div>" +
        '<div class="brand-text"><div class="brand-name">SKU Hub</div><div class="brand-tag">eBay · omnichannel</div></div></div>' +
      sections +
      '<div class="sidebar-footer"><div class="workspace">' +
        '<div class="workspace-avatar">QA</div>' +
        '<div class="workspace-text"><div class="workspace-name">Quản trị viên</div><div class="workspace-role">Toàn quyền · 24 acc</div></div>' +
        '<svg class="workspace-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m7 9 5-5 5 5"/><path d="m7 15 5 5 5-5"/></svg>' +
      "</div></div></aside>";
  }

  function topbarHtml(crumbs) {
    return '<header class="d-topbar">' +
      '<div class="crumbs">' +
        '<button class="hamburger" data-drawer-open aria-label="Mở menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>' +
        crumbsHtml(crumbs) +
      "</div>" +
      '<div class="topbar-actions">' +
        '<div class="ac-acc-switch"><label class="select-wrap"><select class="select" id="ac-acc-select" aria-label="Chọn tài khoản eBay">' +
          '<option>Tất cả tài khoản (24)</option><option>us-sport-jerseys</option><option>usa-fan-store</option><option>topgear-outlet</option><option>jersey-empire-us</option>' +
        "</select></label></div>" +
        '<button class="icon-btn" data-ac-search aria-label="Tìm kiếm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></button>' +
        '<div class="dd-wrap"><button class="icon-btn" data-dropdown aria-label="Thông báo"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="count danger">5</span></button>' +
          '<div class="dd-menu" role="menu"><div class="dd-head"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>Thông báo</div>' +
          '<div class="dd-list">' +
            '<a class="dd-item" href="../listings/index.html"><div class="dd-avatar a3">!</div><div class="dd-body"><div class="dd-text"><strong>48 listing</strong> chưa map SKU chuẩn</div><div class="dd-time">3 PHÚT TRƯỚC</div></div></a>' +
            '<a class="dd-item" href="../orders/index.html"><div class="dd-avatar a1">₪</div><div class="dd-body"><div class="dd-text"><strong>32 đơn mới</strong> vừa kéo từ eBay</div><div class="dd-time">18 PHÚT TRƯỚC</div></div></a>' +
            '<a class="dd-item" href="../accounts/index.html"><div class="dd-avatar a2">✓</div><div class="dd-body"><div class="dd-text"><strong>topgear-outlet</strong> đã đồng bộ token</div><div class="dd-time">1 GIỜ TRƯỚC</div></div></a>' +
          "</div><a class=\"dd-footer\" href=\"../listings/index.html\">Xem tất cả →</a></div></div>" +
        '<button class="icon-btn" id="themeToggle" aria-label="Đổi giao diện"></button>' +
        '<div class="dd-wrap"><div class="avatar" data-dropdown tabindex="0" role="button">QA</div>' +
          '<div class="dd-menu dd-profile" role="menu"><div class="dd-profile-head"><div class="dd-profile-name">Quản trị viên</div><div class="dd-profile-email">admin@skuhub.io</div></div>' +
          '<a class="dd-menu-item" href="../users/index.html"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Hồ sơ</a>' +
          '<a class="dd-menu-item" href="../users/index.html"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Phân quyền</a>' +
          '<div class="dd-divider"></div>' +
          '<a class="dd-menu-item danger" href="../../signin.html"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>Đăng xuất</a>' +
          "</div></div>" +
      "</div></header>";
  }

  function footerHtml() {
    return '<footer class="d-footer"><div>© 2026 · SKU Hub — eBay Catalog & Order Management</div>' +
      '<div class="d-footer-meta"><span>Laravel API</span><span>jQuery ' + $.fn.jquery + "</span></div></footer>";
  }

  /* ---- Theme toggle ------------------------------------------------ */
  function initTheme() {
    var root = document.documentElement;
    var $btn = $("#themeToggle");
    if (!$btn.length) return;
    var sun = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>';
    var moon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>';
    var paint = function () { $btn.html(root.getAttribute("data-theme") === "dark" ? sun : moon); };
    paint();
    $btn.on("click", function () {
      var next = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
      root.setAttribute("data-theme", next);
      try { localStorage.setItem("dash26-theme", next); } catch (e) {}
      paint();
      $(document).trigger("ac:themechange", next);
    });
  }

  /* ---- Interaction wiring (delegated, works after shell render) ---- */
  function initInteractions() {
    var $body = $("body");

    if (!$(".drawer-backdrop").length) $('<div class="drawer-backdrop" aria-hidden="true"></div>').appendTo($body);
    $(document).on("click", "[data-drawer-open]", function (e) { e.preventDefault(); $body.addClass("has-drawer-open"); });
    $(document).on("click", ".drawer-backdrop", function () { $body.removeClass("has-drawer-open"); });
    $(document).on("click", ".d-sidebar a[href]", function () { $body.removeClass("has-drawer-open"); });
    $(document).on("keydown", function (e) { if (e.key === "Escape") $body.removeClass("has-drawer-open"); });

    $(document).on("click", "[data-dropdown]", function (e) {
      e.stopPropagation();
      var $w = $(this).closest(".dd-wrap");
      var open = !$w.hasClass("is-open");
      $(".dd-wrap.is-open").removeClass("is-open");
      $w.toggleClass("is-open", open);
    });
    $(document).on("click", function (e) { if (!$(e.target).closest(".dd-wrap").length) $(".dd-wrap.is-open").removeClass("is-open"); });

    // Tabs (data-tab-group / .tab[data-tab-target] / .tab-panel[data-tab-id])
    $(document).on("click", "[data-tab-group] .tab", function (e) {
      e.preventDefault();
      var $tab = $(this), $group = $tab.closest("[data-tab-group]");
      var target = $tab.data("tab-target");
      $group.find(".tab").removeClass("is-active");
      $tab.addClass("is-active");
      var scope = $group.data("tab-scope");
      var $panels = scope ? $(scope) : $group.find(".tab-panel");
      $panels.removeClass("is-active").filter('[data-tab-id="' + target + '"]').addClass("is-active");
      $group.trigger("ac:tabchange", target);
    });

    $(document).on("click", "[data-accordion-trigger]", function () {
      $(this).closest("[data-accordion-item]").toggleClass("is-open");
    });

    $(document).on("click", "[data-dismiss]", function () {
      $(this).closest($(this).data("dismiss")).fadeOut(160, function () { $(this).remove(); });
    });

    $(document).on("click", "[data-ac-search]", function () {
      AC.toast("Tìm kiếm toàn cục (⌘K) — sẽ kết nối API khi tích hợp backend.", "info");
    });
  }

  AC.renderShell = function () {
    var active = document.body.getAttribute("data-active") || "";
    var crumbs = document.body.getAttribute("data-crumbs") || "";
    var $sb = $("[data-shell-sidebar]"), $tb = $("[data-shell-topbar]"), $ft = $("[data-shell-footer]");
    if ($sb.length && !$(".d-sidebar").length) $sb.replaceWith(sidebarHtml(active));
    if ($tb.length && !$(".d-topbar").length) $tb.replaceWith(topbarHtml(crumbs));
    if ($ft.length && !$(".d-footer").length) $ft.replaceWith(footerHtml());
    initTheme();
    initInteractions();
  };

  /* ------------------------------------------------------------------ *
   * AC.DataTable — lightweight client-side table with sort, search,
   * filtering and pagination. Renders into a container using the theme's
   * .data-table / .data-foot / .pager markup.
   *
   * opts = {
   *   mount, columns:[{key,label,className,sortable,sortKey,render(row)}],
   *   rows, pageSize, search:['field'], empty, rowClass(row)
   * }
   * ------------------------------------------------------------------ */
  AC.DataTable = function (opts) {
    var $mount = $(opts.mount);
    var cols = opts.columns;
    var all = (opts.rows || []).slice();
    var pageSize = opts.pageSize || 10;
    var state = { page: 1, sortKey: null, sortDir: 1, term: "", filter: null };

    var thead = "<thead><tr>" + cols.map(function (c, i) {
      var cls = (c.className || "") + (c.sortable ? " is-sortable" : "");
      var sort = c.sortable ? '<span class="sort"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></span>' : "";
      return '<th class="' + cls + '" data-col="' + i + '">' + AC.esc(c.label) + sort + "</th>";
    }).join("") + "</tr></thead>";

    $mount.html(
      '<div class="table-scroll"><table class="data-table">' + thead + "<tbody></tbody></table></div>" +
      '<div class="data-foot"><div class="data-foot-info"><span class="ac-dt-summary"></span>' +
        '<label class="select-wrap"><select class="select ac-dt-size">' +
          "<option>10</option><option>25</option><option>50</option></select></label></div>" +
      '<div class="pager ac-dt-pager"></div></div>'
    );
    var $tbody = $mount.find("tbody");
    var $summary = $mount.find(".ac-dt-summary");
    var $pager = $mount.find(".ac-dt-pager");

    function matchesSearch(row) {
      if (!state.term) return true;
      var q = state.term.toLowerCase();
      return (opts.search || []).some(function (k) {
        return String(row[k] == null ? "" : row[k]).toLowerCase().indexOf(q) !== -1;
      });
    }

    function view() {
      var data = all.filter(function (r) { return matchesSearch(r) && (!state.filter || state.filter(r)); });
      if (state.sortKey) {
        data.sort(function (a, b) {
          var av = a[state.sortKey], bv = b[state.sortKey];
          if (typeof av === "string") return av.localeCompare(bv, "vi") * state.sortDir;
          return ((av > bv ? 1 : av < bv ? -1 : 0)) * state.sortDir;
        });
      }
      return data;
    }

    function render() {
      var data = view();
      var pages = Math.max(1, Math.ceil(data.length / pageSize));
      if (state.page > pages) state.page = pages;
      var startI = (state.page - 1) * pageSize;
      var slice = data.slice(startI, startI + pageSize);

      if (!slice.length) {
        $tbody.html('<tr><td colspan="' + cols.length + '" style="padding:40px;text-align:center;color:var(--t-muted)">' +
          AC.esc(opts.empty || "Không tìm thấy dữ liệu phù hợp") + "</td></tr>");
      } else {
        $tbody.html(slice.map(function (row) {
          var rc = opts.rowClass ? opts.rowClass(row) : "";
          return '<tr' + (rc ? ' class="' + rc + '"' : "") + ">" + cols.map(function (c) {
            var val = c.render ? c.render(row) : AC.esc(row[c.key]);
            return '<td class="' + (c.className || "") + '">' + val + "</td>";
          }).join("") + "</tr>";
        }).join(""));
      }

      $summary.text("Hiển thị " + (data.length ? startI + 1 : 0) + "–" + Math.min(startI + pageSize, data.length) + " / " + data.length);

      var btns = "";
      var mkBtn = function (label, page, opt) {
        opt = opt || {};
        return '<button class="pager-btn' + (opt.active ? " is-active" : "") + '" data-page="' + page + '"' +
          (opt.disabled ? " disabled" : "") + ">" + label + "</button>";
      };
      btns += mkBtn('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>', state.page - 1, { disabled: state.page === 1 });
      for (var p = 1; p <= pages; p++) {
        if (pages > 7 && (p > 2 && p < pages - 1) && Math.abs(p - state.page) > 1) {
          if (p === 3 || p === pages - 2) btns += '<span class="pager-btn" style="pointer-events:none">…</span>';
          continue;
        }
        btns += mkBtn(p, p, { active: p === state.page });
      }
      btns += mkBtn('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>', state.page + 1, { disabled: state.page === pages });
      $pager.html(btns);
    }

    $mount.on("click", "th.is-sortable", function () {
      var c = cols[$(this).data("col")];
      var key = c.sortKey || c.key;
      if (state.sortKey === key) state.sortDir *= -1; else { state.sortKey = key; state.sortDir = 1; }
      $mount.find("th").removeClass("sorted-asc sorted-desc");
      $(this).addClass(state.sortDir === 1 ? "sorted-asc" : "sorted-desc");
      render();
    });
    $mount.on("click", ".pager-btn[data-page]", function () {
      var p = Number($(this).data("page"));
      if (!$(this).is(":disabled") && p) { state.page = p; render(); }
    });
    $mount.on("change", ".ac-dt-size", function () { pageSize = Number(this.value); state.page = 1; render(); });

    render();

    return {
      search: function (term) { state.term = term || ""; state.page = 1; render(); },
      setFilter: function (fn) { state.filter = fn; state.page = 1; render(); },
      setRows: function (rows) { all = (rows || []).slice(); state.page = 1; render(); },
      refresh: render,
      count: function () { return view().length; }
    };
  };

  /* ------------------------------------------------------------------ *
   * Catalog mock — shared `attributes` + `attribute_values` library.
   * Mirrors the original DB schema (no extra columns). Both the
   * categories screen and product-form read/write through this API.
   * ------------------------------------------------------------------ */
  var CATALOG_KEY = "ac-catalog-library-v1";
  var CATALOG_DEFAULT = [
    { name: "Kích cỡ", code: "size", values: ["S", "M", "L", "XL", "2XL", "3XL"] },
    { name: "Số áo", code: "player_number", values: ["7", "9", "10", "11", "23", "30"] },
    { name: "Màu sắc", code: "color", values: ["Trắng", "Đen", "Xanh", "Đỏ", "Vàng"] },
    { name: "Phiên bản", code: "version", values: ["Fan", "Player", "Retro"] },
    { name: "Kiểu tay", code: "sleeve", values: ["Ngắn tay", "Dài tay"] }
  ];

  AC.catalog = {
    _attrs: null,

    _load: function () {
      if (this._attrs) return this._attrs;
      try {
        var raw = localStorage.getItem(CATALOG_KEY);
        this._attrs = raw ? JSON.parse(raw) : JSON.parse(JSON.stringify(CATALOG_DEFAULT));
      } catch (e) {
        this._attrs = JSON.parse(JSON.stringify(CATALOG_DEFAULT));
      }
      return this._attrs;
    },

    _save: function () {
      try { localStorage.setItem(CATALOG_KEY, JSON.stringify(this._attrs)); } catch (e) { /* noop */ }
    },

    /** Normalize for duplicate checks (simulates slug comparison). */
    norm: function (s) { return String(s || "").trim().toLowerCase(); },

    /** All attributes (= `attributes` table rows). */
    list: function () { return this._load().slice(); },

    get: function (code) {
      return this._load().filter(function (a) { return a.code === code; })[0] || null;
    },

    /** Values for one attribute (= `attribute_values`). */
    values: function (code) {
      var a = this.get(code);
      return a ? a.values.slice() : [];
    },

    addAttribute: function (name, code) {
      var attrs = this._load();
      if (attrs.some(function (a) { return a.code === code; })) return false;
      attrs.push({ name: name, code: code, values: [] });
      this._save();
      return true;
    },

    removeAttribute: function (code) {
      var attrs = this._load(), i = attrs.findIndex(function (a) { return a.code === code; });
      if (i < 0) return false;
      attrs.splice(i, 1);
      this._save();
      return true;
    },

    /** firstOrCreate on `attribute_values`, keyed by normalized value. */
    firstOrCreateValue: function (code, value) {
      var a = this.get(code), v = String(value || "").trim();
      if (!a || !v) return null;
      var hit = a.values.filter(function (x) { return AC.catalog.norm(x) === AC.catalog.norm(v); })[0];
      if (hit) return { value: hit, created: false };
      a.values.push(v);
      this._save();
      return { value: v, created: true };
    },

    removeValue: function (code, value) {
      var a = this.get(code);
      if (!a) return false;
      var n = AC.catalog.norm(value), i = a.values.findIndex(function (x) { return AC.catalog.norm(x) === n; });
      if (i < 0) return false;
      a.values.splice(i, 1);
      this._save();
      return true;
    },

    hasValue: function (code, value) {
      var a = this.get(code);
      if (!a) return false;
      var n = AC.catalog.norm(value);
      return a.values.some(function (x) { return AC.catalog.norm(x) === n; });
    },

    codeSlug: function (s) {
      return String(s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/gi, "d").toLowerCase()
        .replace(/[^a-z0-9]+/g, "_").replace(/^_+|_+$/g, "").slice(0, 32);
    },

    /** Ghi đè danh sách giá trị — dedupe không phân biệt hoa/thường trong cùng attribute. */
    setValues: function (code, values) {
      var a = this.get(code);
      if (!a) return false;
      var seen = {}, out = [];
      (values || []).forEach(function (v) {
        v = String(v || "").trim();
        if (!v) return;
        var n = AC.catalog.norm(v);
        if (seen[n]) return;
        seen[n] = true;
        out.push(v);
      });
      a.values = out;
      this._save();
      return true;
    },

    updateAttribute: function (code, name, values) {
      var a = this.get(code);
      if (!a) return false;
      a.name = String(name || "").trim();
      if (!a.name) return false;
      return this.setValues(code, values);
    },

    /** Demo: số biến thể đang dùng giá trị (maps tới variant_attribute_values). */
    _USAGE_DEMO: {
      "size|m": 24, "size|l": 18, "size|xl": 12, "size|2xl": 6,
      "player_number|9": 8, "player_number|10": 5, "player_number|23": 3,
      "color|vàng": 4, "color|trắng": 9, "version|fan": 15
    },

    valueUsage: function (code, value) {
      var key = code + "|" + this.norm(value);
      return this._USAGE_DEMO[key] || 0;
    },

    attributeUsage: function (code) {
      var a = this.get(code), total = 0, self = this;
      if (!a) return 0;
      a.values.forEach(function (v) { total += self.valueUsage(code, v); });
      return total;
    },

    valuesInUse: function (code) {
      var a = this.get(code), self = this;
      if (!a) return [];
      return a.values.filter(function (v) { return self.valueUsage(code, v) > 0; });
    }
  };

  /* Gợi ý giá trị phổ biến theo code thuộc tính — hiển thị sẵn trong dropdown (không cần gõ). */
  var VALUE_SUGGESTIONS = {
    size: ["S", "M", "L", "XL", "2XL", "3XL", "4XL", "5XL", "Free size"],
    player_number: ["1", "3", "4", "6", "7", "8", "9", "10", "11", "17", "23", "30", "99"],
    color: ["Trắng", "Đen", "Xanh", "Đỏ", "Vàng", "Xám", "Xanh lá", "Cam", "Tím", "Hồng", "Nâu"],
    version: ["Fan", "Player", "Retro", "Authentic", "Replica"],
    sleeve: ["Ngắn tay", "Dài tay", "Ba lỗ"],
    material: ["Cotton", "Polyester", "Thun lạnh", "Nỉ", "Kate"]
  };

  var VALUE_SUGGESTION_ALIASES = {
    kich_co: "size",
    so_ao: "player_number",
    mau_sac: "color",
    phien_ban: "version",
    kieu_tay: "sleeve",
    chat_lieu: "material"
  };

  function resolveSuggestionKey(code, name) {
    code = String(code || "").trim();
    if (VALUE_SUGGESTIONS[code]) return code;
    var alias = VALUE_SUGGESTION_ALIASES[code.toLowerCase()];
    if (alias) return alias;
    var hit = AC.catalog.get(code);
    if (hit && VALUE_SUGGESTIONS[hit.code]) return hit.code;
    name = String(name || "").trim().toLowerCase();
    if (name) {
      var byName = AC.catalog.list().filter(function (a) {
        return a.name.toLowerCase() === name || AC.catalog.codeSlug(a.name) === AC.catalog.codeSlug(name);
      })[0];
      if (byName && VALUE_SUGGESTIONS[byName.code]) return byName.code;
    }
    return code;
  }

  /**
   * Modal tạo/sửa thuộc tính — dùng chung màn Danh mục & Product form.
   * opts.code  → edit mode (code không đổi)
   * opts.onSaved(code) → callback sau khi lưu
   */
  AC.openAttrModal = function (opts) {
    opts = opts || {};
    var isEdit = !!opts.code;
    var existing = isEdit ? AC.catalog.get(opts.code) : null;
    if (isEdit && !existing) return;

    var usageHint = "";
    if (isEdit && existing) {
      var inUse = AC.catalog.valuesInUse(existing.code);
      var total = AC.catalog.attributeUsage(existing.code);
      if (inUse.length) {
        usageHint = '<div class="alert info" style="margin-bottom:0">' +
          '<div class="ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg></div>' +
          '<div class="body"><strong>' + inUse.length + ' giá trị</strong> đang được <strong>' + total + ' biến thể</strong> sử dụng. ' +
          'Xóa khỏi thư viện không xóa biến thể cũ, nhưng không chọn được cho sản phẩm mới.</div></div>';
      }
    }

    var createHint = "";
    if (!isEdit) {
      createHint = '<div class="alert info" style="margin-bottom:0">' +
        '<div class="ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg></div>' +
        '<div class="body">Nhập <strong>tên thuộc tính</strong> (VD: Kích cỡ, Màu sắc) — gợi ý giá trị tự lọc theo loại. Bấm ô <strong>Giá trị trong thư viện</strong> để chọn hoặc Enter để thêm mới.</div></div>';
    }

    var topHint = usageHint || createHint;
    var presetName = opts.name || "";
    var presetCode = opts.code || "";

    var body =
      (topHint ? topHint + '<div style="height:12px"></div>' : "") +
      '<div class="form-grid">' +
        '<div class="field span-2">' +
          '<label class="field-label">Tên thuộc tính <span class="req">*</span></label>' +
          '<input class="input" id="am-name" type="text" placeholder="VD: Kích cỡ, Màu sắc, Chất liệu…" value="' + AC.esc(existing ? existing.name : presetName) + '">' +
        '</div>' +
        '<div class="field span-2">' +
          '<label class="field-label">Mã code <span class="req">*</span></label>' +
          '<input class="input mono" id="am-code" type="text" placeholder="size" value="' + AC.esc(existing ? existing.code : presetCode) + '"' + (isEdit ? " disabled" : "") + '>' +
          '<span class="field-help">' + (isEdit ? "Mã code không đổi sau khi tạo." : "Tự sinh từ tên · unique toàn hệ thống.") + '</span>' +
        '</div>' +
        '<div class="field span-2 am-vals-field">' +
          '<label class="field-label">Giá trị trong thư viện</label>' +
          '<div class="am-vals-s2wrap"><select id="am-vals" multiple></select></div>' +
          '<div id="am-usage-tags" class="am-usage-hint"></div>' +
          '<span class="field-help">Bấm để chọn từ gợi ý, hoặc nhập rồi Enter để thêm giá trị mới. Trùng trong cùng thuộc tính thì gộp (không phân biệt hoa/thường).</span>' +
        '</div>' +
      '</div>';

    function doSave(m, code, name, vals, force) {
      if (isEdit && existing && !force) {
        var removed = existing.values.filter(function (v) {
          return !vals.some(function (nv) { return AC.catalog.norm(nv) === AC.catalog.norm(v); });
        });
        var risky = removed.filter(function (v) { return AC.catalog.valueUsage(code, v) > 0; });
        if (risky.length) {
          var msg = risky.map(function (v) {
            return "• " + v + " (" + AC.catalog.valueUsage(code, v) + " biến thể)";
          }).join("\n");
          AC.confirm({
            title: "Xóa giá trị đang được dùng?",
            message: "Các giá trị sau vẫn còn biến thể sản phẩm:\n" + msg + "\n\nBiến thể cũ giữ nguyên, nhưng không gán được cho sản phẩm mới. Tiếp tục?",
            okText: "Vẫn lưu", danger: true,
            onOk: function () { doSave(m, code, name, vals, true); }
          });
          return false;
        }
      }

      if (isEdit) AC.catalog.updateAttribute(code, name, vals);
      else {
        if (!AC.catalog.addAttribute(name, code)) { AC.toast("Không thể tạo thuộc tính.", "danger"); return false; }
        AC.catalog.setValues(code, vals);
      }

      m.close();
      AC.toast((isEdit ? "Đã cập nhật" : "Đã tạo") + ' thuộc tính "' + name + '".', "success");
      if (opts.onSaved) opts.onSaved(code);
      return true;
    }

    AC.modal({
      title: isEdit ? "Sửa thuộc tính" : "Tạo thuộc tính mới",
      size: "wide",
      body: body,
      buttons: [
        { text: "Huỷ", variant: "secondary" },
        { text: isEdit ? "Lưu" : "Tạo thuộc tính", variant: "primary", close: false, onClick: function (m) {
          var name = m.$body.find("#am-name").val().trim();
          var code = m.$body.find("#am-code").val().trim();
          var vals = m.$body.find("#am-vals").val() || [];

          if (!name) { m.$body.find("#am-name").addClass("is-invalid").trigger("focus"); AC.toast("Nhập tên thuộc tính.", "warning"); return false; }
          if (!isEdit && !code) { m.$body.find("#am-code").addClass("is-invalid").trigger("focus"); AC.toast("Nhập mã code.", "warning"); return false; }
          if (!isEdit && AC.catalog.get(code)) { AC.toast('Mã "' + code + '" đã tồn tại.', "danger"); return false; }

          return doSave(m, code, name, vals, false);
        }}
      ],
      onReady: function (m) {
        var attrCode = existing ? existing.code : null;
        var $vals = m.$body.find("#am-vals");
        var $code = m.$body.find("#am-code");

        function selectedNormMap() {
          var map = {};
          ($vals.val() || []).forEach(function (v) { map[AC.catalog.norm(v)] = true; });
          return map;
        }

        /** Nạp / cập nhật gợi ý theo code — dùng chung cho tạo mới & sửa. */
        function syncValueSuggestions(code, name) {
          code = String(code || "").trim();
          name = String(name || "").trim();
          var key = resolveSuggestionKey(code, name);
          var selNorm = selectedNormMap();
          var list = VALUE_SUGGESTIONS[key] || [];

          $vals.find("option:not(:selected)").each(function () {
            var n = AC.catalog.norm(this.value);
            var keep = list.some(function (v) { return AC.catalog.norm(v) === n; });
            if (!keep) $(this).remove();
          });

          list.forEach(function (v) {
            if (selNorm[AC.catalog.norm(v)]) return;
            var exists = false;
            $vals.find("option").each(function () {
              if (AC.catalog.norm(this.value) === AC.catalog.norm(v)) exists = true;
            });
            if (!exists) $vals.append(new Option(v, v, false, false));
          });
        }

        (existing ? existing.values : (opts.values || [])).forEach(function (v) {
          $vals.append(new Option(v, v, true, true));
        });
        syncValueSuggestions(attrCode || $code.val().trim(), m.$body.find("#am-name").val().trim());

        function refreshChoiceStyles() {
          if (!attrCode || !$.fn.select2) return;
          var $box = $vals.next(".select2-container");
          $box.find(".select2-selection__choice").each(function () {
            var $c = $(this);
            var val = ($c.attr("title") || $c.find(".select2-selection__choice__display").text() || "").trim();
            var usage = AC.catalog.valueUsage(attrCode, val);
            $c.toggleClass("in-use", usage > 0);
          });
        }

        function refreshUsageTags() {
          var $hint = m.$body.find("#am-usage-tags");
          if (!attrCode) { $hint.empty(); return; }
          var inUse = AC.catalog.valuesInUse(attrCode);
          if (!inUse.length) { $hint.empty(); return; }
          $hint.html(
            '<span class="lbl">Đang dùng:</span>' +
            inUse.map(function (v) {
              var n = AC.catalog.valueUsage(attrCode, v);
              return '<span class="u-badge">' + AC.esc(v) + ' · <em>' + n + "</em></span>";
            }).join("")
          );
        }

        if ($.fn.select2) {
          var $s2Wrap = m.$body.find(".am-vals-s2wrap");

          $vals.select2({
            tags: true,
            tokenSeparators: [","],
            placeholder: "Chọn từ gợi ý hoặc nhập giá trị mới…",
            width: "100%",
            minimumInputLength: 0,
            dropdownCssClass: "am-s2-drop am-s2-drop--above",
            dropdownParent: $s2Wrap,
            language: { noResults: function () { return "Nhấn Enter để thêm giá trị mới"; } },
            templateSelection: function (data) {
              return data.id || data.text;
            }
          });

          $vals.on("select2:opening", function () {
            if (!isEdit) {
              syncValueSuggestions($code.val().trim(), m.$body.find("#am-name").val().trim());
            }
          });

          $vals.on("change", function () {
            refreshChoiceStyles();
            refreshUsageTags();
          });
          refreshChoiceStyles();
          refreshUsageTags();

          if (isEdit && attrCode) {
            $vals.on("select2:unselecting", function (e) {
              var val = e.params.args.data.id;
              var usage = AC.catalog.valueUsage(attrCode, val);
              if (usage <= 0) return;
              e.preventDefault();
              AC.confirm({
                title: "Xóa giá trị đang được dùng?",
                message: '"' + val + '" đang có ' + usage + " biến thể sản phẩm.\n\nBiến thể cũ giữ nguyên, nhưng không chọn được cho sản phẩm mới. Xóa khỏi thư viện?",
                okText: "Vẫn xóa", danger: true,
                onOk: function () {
                  var cur = ($vals.val() || []).filter(function (x) {
                    return AC.catalog.norm(x) !== AC.catalog.norm(val);
                  });
                  $vals.val(cur).trigger("change");
                }
              });
            });
          }
        }

        if (!isEdit) {
          var codeTouched = false;
          $code.on("input", function () {
            codeTouched = true;
            syncValueSuggestions(this.value.trim(), m.$body.find("#am-name").val().trim());
          });
          m.$body.find("#am-name").on("input", function () {
            m.$body.find("#am-name, #am-code").removeClass("is-invalid");
            if (!codeTouched) {
              var slug = AC.catalog.codeSlug(this.value);
              $code.val(slug);
              syncValueSuggestions(slug, this.value.trim());
            } else {
              syncValueSuggestions($code.val().trim(), this.value.trim());
            }
          });
        }

        setTimeout(function () { m.$body.find("#am-name").trigger("focus"); }, 80);
      }
    });
  };

  /* ------------------------------------------------------------------ *
   * Categories mock — nested tree stored in localStorage.
   * Mirrors `categories` table: parent_id via nesting, sort_order via drag.
   * ------------------------------------------------------------------ */
  var CATEGORIES_KEY = "ac-categories-tree-v1";
  var CATEGORIES_DEFAULT = [
    { id: "cat-1", name: "Áo bóng đá", slug: "ao-bong-da", count: 642, open: true, children: [
      { id: "cat-1-1", name: "Câu lạc bộ", slug: "cau-lac-bo", count: 410, children: [] },
      { id: "cat-1-2", name: "Đội tuyển quốc gia", slug: "doi-tuyen-quoc-gia", count: 232, children: [] }
    ] },
    { id: "cat-2", name: "Áo bóng rổ (NBA)", slug: "ao-bong-ro-nba", count: 318, children: [] },
    { id: "cat-3", name: "Áo bóng chày (MLB)", slug: "ao-bong-chay-mlb", count: 156, children: [] },
    { id: "cat-4", name: "Áo retro", slug: "ao-retro", count: 124, open: true, children: [
      { id: "cat-4-1", name: "Thập niên 90", slug: "thap-nien-90", count: 68, children: [] },
      { id: "cat-4-2", name: "Thập niên 2000", slug: "thap-nien-2000", count: 56, children: [] }
    ] },
    { id: "cat-5", name: "Phụ kiện", slug: "phu-kien", count: 44, children: [] }
  ];

  AC.categories = {
    _tree: null,

    _load: function () {
      if (this._tree) return this._tree;
      try {
        var raw = localStorage.getItem(CATEGORIES_KEY);
        this._tree = raw ? JSON.parse(raw) : JSON.parse(JSON.stringify(CATEGORIES_DEFAULT));
      } catch (e) {
        this._tree = JSON.parse(JSON.stringify(CATEGORIES_DEFAULT));
      }
      return this._tree;
    },

    _save: function () {
      try { localStorage.setItem(CATEGORIES_KEY, JSON.stringify(this._tree)); } catch (e) { /* ignore */ }
    },

    tree: function () { return this._load(); },

    setTree: function (tree) {
      this._tree = tree || [];
      this._save();
    },

    slugify: function (s) {
      return String(s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/gi, "d").toLowerCase()
        .replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 64);
    },

    /** Trả slug unique; nếu trùng thì tự thêm -2, -3, ... */
    uniqueSlug: function (baseSlug, existsFn) {
      var base = this.slugify(baseSlug);
      if (!base) return { slug: "", adjusted: false, base: "" };
      if (!existsFn(base)) return { slug: base, adjusted: false, base: base };
      var n = 2, candidate;
      while (n < 1000) {
        candidate = base + "-" + n;
        if (!existsFn(candidate)) return { slug: candidate, adjusted: true, base: base };
        n++;
      }
      return { slug: base + "-" + Date.now().toString(36).slice(-4), adjusted: true, base: base };
    },

    nextId: function () {
      return "cat-" + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
    }
  };

  // Expose
  window.AC = AC;
  window.AC.NAV = NAV;

  $(function () {
    if ($("[data-shell-sidebar],[data-shell-topbar],[data-shell-footer]").length) {
      AC.renderShell();
    } else if ($(".d-sidebar, .d-topbar, .d-footer").length) {
      initTheme();
      initInteractions();
    }
  });
})(window, window.jQuery);
