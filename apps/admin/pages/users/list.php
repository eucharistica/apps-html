<?php
// apps/admin/pages/users/list.php

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
        <div class="card-title">Users Management</div>
        <div class="card-toolbar">
            <?php if (emr_can('admin.users.create')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="ki-outline ki-plus fs-2"></i> Add User
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
                        <td><?= htmlspecialchars($user['name'] ?? '-') ?></td>
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
                            <span class="badge badge-light-<?= $user['status']==='active' ? 'success' : 'danger' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                               data-kt-menu-trigger="click"
                               data-kt-menu-placement="bottom-end">
                                Actions <i class="ki-outline ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600
                                        menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                 data-kt-menu="true">

                                <?php if (emr_can('admin.users.edit')): ?>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3"
                                           data-action="edit"
                                           data-user-id="<?= $user['id'] ?>">Edit</a>
                                    </div>
                                <?php endif; ?>

                                <?php if (emr_can('admin.users.delete')): ?>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 text-danger"
                                           data-action="delete"
                                           data-user-id="<?= $user['id'] ?>">Delete</a>
                                    </div>
                                <?php endif; ?>

                            </div>
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
            <form id="addUserForm" autocomplete="off">
                <div class="modal-body">
                    <!-- Dummy invisible fields to prevent browser autofill -->
                    <input type="text" name="fakeusernameremembered" style="display:none">
                    <input type="password" name="fakepasswordremembered" style="display:none">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required autocomplete="new-name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required autocomplete="new-username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" autocomplete="new-email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
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
            <form id="editUserForm" autocomplete="off">
                <input type="hidden" name="user_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
                    </div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Password (leave empty to keep current)</label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <option value="">No Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
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

<!-- CSRF token -->
<?php if (function_exists('emr_csrf_token')): ?>
<input type="hidden" id="csrf_token" value="<?= emr_csrf_token() ?>">
<?php endif; ?>

<script src="<?= EMR_BASE_URL ?>apps/admin/pages/users/users.js"></script>
