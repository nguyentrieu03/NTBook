/* Product form — unified attribute values + variant matrix generator. */
(function ($) {
    "use strict";
  
    var COLORS = ["var(--primary)", "var(--purple)", "var(--success)", "var(--orange)", "var(--info)", "var(--pink)"];
  
    /**
     * Product-scoped attribute selection (= product_attributes + product_attribute_values).
     * `selected` maps normalized value -> canonical display string from the library.
     */
    var attrs = [];
    var variants = [];
  
    // Album ảnh sản phẩm — dùng chung bởi renderThumbs và openImagePicker
    var imgs = [];
    var _imgCounter = 0;
    var _thumbSortable = null;   // SortableJS instance cho #thumbs
  
    function attrMeta(code) {
      var lib = AC.catalog.get(code);
      return lib ? { code: lib.code, name: lib.name } : { code: code, name: code };
    }
  
    function isSelected(attr, value) {
      return Object.prototype.hasOwnProperty.call(attr.selected, AC.catalog.norm(value));
    }
  
    function setSelected(attr, value, on) {
      var n = AC.catalog.norm(value);
      if (on) attr.selected[n] = value;
      else delete attr.selected[n];
    }
  
    function selectedValues(attr) {
      return Object.keys(attr.selected).map(function (k) { return attr.selected[k]; });
    }
  
    function selectedCount(attr) {
      return Object.keys(attr.selected).length;
    }
  
    /* ---- Render attribute groups (unified: chips + select2 tags) ---- */
    function renderAttrs() {
      $(".val-picker").each(function () {
        if ($(this).hasClass("select2-hidden-accessible")) $(this).select2("destroy");
      });
  
      if (!attrs.length) {
        $("#attr-groups").empty();
        return;
      }
  
      var html = attrs.map(function (a, gi) {
        var meta = attrMeta(a.code);
        var color = COLORS[gi % COLORS.length];
        var libVals = AC.catalog.values(a.code);
        var chips = libVals.map(function (v, vi) {
          return '<span class="val-chip' + (isSelected(a, v) ? " on" : "") + '" data-g="' + gi + '" data-vi="' + vi + '">' + AC.esc(v) + "</span>";
        }).join("");
        var picked = selectedCount(a);
        return '<div class="attr-group" data-group="' + gi + '">' +
          '<div class="attr-group-head"><div class="attr-group-name"><span class="dot" style="background:' + color + '"></span>' +
          AC.esc(meta.name) + ' <span class="muted" style="font-weight:400">(' + AC.esc(meta.code) + ')</span>' +
          '<span class="attr-count">' + picked + " giá trị dùng</span></div>" +
          '<span class="attr-remove" data-rm-attr="' + gi + '">Xoá thuộc tính</span></div>' +
          '<div class="chips">' + (chips || '<span class="attr-no-val">Chưa có giá trị trong thư viện — thêm bên dưới.</span>') + "</div>" +
          '<div class="val-picker-wrap"><select class="val-picker" data-g="' + gi + '" data-code="' + AC.esc(meta.code) + '"></select></div></div>';
      }).join("");
  
      $("#attr-groups").html(html);
      initValuePickers();
    }
  
    function initValuePickers() {
      $(".val-picker").each(function () {
        var $el = $(this);
        var gi = Number($el.data("g"));
        var code = $el.data("code");
        var suggestions = AC.catalog.values(code);
        $el.empty().append('<option></option>');
        suggestions.forEach(function (v) {
          $el.append(new Option(v, v, false, false));
        });
        $el.select2({
          width: "100%",
          tags: true,
          allowClear: true,
          placeholder: "Tìm hoặc thêm giá trị…",
          language: {
            noResults: function () { return "Nhập để thêm giá trị mới"; }
          },
          createTag: function (params) {
            var term = $.trim(params.term);
            if (!term) return null;
            return { id: term, text: term, newTag: true };
          }
        }).on("select2:select", function (e) {
          var val = String(e.params.data.id || e.params.data.text).trim();
          if (!val) return;
          var attr = attrs[gi];
          var res = AC.catalog.firstOrCreateValue(code, val);
          if (!res) return;
          if (isSelected(attr, res.value)) {
            AC.toast('Giá trị "' + res.value + '" đã được chọn.', "info");
          } else {
            setSelected(attr, res.value, true);
            AC.toast(res.created ? ('Đã thêm "' + res.value + '" vào thư viện và chọn.') :
              ('Đã chọn "' + res.value + '".'), "success");
          }
          $el.val(null).trigger("change");
          renderAttrs();
        });
      });
    }
  
    /* ---- Attribute picker (select2) — choose attribute from library ---- */
    function renderPicker() {
      var used = {};
      attrs.forEach(function (a) { used[a.code] = true; });
      var library = AC.catalog.list();
      var opts = '<option></option>' + library.filter(function (l) { return !used[l.code]; }).map(function (l) {
        return '<option value="' + l.code + '">' + AC.esc(l.name) + " (" + l.code + ")</option>";
      }).join("");
      var $p = $("#attr-picker");
      if ($p.hasClass("select2-hidden-accessible")) $p.select2("destroy");
      var remaining = library.length - attrs.length;
      $p.html(opts).prop("disabled", remaining <= 0).select2({
        width: "100%",
        allowClear: false,
        placeholder: remaining > 0 ? "Thêm thuộc tính từ thư viện…" : "Đã dùng hết thuộc tính trong thư viện",
        language: { noResults: function () { return "Không có thuộc tính phù hợp"; } }
      });
    }
  
    /* ---- Tạo thuộc tính mới ngay tại màn sản phẩm (modal dùng chung AC.openAttrModal) ---- */
  
    /* ---- Cartesian product of selected attribute values ---- */
    function cartesian() {
      var axes = attrs.map(function (a) {
        var meta = attrMeta(a.code);
        return { code: a.code, name: meta.name, vals: selectedValues(a) };
      }).filter(function (ax) { return ax.vals.length; });
      if (!axes.length) return [];
      var combos = [[]];
      axes.forEach(function (ax) {
        var next = [];
        combos.forEach(function (combo) {
          ax.vals.forEach(function (v) { next.push(combo.concat([{ code: ax.code, name: ax.name, v: v }])); });
        });
        combos = next;
      });
      return combos;
    }
  
    function skuFor(combo) {
      var base = ($("#p-spu").val() || "SPU").toUpperCase().replace(/[^A-Z0-9]+/g, "-");
      return "SPU-" + base.replace(/^SPU-/, "") + "-" + combo.map(function (c) { return String(c.v).toUpperCase().replace(/\s+/g, ""); }).join("-");
    }
  
    function generate() {
      var combos = cartesian();
      if (!combos.length) { AC.toast("Hãy chọn ít nhất một giá trị thuộc tính.", "warning"); return; }
      var prev = {};
      variants.forEach(function (v) { prev[v.key] = v; });
      variants = combos.map(function (combo) {
        var key = combo.map(function (c) { return c.code + ":" + c.v; }).join("|");
        var old = prev[key];
        return {
          key: key,
          combo: combo,
          sku: old ? old.sku : skuFor(combo),
          cost: old ? old.cost : "",
          price: old ? old.price : "",
          barcode: old ? old.barcode : "",
          on: old ? old.on : true,
          image: old ? old.image : null   // null = dùng fallback ảnh chính album
        };
      });
      renderVariants();
      AC.toast("Đã sinh " + variants.length + " biến thể từ thuộc tính.", "success");
    }
  
    /* ---- Album + variant image helpers (all at module scope) ---- */
    // imgs = [{ id, src, name, isPrimary }]  src = FileReader dataURL
    var _STAR_ICO = '<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    var _RM_ICO = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    var _PH_SVG = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>';
  
    function albumPrimary() {
      return imgs.filter(function (im) { return im.isPrimary; })[0] || imgs[0] || null;
    }
  
    var _DRAG_ICO = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg>';
  
    function renderThumbs() {
      if (_thumbSortable) { _thumbSortable.destroy(); _thumbSortable = null; }
      if (!imgs.length) { $("#thumbs").empty(); return; }
      var hasPrimary = imgs.filter(function (im) { return im.isPrimary; }).length;
      if (!hasPrimary) imgs[0].isPrimary = true;
  
      $("#thumbs").html(imgs.map(function (im, i) {
        return '<div class="thumb-item' + (im.isPrimary ? " is-main" : "") + '" data-ti="' + i + '">' +
          '<img class="thumb-img" src="' + im.src + '" alt="' + AC.esc(im.name) + '">' +
          (im.isPrimary ? '<span class="thumb-badge">Ảnh chính</span>' : '') +
          '<span class="thumb-drag-handle" title="Kéo để sắp xếp">' + _DRAG_ICO + '</span>' +
          '<div class="thumb-overlay">' +
            (!im.isPrimary ? '<button class="tov-btn" data-set-primary="' + i + '" title="Đặt làm ảnh chính">' + _STAR_ICO + '</button>' : '') +
            '<button class="tov-btn tov-danger" data-rm-img="' + i + '" title="Xoá ảnh">' + _RM_ICO + '</button>' +
          '</div>' +
        '</div>';
      }).join(""));
  
      _thumbSortable = Sortable.create(document.getElementById("thumbs"), {
        animation: 160,
        ghostClass: "thumb-ghost",
        chosenClass: "thumb-chosen",
        handle: ".thumb-drag-handle",   // chỉ kéo qua handle, click thường không drag
        onEnd: function (evt) {
          if (evt.oldIndex === evt.newIndex) return;
          var moved = imgs.splice(evt.oldIndex, 1)[0];
          imgs.splice(evt.newIndex, 0, moved);
          renderThumbs();   // re-render để cập nhật data-ti + primary badge đúng vị trí
        }
      });
  
      refreshVariantImages();
    }
  
    // Đọc danh sách File objects, push vào imgs[] qua FileReader
    function readImageFiles(files) {
      var fileArr = Array.prototype.slice.call(files);
      // Xác định trước bao nhiêu ảnh hiện tại để biết ảnh đầu tiên có cần isPrimary
      var alreadyHasPrimary = imgs.length > 0;
      var firstNew = true;
      fileArr.forEach(function (file) {
        if (!file.type.match(/^image\//)) {
          AC.toast('"' + file.name + '" không phải ảnh, bỏ qua.', "warning");
          return;
        }
        if (file.size > 5 * 1024 * 1024) {
          AC.toast('"' + file.name + '" vượt 5MB, bỏ qua.', "warning");
          return;
        }
        var makePrimary = !alreadyHasPrimary && firstNew;
        if (makePrimary) { alreadyHasPrimary = true; firstNew = false; }
        var reader = new FileReader();
        reader.onload = function (e) {
          _imgCounter++;
          imgs.push({ id: "img-" + _imgCounter, src: e.target.result, name: file.name, isPrimary: makePrimary });
          renderThumbs();
        };
        reader.readAsDataURL(file);
      });
    }
  
    function varImgCellHtml(v, i) {
      var im = v.image || albumPrimary();
      if (!im) {
        return '<div class="vi-cell vi-ph" data-vimg="' + i + '" title="Click để gán ảnh">' + _PH_SVG + '</div>';
      }
      var isOwn = !!v.image;
      return '<div class="vi-cell ' + (isOwn ? 'vi-own' : 'vi-fallback') + '" data-vimg="' + i + '" title="' + (isOwn ? 'Ảnh riêng — click để đổi' : 'Dùng ảnh chính — click để gán riêng') + '">' +
        '<img class="vi-img" src="' + im.src + '" alt="">' +
        '<span class="vi-badge">' + (isOwn ? '✓' : '↑') + '</span>' +
      '</div>';
    }
  
    function refreshVariantImages() {
      variants.forEach(function (v, i) {
        var $cell = $('#var-wrap td.vi-td[data-row="' + i + '"]');
        if ($cell.length) $cell.html(varImgCellHtml(v, i));
      });
    }
  
    function openImagePicker(varIdx) {
      if (!imgs.length) { AC.toast("Hãy upload ảnh vào album sản phẩm trước.", "warning"); return; }
      var _picked = variants[varIdx].image;
  
      var gridHtml = '<p class="img-picker-help">Chọn ảnh từ album. Nếu không chọn riêng, hệ thống tự lấy ảnh chính của sản phẩm (↑).</p>' +
        '<div class="img-picker-grid">' +
        imgs.map(function (im, pi) {
          var isSel = variants[varIdx].image === im;
          return '<div class="img-picker-item' + (isSel ? ' is-sel' : '') + '" data-pick="' + pi + '">' +
            '<img class="img-picker-thumb" src="' + im.src + '" alt="' + AC.esc(im.name) + '">' +
            (im.isPrimary ? '<span class="img-picker-lbl">Ảnh chính</span>' : '') +
          '</div>';
        }).join('') +
        '</div>';
  
      AC.modal({
        title: 'Gán ảnh đại diện cho biến thể',
        body: gridHtml,
        buttons: [
          { text: 'Dùng ảnh chung', variant: 'ghost', onClick: function () {
            variants[varIdx].image = null;
            refreshVariantImages();
            AC.toast('Đã đặt về ảnh chính album.', 'info');
          }},
          { text: 'Xác nhận', variant: 'primary', onClick: function () {
            variants[varIdx].image = _picked;
            refreshVariantImages();
            if (_picked) AC.toast('Đã gán ảnh riêng cho biến thể.', 'success');
          }}
        ],
        onReady: function (modalApi) {
          modalApi.$body.on('click', '.img-picker-item', function () {
            _picked = imgs[Number($(this).data('pick'))];
            modalApi.$body.find('.img-picker-item').removeClass('is-sel');
            $(this).addClass('is-sel');
          });
        }
      });
    }
  
    function renderVariants() {
      if (!variants.length) {
        $("#var-wrap").html('<div class="var-empty"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>' +
          '<div>Chưa có biến thể. Chọn thuộc tính rồi bấm <strong>Sinh biến thể</strong>.</div></div>');
        $("#var-summary").text("Chưa có biến thể nào.");
        $("#btn-bulk-price").addClass("hide");
        updateSide();
        return;
      }
      var head = "<thead><tr><th style='width:52px' class='center'>Ảnh</th><th>Biến thể</th><th>SKU chuẩn</th><th class=\"pf-hidden\">Giá vốn</th><th class=\"pf-hidden\">Giá bán ($)</th><th>Barcode / UPC</th><th class=\"center\">Bán</th><th></th></tr></thead>";
      var body = variants.map(function (v, i) {
        var label = v.combo.map(function (c) {
          var gi = attrs.findIndex(function (a) { return a.code === c.code; });
          return '<span class="v-opt"><span class="v-dot" style="background:' + COLORS[gi % COLORS.length] + '"></span>' + AC.esc(c.name) + ': <strong>' + AC.esc(c.v) + "</strong></span>";
        }).join("");
        return '<tr data-i="' + i + '">' +
          '<td class="center vi-td" data-row="' + i + '">' + varImgCellHtml(v, i) + '</td>' +
          '<td><div class="stack g6">' + label + "</div></td>" +
          '<td><input class="input mono" data-f="sku" value="' + AC.esc(v.sku) + '" style="min-width:180px"></td>' +
          '<td class="pf-hidden"><input class="input" data-f="cost" type="number" min="0" step="0.01" value="' + AC.esc(v.cost) + '" placeholder="0.00" style="width:100px"></td>' +
          '<td class="pf-hidden"><input class="input" data-f="price" type="number" min="0" step="0.01" value="' + AC.esc(v.price) + '" placeholder="0.00" style="width:100px"></td>' +
          '<td><input class="input mono" data-f="barcode" value="' + AC.esc(v.barcode) + '" placeholder="—" style="width:140px"></td>' +
          '<td class="center"><label class="switch"><input type="checkbox" data-f="on"' + (v.on ? " checked" : "") + '><span class="track"></span></label></td>' +
          '<td class="center"><span class="v-rm" data-rm="' + i + '" title="Xoá"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></span></td>' +
          "</tr>";
      }).join("");
      $("#var-wrap").html('<table class="var-matrix">' + head + "<tbody>" + body + "</tbody></table>");
      $("#var-summary").text(variants.length + " biến thể · " + variants.filter(function (v) { return v.on; }).length + " đang bán");
      $("#btn-bulk-price").removeClass("hide");
      updateSide();
    }
  
    function updateSide() {
      $("#s-var").text(variants.length);
      var prices = variants.map(function (v) { return parseFloat(v.price); }).filter(function (n) { return !isNaN(n) && n > 0; });
      $("#s-price").text(prices.length ? (AC.usd(Math.min.apply(null, prices)) + " – " + AC.usd(Math.max.apply(null, prices))) : "—");
    }
  
    function slugify(s) {
      return s.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/gi, "d")
        .toUpperCase().replace(/[^A-Z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 24);
    }
  
    $(function () {
      renderAttrs();
      renderPicker();
      renderVariants();
  
      var spuTouched = false;
      $("#p-spu").on("input", function () { spuTouched = true; });
      $("#p-name").on("input", AC.debounce(function () {
        if (!spuTouched) $("#p-spu").val(slugify(this.value));
      }, 200));
  
      // Toggle value on/off (= product_attribute_values pivot only)
      $("#attr-groups").on("click", ".val-chip", function () {
        var gi = Number($(this).data("g")), vi = Number($(this).data("vi"));
        var attr = attrs[gi];
        var val = AC.catalog.values(attr.code)[vi];
        if (val == null) return;
        setSelected(attr, val, !isSelected(attr, val));
        renderAttrs();
      });
  
      $("#attr-groups").on("click", "[data-rm-attr]", function () {
        attrs.splice(Number($(this).data("rm-attr")), 1);
        renderAttrs();
        renderPicker();
      });
  
      $(document).on("select2:select", "#attr-picker", function (e) {
        var lib = AC.catalog.get(e.params.data.id);
        if (!lib) return;
        attrs.push({ code: lib.code, selected: {} });
        renderAttrs();
        renderPicker();
        AC.toast('Đã thêm thuộc tính "' + lib.name + '". Chọn hoặc thêm giá trị bên dưới.', "success");
      });
  
      $("#btn-gen").on("click", generate);
      $("#btn-new-attr").on("click", function () {
        AC.openAttrModal({
          onSaved: function (code) {
            attrs.push({ code: code, selected: {} });
            renderAttrs();
            renderPicker();
            AC.toast('Đã thêm thuộc tính vào sản phẩm. Chọn giá trị bên dưới.', "info");
          }
        });
      });
  
      $("#var-wrap").on("input change", "[data-f]", function () {
        var i = $(this).closest("tr").data("i"), f = $(this).data("f");
        variants[i][f] = f === "on" ? this.checked : this.value;
        if (f === "price") updateSide();
        if (f === "on") $("#var-summary").text(variants.length + " biến thể · " + variants.filter(function (v) { return v.on; }).length + " đang bán");
      });
  
      $("#var-wrap").on("click", "[data-rm]", function () {
        variants.splice(Number($(this).data("rm")), 1);
        renderVariants();
      });
  
      $("#btn-bulk-price").on("click", function () {
        AC.modal({
          title: "Đặt giá hàng loạt",
          body: '<div class="field"><label class="field-label">Giá bán áp cho tất cả biến thể ($)</label><input class="input" id="bulk-price" type="number" min="0" step="0.01" placeholder="49.90"></div>',
          buttons: [
            { text: "Huỷ", variant: "secondary" },
            { text: "Áp dụng", variant: "primary", onClick: function () {
              var p = $("#bulk-price").val();
              if (!p) { AC.toast("Nhập giá.", "warning"); return false; }
              variants.forEach(function (v) { v.price = p; });
              renderVariants();
              AC.toast("Đã áp giá " + AC.usd(p) + " cho " + variants.length + " biến thể.", "success");
            } }
          ]
        });
      });
  
      // Album events — click mở file picker
      $("#dz").on("click", function () { $("#dz-input").trigger("click"); });
  
      $("#dz-input").on("change", function () {
        if (this.files && this.files.length) readImageFiles(this.files);
        this.value = ""; // Reset để chọn lại cùng file vẫn trigger change
      });
  
      // Drag & drop
      $("#dz").on("dragover dragenter", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass("dragover");
      }).on("dragleave dragend drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");
        if (e.type === "drop" && e.originalEvent.dataTransfer.files.length) {
          readImageFiles(e.originalEvent.dataTransfer.files);
        }
      });
  
      $("#thumbs").on("click", "[data-set-primary]", function (e) {
        e.stopPropagation();
        var idx = Number($(this).data("set-primary"));
        imgs.forEach(function (im) { im.isPrimary = false; });
        imgs[idx].isPrimary = true;
        renderThumbs();
        AC.toast("Đã đặt làm ảnh chính.", "success");
      });
  
      $("#thumbs").on("click", "[data-rm-img]", function (e) {
        e.stopPropagation();
        var idx = Number($(this).data("rm-img"));
        var wasMain = imgs[idx].isPrimary;
        var removed = imgs.splice(idx, 1)[0];
        if (wasMain && imgs.length) imgs[0].isPrimary = true;
        variants.forEach(function (v) { if (v.image === removed) v.image = null; });
        renderThumbs();
      });
  
      // Variant image column — open picker on click
      $("#var-wrap").on("click", "[data-vimg]", function () {
        openImagePicker(Number($(this).data("vimg")));
      });
  
      function validate() {
        var ok = true;
        if (!$("#p-name").val().trim()) { $("#p-name").addClass("is-invalid"); ok = false; } else $("#p-name").removeClass("is-invalid");
        if (!$("#p-spu").val().trim()) { $("#p-spu").addClass("is-invalid"); ok = false; } else $("#p-spu").removeClass("is-invalid");
        return ok;
      }
      $("#btn-save").on("click", function () {
        if (!validate()) { AC.toast("Vui lòng điền các trường bắt buộc.", "danger"); return; }
        if (!variants.length) { AC.toast("Chưa có biến thể nào — hãy sinh biến thể trước khi lưu.", "warning"); return; }
        AC.toast("Đã lưu sản phẩm với " + variants.length + " biến thể (SKU chuẩn).", "success", { title: "Thành công" });
      });
      $("#btn-draft").on("click", function () { AC.toast("Đã lưu nháp.", "info"); });
      $("#btn-cancel").on("click", function () { window.location.href = "../products/index.html"; });
    });
  })(window.jQuery);
  