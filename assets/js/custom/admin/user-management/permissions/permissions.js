"use strict";

(function () {
  const qs = (sel, root = document) => root.querySelector(sel);

  const BASE = (window.EMR?.baseUrl || "/").replace(/\/?$/, "/");
  const csrf = () => qs('input[name="_token"]')?.value || "";

  const SEL = {
    table: "#kt_permissions_table",
    search: '[data-kt-permissions-table-filter="search"]',

    syncBtn: "#kt_permissions_sync_btn",

    modalAdd: "#kt_modal_add_permission",
    formAdd: "#kt_modal_add_permission_form",
  };

  // Kolom tabel (sesuai markup list.php model baru)
  // 0 Name | 1 Assigned to | 2 Created Date
  const COL = { NAME: 0, ASSIGNED: 1, CREATED: 2 };

  const API = {
    list: `${BASE}apps/admin/pages/permissions/api/list-permissions.php`,
    create: `${BASE}apps/admin/pages/permissions/api/create-permissions.php`,
    sync: `${BASE}apps/admin/pages/permissions/api/sync-registry.php`,
  };

  const swalOk = (title, text) =>
    Swal.fire({
      icon: "success",
      title,
      text,
      buttonsStyling: false,
      confirmButtonText: "OK",
      customClass: { confirmButton: "btn btn-primary" },
    });

  const swalErr = (title, text) =>
    Swal.fire({
      icon: "error",
      title,
      text,
      buttonsStyling: false,
      confirmButtonText: "OK",
      customClass: { confirmButton: "btn btn-primary" },
    });

  function disableSubmit(form, loading = true, labelIdle = "Submit") {
    const btn = form?.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.disabled = loading;
    if (loading) btn.setAttribute("data-kt-indicator", "on");
    else btn.removeAttribute("data-kt-indicator");
    btn.innerText = loading ? "Please wait..." : labelIdle;
  }

  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function renderAssignedTo(roles) {
    if (!roles) return "";
    if (typeof roles === "string") return escapeHtml(roles);

    if (Array.isArray(roles)) {
      if (roles.length === 0) return `<span class="text-muted">-</span>`;
      return roles
        .map((r) => {
          const name = escapeHtml(r.name ?? "");
          return `<span class="badge badge-light-primary fs-7 m-1">${name}</span>`;
        })
        .join("");
    }

    return escapeHtml(String(roles));
  }

  async function apiJson(url, options = {}) {
    const res = await fetch(url, options);
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("API returned non-JSON:", {
        url,
        status: res.status,
        contentType: res.headers.get("content-type"),
        preview: text.slice(0, 300),
      });
      throw e;
    }
  }

  const Permissions = (function () {
    let dt = null;
    let tableEl = null;

    function canDT() {
      return typeof $ !== "undefined" && $.fn && $.fn.DataTable;
    }

    function initDataTable() {
      tableEl = qs(SEL.table);
      if (!tableEl || !canDT()) return;

      if ($.fn.DataTable.isDataTable(tableEl)) {
        dt = $(tableEl).DataTable();
        return;
      }

      dt = $(tableEl).DataTable({
        info: false,
        order: [],
        pageLength: 10,
        lengthChange: false,
        columnDefs: [{ orderable: false, targets: COL.ASSIGNED }],
      });
    }

    function bindSearch() {
      const input = qs(SEL.search);
      if (!input || !dt) return;

      let timer = null;
      input.addEventListener("keyup", (e) => {
        clearTimeout(timer);
        const v = e.target.value;
        timer = setTimeout(() => dt.search(v).draw(), 150);
      });
    }

    async function loadList() {
      if (!dt) return;

      try {
        const json = await apiJson(API.list);
        if (!json.success) {
          swalErr("Error", json.message || "Gagal load permissions");
          return;
        }

        const rows = Array.isArray(json.data) ? json.data : [];

        dt.clear();

        rows.forEach((p) => {
          const name = escapeHtml(p.name ?? p.permission_name ?? "");
          const assignedHtml = renderAssignedTo(p.assigned_to || p.roles || []);
          const createdLabel = escapeHtml(p.created_label || p.created_at_label || "");
          const createdOrder = escapeHtml(p.created_at || "");

          dt.row.add([
            name,
            assignedHtml,
            `<span data-order="${createdOrder}">${createdLabel || createdOrder}</span>`,
          ]);
        });

        dt.draw();
      } catch (err) {
        console.error(err);
        swalErr("Error", "Terjadi kesalahan saat load permissions");
      }
    }

    return {
      init() {
        initDataTable();
        bindSearch();
      },
      reload: loadList,
      get dt() {
        return dt;
      },
    };
  })();

  const PermissionModals = (function () {
    function setupValidation(form) {
      if (typeof FormValidation === "undefined" || !FormValidation.formValidation) return null;

      return FormValidation.formValidation(form, {
        fields: {
          permission_name: {
            validators: {
              notEmpty: { message: "Permission name is required" },
            },
          },
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap: new FormValidation.plugins.Bootstrap5({
            rowSelector: ".fv-row",
            eleInvalidClass: "",
            eleValidClass: "",
          }),
        },
      });
    }

    function bindAdd() {
      const modalEl = qs(SEL.modalAdd);
      if (!modalEl) return;

      const form = qs(SEL.formAdd, modalEl);
      if (!form) return;

      const modal = new bootstrap.Modal(modalEl);
      const fv = setupValidation(form);

      const btnClose = modalEl.querySelector('[data-kt-permissions-modal-action="close"]');
      const btnCancel = modalEl.querySelector('[data-kt-permissions-modal-action="cancel"]');
      const btnSubmit = modalEl.querySelector('[data-kt-permissions-modal-action="submit"]');

      const closeConfirm = (e) => {
        e?.preventDefault?.();
        Swal.fire({
          text: "Tutup form tambah permission?",
          icon: "warning",
          showCancelButton: true,
          buttonsStyling: false,
          confirmButtonText: "Ya, tutup",
          cancelButtonText: "Batal",
          customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-active-light" },
        }).then((r) => {
          if (r.isConfirmed) modal.hide();
        });
      };

      if (btnClose) btnClose.addEventListener("click", closeConfirm);

      if (btnCancel)
        btnCancel.addEventListener("click", (e) => {
          e.preventDefault();
          form.reset();
          modal.hide();
        });

      if (btnSubmit) {
        btnSubmit.addEventListener("click", async (e) => {
          e.preventDefault();

          const name = form.querySelector('[name="permission_name"]')?.value?.trim() || "";
          if (!name) {
            swalErr("Validasi", "Permission name wajib diisi.");
            return;
          }

          if (fv) {
            const status = await fv.validate();
            if (status !== "Valid") return;
          }

          disableSubmit(form, true, "Submit");

          try {
            const body =
              `permission_name=${encodeURIComponent(name)}` +
              `&_token=${encodeURIComponent(csrf())}`;

            const json = await apiJson(API.create, {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body,
            });

            if (!json.success) {
              swalErr("Gagal", json.message || "Gagal menambah permission");
              return;
            }

            const msg = (json && typeof json.message === "string" && json.message.trim())
              ? json.message
              : `Permission berhasil dibuat: ${name}`;
            await swalOk("Berhasil", msg);
            form.reset();
            modal.hide();
            await Permissions.reload();
          } catch (err) {
            console.error(err);
            swalErr("Error", "Terjadi kesalahan saat menambah permission");
          } finally {
            disableSubmit(form, false, "Submit");
          }
        });
      }
    }

    return { init: bindAdd };
  })();

  function bindSync() {
    const btn = qs(SEL.syncBtn);
    if (!btn) return;

    btn.addEventListener("click", async (e) => {
      e.preventDefault();

      Swal.fire({
        text: "Sync permissions dari registry sekarang?",
        icon: "question",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Ya, sync",
        cancelButtonText: "Batal",
        customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-active-light" },
      }).then(async (r) => {
        if (!r.isConfirmed) return;

        btn.disabled = true;

        try {
          const body = `_token=${encodeURIComponent(csrf())}`;
          const json = await apiJson(API.sync, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body,
          });

          if (!json.success) {
            swalErr("Gagal", json.message || "Sync gagal");
            return;
          }

          const sum = json.data?.summary;
          const msg = sum
            ? `Routes: ${sum.routes_scanned}, New permissions: ${sum.permissions_inserted}`
            : (json.message || "Sync berhasil");

          await swalOk("Berhasil", msg);
          await Permissions.reload();
        } catch (err) {
          console.error(err);
          swalErr("Error", "Terjadi kesalahan saat sync registry");
        } finally {
          btn.disabled = false;
        }
      });
    });
  }

  async function boot() {
    Permissions.init();
    PermissionModals.init();
    bindSync();
    await Permissions.reload();
  }

  if (typeof KTUtil !== "undefined" && typeof KTUtil.onDOMContentLoaded === "function") {
    KTUtil.onDOMContentLoaded(boot);
  } else {
    document.addEventListener("DOMContentLoaded", boot);
  }
})();
