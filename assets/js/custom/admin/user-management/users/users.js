"use strict";

(function () {
  // ---------- Helpers ----------
  const qs = (sel, root = document) => root.querySelector(sel);

  function emrUrl(path) {
    return `${window.EMR?.baseUrl || "/"}${path}`;
  }

  function getCsrf() {
    return qs("#csrf_token")?.value || "";
  }

  function disableSubmit(form, loading = true, labelIdle = "Save Changes") {
    const btn = form?.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.disabled = loading;
    btn.innerText = loading ? "Processing..." : labelIdle;
  }

  // ---------- DataTable + UI ----------
  const UsersTable = (function () {
    let dt = null;
    let tableEl = null;

    function initDataTable() {
      tableEl = document.getElementById("kt_table_users");
      if (!tableEl) return;
      if (typeof $ === "undefined" || !$.fn?.DataTable) return;

      // 6 kolom: Name, Username, Email, Roles, Status, Actions
      dt = $(tableEl).DataTable({
        info: false,
        order: [],
        pageLength: 10,
        lengthChange: false,

        // Buttons export (akan aktif jika Buttons extension tersedia)
        dom: "<'row'<'col-12'B>>" + "rt" + "<'row'<'col-12'p>>",
        buttons: [
          { extend: "copyHtml5", title: "Users" },
          { extend: "csvHtml5", title: "Users" },
          { extend: "excelHtml5", title: "Users" },
          { extend: "pdfHtml5", title: "Users" },
          { extend: "print", title: "Users" },
        ],

        columnDefs: [
          { orderable: false, targets: 5 }, // Actions
        ],
      });

      // Sembunyikan tombol default Buttons (kita trigger dari modal)
      // Aman walau Buttons tidak ada, karena container mungkin null
      $(dt.buttons().container()).addClass("d-none");
    }

    function bindSearch() {
      const input = qs('[data-kt-user-table-filter="search"]');
      if (!input || !dt) return;

      input.addEventListener("keyup", (e) => {
        dt.search(e.target.value).draw();
      });
    }

    // Filter role: gunakan "column search" untuk kolom Roles (index 3)
    // Lebih akurat daripada dt.search(global) karena cuma match di kolom roles. [web:53]
    function bindFilterRole() {
      const form = qs('[data-kt-user-table-filter="form"]');
      const btnApply = qs('[data-kt-user-table-filter="filter"]', form || document);
      const btnReset = qs('[data-kt-user-table-filter="reset"]');
      const roleSelect = qs('[data-kt-user-table-filter="role"]', form || document);

      if (!dt || !roleSelect) return;

      if (btnApply) {
        btnApply.addEventListener("click", () => {
          const val = roleSelect.value || "";
          dt.column(3).search(val).draw(); // Roles column
        });
      }

      if (btnReset) {
        btnReset.addEventListener("click", () => {
          if (typeof $ !== "undefined") $(roleSelect).val("").trigger("change");
          else roleSelect.value = "";

          dt.column(3).search("").draw();
          dt.search("").draw();
        });
      }
    }

    function exportByFormat(format) {
      if (!dt) return;

      // Mapping format modal -> Buttons index
      // urutan sesuai konfigurasi buttons di atas
      const map = {
        copy: 0,
        csv: 1,
        excel: 2,
        pdf: 3,
        print: 4,
      };

      const idx = map[format];
      if (typeof idx === "undefined") return;

      dt.button(idx).trigger(); // cara umum trigger tombol export DataTables [web:44]
    }

    return {
      init() {
        initDataTable();
        bindSearch();
        bindFilterRole();
      },
      exportByFormat,
      getDt() {
        return dt;
      },
    };
  })();

  // ---------- Export Modal (real export) ----------
  const ExportModal = (function () {
    function init() {
      const modalEl = document.getElementById("kt_modal_export_users");
      if (!modalEl) return;

      const form = modalEl.querySelector("#kt_modal_export_users_form");
      const btnSubmit = modalEl.querySelector('[data-kt-users-modal-action="submit"]');
      const btnCancel = modalEl.querySelector('[data-kt-users-modal-action="cancel"]');
      const btnClose = modalEl.querySelector('[data-kt-users-modal-action="close"]');

      const modal = new bootstrap.Modal(modalEl);

      function closeModal() {
        modal.hide();
      }

      function getFormat() {
        // name="format" di HTML modal kamu
        return form?.querySelector('[name="format"]')?.value || "";
      }

      if (btnSubmit) {
        btnSubmit.addEventListener("click", (e) => {
          e.preventDefault();

          const format = getFormat();
          if (!format) {
            Swal.fire({
              icon: "error",
              title: "Format wajib dipilih",
              text: "Pilih salah satu format export (Excel / PDF / CSV / Print).",
              buttonsStyling: false,
              confirmButtonText: "OK",
              customClass: { confirmButton: "btn btn-primary" },
            });
            return;
          }

          // catatan: di HTML kamu ada "cvs" harusnya "csv"
          const normalized = format === "cvs" ? "csv" : format;

          // trigger export beneran
          try {
            UsersTable.exportByFormat(normalized); // DataTables Buttons [web:41]
            closeModal();
          } catch (err) {
            console.error(err);
            Swal.fire({
              icon: "error",
              title: "Export gagal",
              text: "Buttons extension DataTables belum tersedia / belum ter-load.",
              buttonsStyling: false,
              confirmButtonText: "OK",
              customClass: { confirmButton: "btn btn-primary" },
            });
          }
        });
      }

      const askCancel = (e) => {
        e?.preventDefault?.();
        Swal.fire({
          text: "Tutup modal export?",
          icon: "warning",
          showCancelButton: true,
          buttonsStyling: false,
          confirmButtonText: "Ya",
          cancelButtonText: "Tidak",
          customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-active-light",
          },
        }).then((r) => {
          if (r.isConfirmed) {
            form?.reset?.();
            closeModal();
          }
        });
      };

      if (btnCancel) btnCancel.addEventListener("click", askCancel);
      if (btnClose) btnClose.addEventListener("click", askCancel);
    }

    return { init };
  })();

  // ---------- CRUD actions (Edit/Delete/Create/Update) ----------
  async function editUser(userId) {
    try {
      const res = await fetch(emrUrl(`apps/admin/pages/users/api/get-users.php?id=${encodeURIComponent(userId)}`));
      const json = await res.json();
      if (!json.success) {
        Swal.fire({ icon: "error", title: "Error", text: json.message || "Gagal ambil data user" });
        return;
      }

      const user = json.data || {};
      const form = document.getElementById("editUserForm");
      if (!form) return;

      form.querySelector('[name="user_id"]').value = user.id ?? "";
      form.querySelector('[name="name"]').value = user.name ?? "";
      form.querySelector('[name="username"]').value = user.username ?? "";
      form.querySelector('[name="email"]').value = user.email ?? "";
      form.querySelector('[name="status"]').value = user.status ?? "active";
      form.querySelector('[name="role_id"]').value = user.role_id ?? "";

      new bootstrap.Modal(document.getElementById("editUserModal")).show();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: "error", title: "Error", text: "Terjadi kesalahan ambil data user" });
    }
  }

  function deleteUser(userId) {
    const csrf = getCsrf();

    Swal.fire({
      icon: "warning",
      title: "Hapus user?",
      text: "Data yang sudah dihapus tidak bisa dikembalikan.",
      showCancelButton: true,
      buttonsStyling: false,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
      customClass: {
        confirmButton: "btn btn-danger",
        cancelButton: "btn btn-secondary",
      },
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
        const r = await fetch(emrUrl("apps/admin/pages/users/api/delete-user.php"), {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `user_id=${encodeURIComponent(userId)}&_csrf=${encodeURIComponent(csrf)}`,
        });

        const data = await r.json();
        if (data.success) {
          await Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: data.message || "User berhasil dihapus",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: { confirmButton: "btn btn-primary" },
          });

          location.reload();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: data.message || "Gagal menghapus user",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: { confirmButton: "btn btn-primary" },
          });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: "Terjadi kesalahan saat menghapus user",
          buttonsStyling: false,
          confirmButtonText: "OK",
          customClass: { confirmButton: "btn btn-primary" },
        });
      }
    });
  }

  // event delegation edit/delete
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

  // create/update submit
  function bindForms() {
    const addForm = document.getElementById("addUserForm");
    const editForm = document.getElementById("editUserForm");

    if (addForm) {
      addForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        disableSubmit(addForm, true, "Create User");

        try {
          const fd = new FormData(addForm);
          const csrf = getCsrf();
          if (csrf) fd.append("_csrf", csrf);

          const r = await fetch(emrUrl("apps/admin/pages/users/api/create-user.php"), {
            method: "POST",
            body: new URLSearchParams(fd),
          });

          const data = await r.json();
          if (data.success) {
            await Swal.fire({
              icon: "success",
              title: "Berhasil!",
              text: data.message || "User berhasil dibuat",
              buttonsStyling: false,
              confirmButtonText: "OK",
              customClass: { confirmButton: "btn btn-primary" },
            });
            location.reload();
          } else {
            Swal.fire({
              icon: "error",
              title: "Error!",
              text: data.message || "Gagal membuat user",
              buttonsStyling: false,
              confirmButtonText: "OK",
              customClass: { confirmButton: "btn btn-primary" },
            });
          }
        } catch (err) {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Terjadi kesalahan saat membuat user",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: { confirmButton: "btn btn-primary" },
          });
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
          const csrf = getCsrf();
          if (csrf) fd.append("_csrf", csrf);

          const r = await fetch(emrUrl("apps/admin/pages/users/api/update-user.php"), {
            method: "POST",
            body: new URLSearchParams(fd),
          });

          const data = await r.json();
          if (data.success) {
            await Swal.fire({
              icon: "success",
              title: "Berhasil!",
              text: data.message || "Update user berhasil",
              buttonsStyling: false,
              confirmButtonText: "OK",
              customClass: { confirmButton: "btn btn-primary" },
            });
            location.reload();
          } else {
            Swal.fire({
              icon: "error",
              title: "Error!",
              text: data.message || "Gagal update user",
              buttonsStyling: false,
              confirmButtonText: "OK",
              customClass: { confirmButton: "btn btn-primary" },
            });
          }
        } catch (err) {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Terjadi kesalahan saat update user",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: { confirmButton: "btn btn-primary" },
          });
        } finally {
          disableSubmit(editForm, false, "Save Changes");
        }
      });
    }
  }

  // ---------- Boot ----------
  function boot() {
    UsersTable.init();
    ExportModal.init();
    bindForms();
  }

  // Metronic style lifecycle
  if (typeof KTUtil !== "undefined" && typeof KTUtil.onDOMContentLoaded === "function") {
    KTUtil.onDOMContentLoaded(boot);
  } else {
    document.addEventListener("DOMContentLoaded", boot);
  }
})();
