"use strict";

(function () {
  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const BASE = window.EMR?.baseUrl || "/";
  const csrf = () => qs("#csrf_token")?.value || "";

  const SEL = {
    table: "#kt_permissions_table",
    search: '[data-kt-permissions-table-filter="search"]',
    deleteBtn: '[data-kt-permissions-table-filter="delete_row"]',

    modalAdd: "#kt_modal_add_permission",
    formAdd: "#kt_modal_add_permission_form",

    modalUpdate: "#kt_modal_update_permission",
    formUpdate: "#kt_modal_update_permission_form",
  };

  // Kolom tabel (sesuai markup list.php kamu)
  // 0 Name | 1 Assigned to | 2 Created Date | 3 Actions
  const COL = { NAME: 0, ASSIGNED: 1, CREATED: 2, ACTIONS: 3 };

  // ===== API endpoints (samakan dengan struktur folder kamu) =====
  const API = {
    list: `${BASE}apps/admin/pages/permissions/api/list-permissions.php`,
    create: `${BASE}apps/admin/pages/permissions/api/create-permission.php`,
    update: `${BASE}apps/admin/pages/permissions/api/update-permission.php`,
    delete: `${BASE}apps/admin/pages/permissions/api/delete-permission.php`,
    get: `${BASE}apps/admin/pages/permissions/api/get-permission.php`,
  };

  // ===== SweetAlert helpers =====
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
    // label biarkan Metronic indicator yang handle; fallback:
    btn.innerText = loading ? "Please wait..." : labelIdle;
  }

  // ===== Utils render =====
  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // roles: array of {id,name} atau string list
  function renderAssignedTo(roles) {
    if (!roles) return "";
    if (typeof roles === "string") return escapeHtml(roles);

    if (Array.isArray(roles)) {
      return roles
        .map((r) => {
          const name = escapeHtml(r.name ?? "");
          return `<span class="badge badge-light-primary fs-7 m-1">${name}</span>`;
        })
        .join("");
    }

    return escapeHtml(String(roles));
  }

  // ===== Data layer =====
  async function apiJson(url, options = {}) {
    const res = await fetch(url, options);
    const text = await res.text(); // baca raw dulu

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

  // ===== Permissions module =====
  const Permissions = (function () {
    let dt = null;
    let tableEl = null;

    function canDT() {
      return typeof $ !== "undefined" && $.fn && $.fn.DataTable;
    }

    function initDataTable() {
      tableEl = qs(SEL.table);
      if (!tableEl || !canDT()) return;

      // guard re-init
      if ($.fn.DataTable.isDataTable(tableEl)) {
        dt = $(tableEl).DataTable();
        return;
      }

      dt = $(tableEl).DataTable({
        info: false,
        order: [],
        pageLength: 10,
        lengthChange: false,
        columnDefs: [
          { orderable: false, targets: COL.ASSIGNED },
          { orderable: false, targets: COL.ACTIONS },
        ],
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

    function bindDeleteAction() {
      // delegation (karena row akan di-render ulang dari API)
      document.addEventListener("click", (e) => {
        const btn = e.target.closest(SEL.deleteBtn);
        if (!btn) return;

        e.preventDefault();

        const tr = btn.closest("tr");
        const permissionId = btn.dataset.permissionId;
        const name = tr?.querySelectorAll("td")?.[COL.NAME]?.innerText || "permission";

        if (!permissionId) {
          swalErr("Error", "permissionId tidak ditemukan pada tombol delete.");
          return;
        }

        Swal.fire({
          text: `Hapus permission "${name}"?`,
          icon: "warning",
          showCancelButton: true,
          buttonsStyling: false,
          confirmButtonText: "Ya, hapus",
          cancelButtonText: "Batal",
          customClass: {
            confirmButton: "btn fw-bold btn-danger",
            cancelButton: "btn fw-bold btn-active-light-primary",
          },
        }).then(async (r) => {
          if (!r.isConfirmed) return;

          try {
            const body = `permission_id=${encodeURIComponent(permissionId)}&_csrf=${encodeURIComponent(csrf())}`;
            const json = await apiJson(API.delete, {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body,
            });

            if (!json.success) {
              swalErr("Gagal", json.message || "Gagal menghapus permission");
              return;
            }

            await swalOk("Berhasil", json.message || "Permission dihapus");
            // remove row tanpa reload halaman
            if (dt && tr) dt.row($(tr)).remove().draw();
          } catch (err) {
            console.error(err);
            swalErr("Error", "Terjadi kesalahan saat menghapus permission");
          }
        });
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
          const id = p.id ?? p.permission_id ?? "";
          const name = escapeHtml(p.name ?? p.permission_name ?? "");
          const assignedHtml = renderAssignedTo(p.assigned_to || p.roles || []);
          const createdLabel = escapeHtml(p.created_label || p.created_at_label || "");
          const createdOrder = escapeHtml(p.created_at || "");

          dt.row.add([
            name,
            assignedHtml,
            `<span data-order="${createdOrder}">${createdLabel || createdOrder}</span>`,
            `
              <div class="text-end">
                <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3"
                        data-action="edit"
                        data-permission-id="${escapeHtml(id)}"
                        data-bs-toggle="modal"
                        data-bs-target="#kt_modal_update_permission">
                  <i class="ki-outline ki-setting-3 fs-3"></i>
                </button>
                <button class="btn btn-icon btn-active-light-primary w-30px h-30px"
                        data-kt-permissions-table-filter="delete_row"
                        data-permission-id="${escapeHtml(id)}">
                  <i class="ki-outline ki-trash fs-3"></i>
                </button>
              </div>
            `,
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
        bindDeleteAction();
      },
      reload: loadList,
      get dt() {
        return dt;
      },
    };
  })();

  // ===== Modals Add/Update =====
  const PermissionModals = (function () {
    function setupValidation(form) {
      // optional: kalau FormValidation plugin ada, pakai. Kalau tidak, minimal check manual.
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
      if (btnCancel) btnCancel.addEventListener("click", (e) => {
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
            const isCore = form.querySelector('[name="permissions_core"]')?.checked ? 1 : 0;

            const body =
              `permission_name=${encodeURIComponent(name)}` +
              `&is_core=${encodeURIComponent(isCore)}` +
              `&_csrf=${encodeURIComponent(csrf())}`;

            const json = await apiJson(API.create, {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body,
            });

            if (!json.success) {
              swalErr("Gagal", json.message || "Gagal menambah permission");
              return;
            }

            await swalOk("Berhasil", json.message || "Permission berhasil dibuat");
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

    function bindUpdate() {
      const modalEl = qs(SEL.modalUpdate);
      if (!modalEl) return;

      const form = qs(SEL.formUpdate, modalEl);
      if (!form) return;

      const modal = new bootstrap.Modal(modalEl);
      const fv = setupValidation(form);

      const btnClose = modalEl.querySelector('[data-kt-permissions-modal-action="close"]');
      const btnCancel = modalEl.querySelector('[data-kt-permissions-modal-action="cancel"]');
      const btnSubmit = modalEl.querySelector('[data-kt-permissions-modal-action="submit"]');

      // Isi form saat tombol edit di table diklik
      document.addEventListener("click", async (e) => {
        const btn = e.target.closest('[data-action="edit"][data-permission-id]');
        if (!btn) return;

        const id = btn.dataset.permissionId;
        if (!id) return;

        // Simpan id ke hidden input; kalau belum ada, buat
        let hidden = form.querySelector('[name="permission_id"]');
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = "permission_id";
          form.appendChild(hidden);
        }
        hidden.value = id;

        // Optional: ambil data detail dari API.get, atau cukup pakai row text
        try {
          const url = `${API.get}?id=${encodeURIComponent(id)}`;
          const json = await apiJson(url);
          if (json.success && json.data) {
            form.querySelector('[name="permission_name"]').value = json.data.name ?? json.data.permission_name ?? "";
          } else {
            // fallback: ambil dari row
            const tr = btn.closest("tr");
            const nameCell = tr?.querySelectorAll("td")?.[COL.NAME]?.innerText || "";
            form.querySelector('[name="permission_name"]').value = nameCell;
          }
        } catch (err) {
          console.error(err);
        }
      });

      const closeConfirm = (e) => {
        e?.preventDefault?.();
        Swal.fire({
          text: "Tutup form update permission?",
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
      if (btnCancel) btnCancel.addEventListener("click", (e) => {
        e.preventDefault();
        modal.hide();
      });

      if (btnSubmit) {
        btnSubmit.addEventListener("click", async (e) => {
          e.preventDefault();

          const id = form.querySelector('[name="permission_id"]')?.value || "";
          const name = form.querySelector('[name="permission_name"]')?.value?.trim() || "";

          if (!id) {
            swalErr("Error", "permission_id tidak ditemukan.");
            return;
          }
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
              `permission_id=${encodeURIComponent(id)}` +
              `&permission_name=${encodeURIComponent(name)}` +
              `&_csrf=${encodeURIComponent(csrf())}`;

            const json = await apiJson(API.update, {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body,
            });

            if (!json.success) {
              swalErr("Gagal", json.message || "Gagal update permission");
              return;
            }

            await swalOk("Berhasil", json.message || "Permission berhasil diupdate");
            modal.hide();
            await Permissions.reload();
          } catch (err) {
            console.error(err);
            swalErr("Error", "Terjadi kesalahan saat update permission");
          } finally {
            disableSubmit(form, false, "Submit");
          }
        });
      }
    }

    return {
      init() {
        bindAdd();
        bindUpdate();
      },
    };
  })();

  // ===== Boot =====
  async function boot() {
    Permissions.init();
    PermissionModals.init();
    await Permissions.reload();
  }

  if (typeof KTUtil !== "undefined" && typeof KTUtil.onDOMContentLoaded === "function") {
    KTUtil.onDOMContentLoaded(boot);
  } else {
    document.addEventListener("DOMContentLoaded", boot);
  }
})();
