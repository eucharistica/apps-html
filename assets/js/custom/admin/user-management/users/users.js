"use strict";

(function () {
  // --------- tiny helpers ----------
  const qs = (sel, root = document) => root.querySelector(sel);

  const baseUrl = () => window.EMR?.baseUrl || "/";
  const csrfToken = () => qs("#csrf_token")?.value || "";

  const disableSubmit = (form, loading = true, labelIdle = "Save Changes") => {
    const btn = form?.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.disabled = loading;
    btn.innerText = loading ? "Processing..." : labelIdle;
  };

  const swalError = (title, text) =>
    Swal.fire({
      icon: "error",
      title,
      text,
      buttonsStyling: false,
      confirmButtonText: "OK",
      customClass: { confirmButton: "btn btn-primary" },
    });

  const swalSuccess = (title, text) =>
    Swal.fire({
      icon: "success",
      title,
      text,
      buttonsStyling: false,
      confirmButtonText: "OK",
      customClass: { confirmButton: "btn btn-primary" },
    });

  // --------- DataTable + Filters + Export ----------
  const UsersTable = (function () {
    let dt = null;
    let tableEl = null;

    const SEL = {
      table: "#kt_table_users",
      search: '[data-kt-user-table-filter="search"]',
      filterForm: '[data-kt-user-table-filter="form"]',
      filterApply: '[data-kt-user-table-filter="filter"]',
      filterReset: '[data-kt-user-table-filter="reset"]',
      filterRole: '[data-kt-user-table-filter="role"]',

      exportModal: "#kt_modal_export_users",
      exportForm: "#kt_modal_export_users_form",
      exportSubmit: '[data-kt-users-modal-action="submit"]',
      exportCancel: '[data-kt-users-modal-action="cancel"]',
      exportClose: '[data-kt-users-modal-action="close"]',
      exportFormat: '[name="format"]',
    };

    // Name(0), Username(1), Roles(2), Status(3), Last login(4), Actions(5)
    const COL = { ROLES: 2, ACTIONS: 5 };

    const canDT = () => typeof $ !== "undefined" && $.fn && $.fn.DataTable;

    function initDataTable() {
      tableEl = qs(SEL.table);
      if (!tableEl || !canDT()) return;

      // guard: cegah "Cannot reinitialise DataTable" [web:107]
      if ($.fn.DataTable.isDataTable(tableEl)) {
        dt = $(tableEl).DataTable();
        return;
      }

      dt = $(tableEl).DataTable({
        info: false,
        order: [],
        pageLength: 10,
        lengthChange: false,

        // penting: jangan set dom/layout, biar paging & responsif tetap native
        columnDefs: [{ orderable: false, targets: COL.ACTIONS }],

        buttons: [
          { extend: "copyHtml5", title: "Users" },
          { extend: "csvHtml5", title: "Users" },
          { extend: "excelHtml5", title: "Users" },
          { extend: "pdfHtml5", title: "Users" },
          { extend: "print", title: "Users" },
        ],
      });

      // hide button container (export via modal trigger)
      try {
        $(dt.buttons().container()).addClass("d-none");
      } catch (e) {}
    }

    function bindSearch() {
      const input = qs(SEL.search);
      if (!input || !dt) return;

      let timer = null;
      input.addEventListener("keyup", (e) => {
        clearTimeout(timer);
        const val = e.target.value;
        timer = setTimeout(() => dt.search(val).draw(), 150);
      });
    }

    function bindFilterRole() {
      const form = qs(SEL.filterForm);
      const btnApply = form ? qs(SEL.filterApply, form) : null;
      const btnReset = form ? qs(SEL.filterReset, form) : qs(SEL.filterReset);
      const roleSelect = form ? qs(SEL.filterRole, form) : qs(SEL.filterRole);

      if (!dt || !roleSelect) return;

      if (btnApply) {
        btnApply.addEventListener("click", () => {
          const role = roleSelect.value || "";

          // exact match supaya "Admin" tidak match "Super Admin" [web:106]
          if (!role) {
            dt.column(COL.ROLES).search("").draw();
            return;
          }

          const safe = $.fn.dataTable.util.escapeRegex(role);
          dt.column(COL.ROLES).search(`^${safe}$`, true, false).draw();
        });
      }

      if (btnReset) {
        btnReset.addEventListener("click", () => {
          if (typeof $ !== "undefined") $(roleSelect).val("").trigger("change");
          else roleSelect.value = "";

          dt.column(COL.ROLES).search("");
          dt.search("").draw();
        });
      }
    }

    function exportByFormat(format) {
      if (!dt) return false;
      const map = { copy: 0, csv: 1, excel: 2, pdf: 3, print: 4 };
      const idx = map[format];
      if (typeof idx === "undefined") return false;

      dt.button(idx).trigger();
      return true;
    }

    function bindExportModal() {
      const modalEl = qs(SEL.exportModal);
      if (!modalEl || !dt) return;

      const form = qs(SEL.exportForm, modalEl);
      const btnSubmit = qs(SEL.exportSubmit, modalEl);
      const btnCancel = qs(SEL.exportCancel, modalEl);
      const btnClose = qs(SEL.exportClose, modalEl);

      if (!btnSubmit) return;

      const modal = new bootstrap.Modal(modalEl);
      const close = () => modal.hide();

      btnSubmit.addEventListener("click", (e) => {
        e.preventDefault();

        const format = form?.querySelector(SEL.exportFormat)?.value || "";
        if (!format) {
          swalError("Format wajib dipilih", "Pilih salah satu format export (Excel / PDF / CSV / Print).");
          return;
        }

        const ok = exportByFormat(format);
        if (!ok) {
          swalError("Export gagal", "Format tidak didukung atau Buttons belum ter-load.");
          return;
        }

        close();
      });

      const askClose = (e) => {
        e?.preventDefault?.();
        close();
      };

      if (btnCancel) btnCancel.addEventListener("click", askClose);
      if (btnClose) btnClose.addEventListener("click", askClose);
    }

    return {
      init() {
        initDataTable();
        bindSearch();
        bindFilterRole();
        bindExportModal();
      },
    };
  })();

  // --------- CRUD handlers ----------
  async function editUser(userId) {
    try {
      const url = `${baseUrl()}apps/admin/pages/users/api/get-users.php?id=${encodeURIComponent(userId)}`;
      const res = await fetch(url);
      const json = await res.json();

      if (!json.success) {
        swalError("Error", json.message || "Gagal ambil data user");
        return;
      }

      const user = json.data || {};
      const form = qs("#editUserForm");
      if (!form) return;

      form.querySelector('[name="user_id"]').value = user.id ?? "";
      form.querySelector('[name="name"]').value = user.name ?? "";
      form.querySelector('[name="username"]').value = user.username ?? "";
      form.querySelector('[name="email"]').value = user.email ?? "";
      form.querySelector('[name="status"]').value = user.status ?? "active";
      form.querySelector('[name="role_id"]').value = user.role_id ?? "";

      new bootstrap.Modal(qs("#editUserModal")).show();
    } catch (err) {
      console.error(err);
      swalError("Error", "Terjadi kesalahan ambil data user");
    }
  }

  function deleteUser(userId) {
    const csrf = csrfToken();

    Swal.fire({
      icon: "warning",
      title: "Hapus user?",
      text: "Data yang sudah dihapus tidak bisa dikembalikan.",
      showCancelButton: true,
      buttonsStyling: false,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
      customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-secondary" },
    }).then(async (result) => {
      if (!result.isConfirmed) return;

      Swal.fire({
        title: "Memproses...",
        text: "Sedang menghapus user",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const r = await fetch(`${baseUrl()}apps/admin/pages/users/api/delete-user.php`, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `user_id=${encodeURIComponent(userId)}&_csrf=${encodeURIComponent(csrf)}`,
        });

        const data = await r.json();
        if (data.success) {
          await swalSuccess("Berhasil!", data.message || "User berhasil dihapus");
          location.reload();
        } else {
          swalError("Error!", data.message || "Gagal menghapus user");
        }
      } catch (err) {
        console.error(err);
        swalError("Error!", "Terjadi kesalahan saat menghapus user");
      }
    });
  }

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-action]");
    if (!btn) return;

    e.preventDefault();

    const userId = btn.dataset.userId;
    const action = btn.dataset.action;
    if (!userId) return;

    if (action === "edit") editUser(userId);
    if (action === "delete") deleteUser(userId);
  });

  // --------- Forms: create / update ----------
  function bindForms() {
    const addForm = qs("#addUserForm");
    const editForm = qs("#editUserForm");

    if (addForm) {
      addForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        disableSubmit(addForm, true, "Create User");

        try {
          const fd = new FormData(addForm);
          const csrf = csrfToken();
          if (csrf) fd.append("_csrf", csrf);

          const r = await fetch(`${baseUrl()}apps/admin/pages/users/api/create-user.php`, {
            method: "POST",
            body: new URLSearchParams(fd),
          });

          const data = await r.json();
          if (data.success) {
            await swalSuccess("Berhasil!", data.message || "User berhasil dibuat");
            location.reload();
          } else {
            swalError("Error!", data.message || "Gagal membuat user");
          }
        } catch (err) {
          console.error(err);
          swalError("Error!", "Terjadi kesalahan saat membuat user");
        } finally {
          disableSubmit(addForm, false, "Create User");
        }
      });
    }

    if (editForm) {
      editForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        disableSubmit(editForm, true, "Save Changes");

        try {
          const fd = new FormData(editForm);
          const csrf = csrfToken();
          if (csrf) fd.append("_csrf", csrf);

          const r = await fetch(`${baseUrl()}apps/admin/pages/users/api/update-user.php`, {
            method: "POST",
            body: new URLSearchParams(fd),
          });

          const data = await r.json();
          if (data.success) {
            await swalSuccess("Berhasil!", data.message || "Update user berhasil");
            location.reload();
          } else {
            swalError("Error!", data.message || "Gagal update user");
          }
        } catch (err) {
          console.error(err);
          swalError("Error!", "Terjadi kesalahan saat update user");
        } finally {
          disableSubmit(editForm, false, "Save Changes");
        }
      });
    }
  }

  function boot() {
    UsersTable.init();
    bindForms();
  }

  if (typeof KTUtil !== "undefined" && typeof KTUtil.onDOMContentLoaded === "function") {
    KTUtil.onDOMContentLoaded(boot);
  } else {
    document.addEventListener("DOMContentLoaded", boot);
  }
})();
