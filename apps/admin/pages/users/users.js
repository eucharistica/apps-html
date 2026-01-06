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

            form.querySelector('[name="user_id"]').value  = user.id ?? '';
            form.querySelector('[name="name"]').value     = user.name ?? '';
            form.querySelector('[name="username"]').value = user.username ?? '';
            form.querySelector('[name="email"]').value    = user.email ?? '';
            form.querySelector('[name="status"]').value   = user.status ?? 'active';
            form.querySelector('[name="role_id"]').value  = user.role_id ?? '';

            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        })
        .catch(err => { console.error(err); alert('Error ambil data user'); });
}

function deleteUser(userId) {
    if (!confirm('Are you sure?')) return;

    const csrf = document.getElementById('csrf_token')?.value || '';

    fetch(`${window.EMR.baseUrl}apps/admin/pages/users/api/delete-user.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `user_id=${userId}&_csrf=${csrf}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Error deleting user');
    });
}

function disableSubmit(form, loading = true) {
    const btn = form.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.disabled = loading;
    btn.innerText = loading ? 'Processing...' : 'Save Changes';
}

document.addEventListener('DOMContentLoaded', () => {
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
            if (data.success) location.reload();
            else alert(data.message);
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
            if (data.success) location.reload();
            else alert(data.message);
        })
        .finally(() => disableSubmit(e.target, false));
    });
});
