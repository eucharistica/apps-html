<?php
// apps/admin/pages/users/list.php

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../auth/rbac.php';

emr_require_permission('admin.users.view');

$pdo = emr_pdo();

// Fetch users
$stmt = $pdo->query("
    SELECT u.id, u.username, u.name, u.email, u.status, 
           GROUP_CONCAT(r.name SEPARATOR ', ') as roles
    FROM emr_users u
    LEFT JOIN emr_user_has_roles ur ON ur.user_id = u.id
    LEFT JOIN emr_roles r ON r.id = ur.role_id
    GROUP BY u.id
    ORDER BY u.id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles untuk dropdown
$stmt = $pdo->query("SELECT id, name FROM emr_roles ORDER BY name");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            Users Management
        </div>
        <div class="card-toolbar">
            <?php if (emr_can('admin.users.create')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="ki-outline ki-plus fs-2"></i>
                    Add User
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body pt-0">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><span class="fw-bold"><?= htmlspecialchars($user['name'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                        <td>
                            <?php if ($user['roles']): ?>
                                <span class="badge badge-light-primary"><?= htmlspecialchars($user['roles']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">No roles</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-light-<?= $user['status'] === 'active' ? 'success' : 'danger' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if (emr_can('admin.users.edit')): ?>
                                <button class="btn btn-sm btn-light btn-active-light-primary" data-bs-toggle="modal"
                                    data-bs-target="#editUserModal" onclick="editUser(<?= $user['id'] ?>)">
                                    Edit
                                </button>
                            <?php endif; ?>
                            <?php if (emr_can('admin.users.delete')): ?>
                                <button class="btn btn-sm btn-light btn-active-light-danger"
                                    onclick="deleteUser(<?= $user['id'] ?>)">
                                    Delete
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role_id">
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (leave empty to keep current)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role_id">
                            <option value="">No Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(userId) {
        fetch('<?= EMR_BASE_URL ?>apps/admin/pages/users/api/get-users.php?id=' + userId)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.querySelector('#editUserForm input[name="user_id"]').value = user.id;
                    document.querySelector('#editUserForm input[name="name"]').value = user.name || '';
                    document.querySelector('#editUserForm input[name="username"]').value = user.username;
                    document.querySelector('#editUserForm input[name="email"]').value = user.email || '';
                    document.querySelector('#editUserForm select[name="status"]').value = user.status;
                    document.querySelector('#editUserForm select[name="role_id"]').value = user.role_id || '';
                }
            })
            .catch(e => alert('Error loading user'));
    }

    function deleteUser(userId) {
        if (!confirm('Are you sure?')) return;

        fetch('<?= EMR_BASE_URL ?>apps/admin/pages/users/api/delete-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'user_id=' + userId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting user');
                }
            })
            .catch(e => alert('Error'));
    }

    document.getElementById('addUserForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('<?= EMR_BASE_URL ?>apps/admin/pages/users/api/create-user.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error creating user');
                }
            })
            .catch(e => alert('Error'));
    });

    document.getElementById('editUserForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('<?= EMR_BASE_URL ?>apps/admin/pages/users/api/update-user.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error updating user');
                }
            })
            .catch(e => alert('Error'));
    });
</script>