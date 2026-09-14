/* Categories — JS effects only (sortable, modals, search filter). */
(function ($) {
  "use strict";

  if (!$) {
    console.error("[categories] jQuery is required but was not found.");
    return;
  }

  function readConfig() {
    var el = document.getElementById("ssp-categories-config");
    if (!el) return {};
    try {
      return JSON.parse(el.textContent || "{}");
    } catch (e) {
      console.error("[categories] invalid config JSON", e);
      return {};
    }
  }

  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") : "";
  }

  var cfg = readConfig();
  var _sortables = [];
  var slugTouched = false;
  var codeTouched = false;
  var debounce = window.AC && window.AC.debounce
    ? window.AC.debounce
    : function (fn) { return fn; };

  function slugify(text) {
    return String(text || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d")
      .replace(/Đ/g, "D")
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }

  function codeSlug(text) {
    return slugify(text).replace(/-/g, "_");
  }

  function destroySortables() {
    _sortables.forEach(function (s) { s.destroy(); });
    _sortables = [];
  }

  function buildTreeFromDom($container) {
    var result = [];
    $container.children(".tree-node").each(function () {
      var $node = $(this);
      var id = Number($node.data("id"));
      if (!id) return;
      var $children = $node.children(".tree-children").first();
      var node = { id: id };
      if ($children.length) {
        var kids = buildTreeFromDom($children);
        if (kids.length) node.children = kids;
      }
      result.push(node);
    });
    return result;
  }

  function refreshLeafStates() {
    $("#tree .tree-node").each(function () {
      var $node = $(this);
      var $toggle = $node.children(".tree-row").find(".tree-toggle");
      var hasKids = $node.children(".tree-children").children(".tree-node").length > 0;
      $toggle.toggleClass("leaf", !hasKids);
      if (!hasKids) {
        $node.removeClass("is-open");
      }
    });
  }

  function initTreeSortable() {
    if (typeof window.Sortable === "undefined") return;
    destroySortables();

    var root = document.getElementById("tree");
    if (!root || !root.querySelector(".tree-node")) return;

    function isInvalidMove(evt) {
      var dragged = evt.dragged;
      var to = evt.to;
      return !!(dragged && to && dragged.contains(to));
    }

    function mountSortable(el) {
      _sortables.push(window.Sortable.create(el, {
        group: { name: "cat-tree", pull: true, put: true },
        draggable: ".tree-node",
        handle: ".tree-drag-handle",
        animation: 160,
        fallbackOnBody: true,
        swapThreshold: 0.55,
        invertSwap: true,
        emptyInsertThreshold: 16,
        ghostClass: "tree-ghost",
        chosenClass: "tree-chosen",
        dragClass: "tree-dragging",
        onStart: function () { $("#tree").addClass("is-sorting"); },
        onMove: function (evt) { return !isInvalidMove(evt); },
        onEnd: function (evt) {
          $("#tree").removeClass("is-sorting");
          if (!evt.from || !evt.to) return;
          if (evt.from === evt.to && evt.oldIndex === evt.newIndex) return;

          refreshLeafStates();
          var tree = buildTreeFromDom($("#tree"));
          if (!cfg.reorderUrl) return;

          function revertDrag() {
            var item = evt.item;
            var from = evt.from;
            var ref = from.children[evt.oldIndex] || null;
            from.insertBefore(item, ref);
            refreshLeafStates();
          }

          fetch(cfg.reorderUrl, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "X-CSRF-TOKEN": csrfToken(),
            },
            body: JSON.stringify({ tree: tree }),
          })
            .then(function (r) {
              if (!r.ok) throw new Error("reorder failed");
              return r.json();
            })
            .then(function () {
              if (window.AC && window.AC.toast) window.AC.toast("Đã cập nhật vị trí danh mục.", "info");
            })
            .catch(function () {
              revertDrag();
              if (window.AC && window.AC.toast) window.AC.toast("Không lưu được thứ tự danh mục.", "danger");
            });
        },
      }));
    }

    mountSortable(root);
    root.querySelectorAll(".tree-children").forEach(mountSortable);
  }

  function openBackdrop($el) {
    $el.removeAttr("hidden");
    requestAnimationFrame(function () { $el.addClass("show"); });
  }

  function closeBackdrop($el) {
    $el.removeClass("show");
    setTimeout(function () { $el.attr("hidden", "hidden"); }, 220);
  }

  function clearFormErrors($form) {
    $form.find(".modal-form-errors").remove();
    $form.find(".field-errors").remove();
    $form.find(".is-invalid").removeClass("is-invalid");
  }

  function focusFirstInvalid($form) {
    var $target = $form.find(".is-invalid").first();
    if (!$target.length) {
      $target = $form.find(".field-errors").first().closest(".field").find("input, select, textarea").first();
    }
    if ($target.length) {
      setTimeout(function () { $target.trigger("focus"); }, 80);
      return;
    }
    setTimeout(function () { $form.find("input:visible").first().trigger("focus"); }, 80);
  }

  function bindFormErrorClear($form) {
    $form.on("input change", "input, select, textarea", function () {
      var $field = $(this).closest(".field");
      $field.find(".field-errors").remove();
      $(this).removeClass("is-invalid");
      $field.find(".is-invalid").removeClass("is-invalid");
      if (!$form.find(".field-errors").length) {
        $form.find(".modal-form-errors").remove();
      }
    });
  }

  function openCatModal(opts) {
    opts = opts || {};
    var $modal = $("#cat-modal");
    var $form = $("#cat-form");
    var isEdit = !!opts.editId;

    if (!opts.keepErrors) {
      clearFormErrors($form);
    }

    slugTouched = !!opts.slug;
    $("#cat-modal-title").text(opts.title || (isEdit ? "Sửa danh mục" : "Thêm danh mục"));
    $("#cat-modal-submit").text(isEdit ? "Lưu" : "Thêm");
    $("#cat-name").val(opts.name || "");
    $("#cat-slug").val(opts.slug || "");
    $("#cat-parent-id").val(opts.parentId || "");
    $("#cat-entity-id").val(opts.editId || "");
    $("#cat-parent-name-hidden").val(opts.parentName || "");
    $("#cat-form-method").val(isEdit ? "PATCH" : "POST");

    if (opts.parentName) {
      $(".js-cat-parent-note").removeAttr("hidden");
      $("#cat-parent-note-name").text(opts.parentName);
    } else {
      $(".js-cat-parent-note").attr("hidden", "hidden");
      $("#cat-parent-note-name").text("");
    }

    $form.attr("action", isEdit
      ? cfg.updateUrlBase + "/" + opts.editId
      : cfg.storeUrl);

    openBackdrop($modal);
    if (opts.keepErrors) {
      focusFirstInvalid($form);
    } else {
      setTimeout(function () { $("#cat-name").trigger("focus"); }, 80);
    }
  }

  function toValueArray(values) {
    if (Array.isArray(values)) return values;
    if (values == null || values === "") return [];
    return String(values).split(/\r\n|\r|\n|,/).map(function (v) {
      return v.trim();
    }).filter(Boolean);
  }

  function destroyAttrValuesSelect() {
    var $vals = $("#attr-values");
    if ($vals.hasClass("select2-hidden-accessible")) {
      $vals.select2("destroy");
    }
    $vals.empty();
  }

  function initAttrValuesSelect(values) {
    var $vals = $("#attr-values");
    destroyAttrValuesSelect();

    toValueArray(values).forEach(function (v) {
      $vals.append(new Option(v, v, true, true));
    });

    if ($.fn && $.fn.select2) {
      $vals.select2({
        tags: true,
        tokenSeparators: [","],
        placeholder: "Chọn hoặc nhập giá trị mới…",
        width: "100%",
        minimumInputLength: 0,
        dropdownCssClass: "am-s2-drop am-s2-drop--above",
        dropdownParent: $(".am-vals-s2wrap"),
        language: { noResults: function () { return "Nhấn Enter để thêm giá trị mới"; } },
      });
    }
  }

  function openAttrModal(opts) {
    opts = opts || {};
    var $modal = $("#attr-modal");
    var $form = $("#attr-form");
    var isEdit = !!opts.id;

    if (!opts.keepErrors) {
      clearFormErrors($form);
    }

    codeTouched = !!opts.code;
    $("#attr-modal-title").text(isEdit ? "Sửa thuộc tính" : "Tạo thuộc tính mới");
    $("#attr-modal-submit").text(isEdit ? "Lưu" : "Tạo thuộc tính");
    $("#attr-name").val(opts.name || "");
    $("#attr-code").val(opts.code || "").prop("readonly", isEdit);
    $("#attr-entity-id").val(opts.id || "");
    $("#attr-form-method").val(isEdit ? "PATCH" : "POST");
    $form.attr("action", isEdit ? cfg.attrUpdateUrlBase + "/" + opts.id : cfg.attrStoreUrl);

    initAttrValuesSelect(opts.values);

    openBackdrop($modal);
    if (opts.keepErrors) {
      focusFirstInvalid($form);
    } else {
      setTimeout(function () { $("#attr-name").trigger("focus"); }, 80);
    }
  }

  function filterAttrs() {
    var q = $("#attr-search").val().trim().toLowerCase();
    var visible = 0;
    $("#attr-table-body tr[data-search]").each(function () {
      var match = !q || String($(this).data("search")).indexOf(q) >= 0;
      $(this).toggleClass("is-hidden", !match);
      if (match) visible++;
    });
    $("#attr-no-results").toggleClass("is-hidden", visible > 0 || !q);
  }

  $(function () {
    initTreeSortable();
    refreshLeafStates();
    bindFormErrorClear($("#cat-form"));
    bindFormErrorClear($("#attr-form"));

    $("#tree").on("click", ".tree-toggle:not(.leaf)", function (e) {
      e.stopPropagation();
      $(this).closest(".tree-node").toggleClass("is-open");
    });

    $("#tree").on("click", ".tree-row", function (e) {
      if ($(e.target).closest(".tree-actions,.tree-toggle,.tree-drag-handle,form").length) return;
      $("#tree .tree-row").removeClass("is-active");
      $(this).addClass("is-active");
    });

    $("#btn-add-cat").on("click", function () {
      openCatModal({ title: "Thêm danh mục gốc" });
    });

    $("#tree").on("click", ".js-cat-add-child", function (e) {
      e.stopPropagation();
      openCatModal({
        title: "Thêm danh mục con",
        parentId: $(this).data("id"),
        parentName: $(this).data("name"),
      });
    });

    $("#tree").on("click", ".js-cat-edit", function (e) {
      e.stopPropagation();
      openCatModal({
        title: "Sửa danh mục",
        editId: $(this).data("id"),
        name: $(this).data("name"),
        slug: $(this).data("slug"),
      });
    });

    $(".js-cat-modal-close").on("click", function () {
      closeBackdrop($("#cat-modal"));
    });

    $("#cat-modal").on("mousedown", function (e) {
      if (e.target === this) closeBackdrop($("#cat-modal"));
    });

    $("#cat-slug").on("input", function () { slugTouched = true; });
    $("#cat-name").on("input", function () {
      if (!slugTouched) $("#cat-slug").val(slugify(this.value));
    });

    $("#cat-form").on("submit", function () {
      if (!$("#cat-slug").val().trim()) {
        $("#cat-slug").val(slugify($("#cat-name").val()));
      }
    });

    $(".js-cat-delete-form").on("submit", function (e) {
      var name = $(this).find("[data-name]").data("name") || "danh mục này";
      if (!window.confirm('Xoá "' + name + '"? Sản phẩm thuộc danh mục này sẽ được gỡ phân loại (không bị xoá).')) {
        e.preventDefault();
      }
    });

    $("#btn-add-attr").on("click", function () {
      openAttrModal({});
    });

    $("#attr-list").on("click", ".js-attr-edit", function () {
      var $tr = $(this).closest("tr");
      var vals = $tr.data("attr-values") || $tr.data("attrValues") || [];
      openAttrModal({
        id: $tr.data("attr-id"),
        name: $tr.data("attr-name"),
        code: $tr.data("attr-code"),
        values: vals,
      });
    });

    function closeAttrModal() {
      destroyAttrValuesSelect();
      closeBackdrop($("#attr-modal"));
    }

    $(".js-attr-modal-close").on("click", closeAttrModal);

    $("#attr-modal").on("mousedown", function (e) {
      if (e.target === this) closeAttrModal();
    });

    $("#attr-code").on("input", function () { codeTouched = true; });
    $("#attr-name").on("input", function () {
      if (!codeTouched && !$("#attr-code").prop("readonly")) {
        $("#attr-code").val(codeSlug(this.value));
      }
    });

    $(".js-attr-delete-form").on("submit", function (e) {
      var name = $(this).find("[data-name]").data("name") || "thuộc tính này";
      if (!window.confirm('Xóa "' + name + '"?')) e.preventDefault();
    });

    $("#attr-search").on("input", debounce(filterAttrs, 180));

    if (cfg.openModal === "category") {
      openCatModal({
        title: cfg.old && cfg.old.editId ? "Sửa danh mục" : "Thêm danh mục",
        editId: cfg.old && cfg.old.editId,
        name: cfg.old && cfg.old.name,
        slug: cfg.old && cfg.old.slug,
        parentId: cfg.old && cfg.old.parentId,
        parentName: cfg.old && cfg.old.parentName,
        keepErrors: true,
      });
    } else if (cfg.openModal === "attribute") {
      openAttrModal({
        id: cfg.old && cfg.old.editId,
        name: cfg.old && cfg.old.name,
        code: cfg.old && cfg.old.code,
        values: cfg.old && cfg.old.values,
        keepErrors: true,
      });
    }
  });
})(window.jQuery);
