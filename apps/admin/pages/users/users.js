"use strict";

// apps/admin/pages/users/users.js
// - Keeps existing CRUD (create/edit/update/delete) logic
// - Adds DataTables init + search/filter hooks inspired by Metronic example

var KTUsersList = function () {
    var datatable;
    var table;

    var initDatatable = function () {
        table = document.getElementById('kt_table_users');
        if (!table || typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

        datatable = $(table).DataTable({
            info: false,
            order: [],
            pageLength: 10,
            lengthChange: false,
            // Keep Actions column unsortable
            columnDefs: [
                {
                    orderable: false,
                    targets: -1
                }
            ]
        });
    };

    var handleSearchDatatable = function () {
        if (!datatable) return;

        var filterSearch = document.querySelector('[data-kt-user-table-filter="search"]');
        if (!filterSearch) return;

        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    };

    var handleFilterDatatable = function () {
        if (!datatable) return;

        var filterForm = document.querySelector('[data-kt-user-table-filter="form"]');
        if (!filterForm) return;

        var filterButton = filterForm.querySelector('[data-kt-user-table-filter="filter"]');
        if (!filterButton) return;

        var filterSelects = filterForm.querySelectorAll('select');

        filterButton.addEventListener('click', function () {
            var filterString = '';
            filterSelects.forEach(function (selectEl, idx) {
                if (selectEl.value && selectEl.value !== '') {
                    if (idx !== 0) filterString += ' ';
                    filterString += selectEl.value;
                }
            });

            datatable.search(filterString).draw();
        });
    };

    var handleResetForm = function () {
        if (!datatable) return;

        var resetButton = document.querySelector('[data-kt-user-table-filter="reset"]');
        var filterForm = document.querySelector('[data-kt-user-table-filter="form"]');

        if (!resetButton || !filterForm) return;

        resetButton.addEventListener('click', function () {
            filterForm.querySelectorAll('select').forEach(function (selectEl) {
                if (typeof $ !== 'undefined') {
                    $(selectEl).val('').trigger('change');
                } else {
                    selectEl.value = '';
                }
            });

            datatable.search('').draw();
        });
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            handleFilterDatatable();
            handleResetForm();
        }
    };
}();

// Existing action handlers (Edit/Delete)
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    e.preventDefault();

    const userId = btn.dataset.userId;
    const action = btn.dataset.action;

    if (!userId) return;

    if (action === 'edit') editUser(userId);
    if (action === 'delete') deleteUser(userId);
});

function editUser(userId) {
    fetch(`${window.EMR.baseUrl}apps/admin/pages/users/api/get-users.php?id=${userId}`)
        .then(res => res.json())
        .then(res => {
            if (!res.success) return alert(res.message);

            const user = res.data;
            const form = document.getElementById('editUserForm');
            if (!form) return;

            form.querySelector('[name="user_id"]').value = user.id ?? '';
            form.querySelector('[name="name"]').value = user.name ?? '';
            form.querySelector('[name="username"]').value = user.username ?? '';
            form.querySelector('[name="email"]').value = user.email ?? '';
            form.querySelector('[name="status"]').value = user.status ?? 'active';
            form.querySelector('[name="role_id"]').value = user.role_id ?? '';

            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        })
        .catch(err => { console.error(err); alert('Error ambil data user'); });
}

function deleteUser(userId) {
    const csrf = document.getElementById('csrf_token')?.value || '';

    Swal.fire({
        icon: 'warning',
        title: 'Hapus user?',
        text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
        showCancelButton: true,
        buttonsStyling: !1,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-secondary"
        }
    }).then((result) => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang menghapus user',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(`${window.EMR.baseUrl}apps/admin/pages/users/api/delete-user.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${encodeURIComponent(userId)}&_csrf=${encodeURIComponent(csrf)}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'User berhasil dihapus',
                        buttonsStyling: !1,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Gagal menghapus user',
                        buttonsStyling: !1,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus user',
                    buttonsStyling: !1,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            });
    });
}

function disableSubmit(form, loading = true) {
    const btn = form.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.disabled = loading;
    btn.innerText = loading ? 'Processing...' : 'Save Changes';
}

document.addEventListener('DOMContentLoaded', () => {
    // Init users table (DataTables + search/filter)
    if (typeof KTUtil !== 'undefined' && typeof KTUtil.onDOMContentLoaded === 'function') {
        // If KTUtil exists, keep same lifecycle style as Metronic
        KTUtil.onDOMContentLoaded(function () {
            KTUsersList.init();
        });
    } else {
        KTUsersList.init();
    }

    const addForm = document.getElementById('addUserForm');
    const editForm = document.getElementById('editUserForm');

    if (addForm) addForm.addEventListener('submit', e => {
        e.preventDefault();
        disableSubmit(e.target, true);

        const formData = new FormData(e.target);
        const csrfToken = document.getElementById('csrf_token')?.value;
        if (csrfToken) formData.append('_csrf', csrfToken);

        fetch(`${window.EMR.baseUrl}apps/admin/pages/users/api/create-user.php`, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'User berhasil dibuat',
                        buttonsStyling: !1,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Gagal membuat user',
                        buttonsStyling: !1,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat membuat user',
                    buttonsStyling: !1,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                console.error(err);
            })
            .finally(() => disableSubmit(e.target, false));
    });


    if (editForm) editForm.addEventListener('submit', e => {
        e.preventDefault();
        disableSubmit(e.target, true);

        const formData = new FormData(e.target);
        const csrfToken = document.getElementById('csrf_token')?.value;
        if (csrfToken) formData.append('_csrf', csrfToken);

        fetch(`${window.EMR.baseUrl}apps/admin/pages/users/api/update-user.php`, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Update user berhasil',
                        buttonsStyling: !1,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Gagal update user',
                        buttonsStyling: !1,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat update user',
                    buttonsStyling: !1,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                console.error(err);
            })
            .finally(() => disableSubmit(e.target, false));
    });
});
