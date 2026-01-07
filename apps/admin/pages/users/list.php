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
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search user" />
            </div>
            <!--end::Search-->
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                                    <!--begin::Filter-->
                                                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                    <i class="ki-outline ki-filter fs-2"></i>Filter</button>
                                                    <!--begin::Menu 1-->
                                                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                                        <!--begin::Header-->
                                                        <div class="px-7 py-5">
                                                            <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                                                        </div>
                                                        <!--end::Header-->
                                                        <!--begin::Separator-->
                                                        <div class="separator border-gray-200"></div>
                                                        <!--end::Separator-->
                                                        <!--begin::Content-->
                                                        <div class="px-7 py-5" data-kt-user-table-filter="form">
                                                            <!--begin::Input group-->
                                                            <div class="mb-10">
                                                                <label class="form-label fs-6 fw-semibold">Role:</label>
                                                                <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select option" data-allow-clear="true" data-kt-user-table-filter="role" data-hide-search="true">
                                                                    <option></option>
                                                                    <?php foreach ($roles as $role): ?>
                                                                            <option value="<?= htmlspecialchars($role['name']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <!--end::Input group-->
                                                            <!--begin::Actions-->
                                                            <div class="d-flex justify-content-end">
                                                                <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="reset">Reset</button>
                                                                <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="filter">Apply</button>
                                                            </div>
                                                            <!--end::Actions-->
                                                        </div>
                                                        <!--end::Content-->
                                                    </div>
                                                    <!--end::Menu 1-->
                                                    <!--end::Filter-->
            <!--begin::Export-->
            <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_export_users">
            <i class="ki-outline ki-exit-up fs-2"></i>Export</button>
            <!--end::Export-->
            <!--begin::Add user-->
            <?php if (emr_can('admin.users.create')): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="ki-outline ki-plus fs-2"></i> Add User
                            </button>
            <?php endif; ?>
            <!--end::Add user-->
            </div>            
        </div>
    </div>
    <!--begin::Modal - Adjust Balance-->
                                                <div class="modal fade" id="kt_modal_export_users" tabindex="-1" aria-hidden="true">
                                                    <!--begin::Modal dialog-->
                                                    <div class="modal-dialog modal-dialog-centered mw-650px">
                                                        <!--begin::Modal content-->
                                                        <div class="modal-content">
                                                            <!--begin::Modal header-->
                                                            <div class="modal-header">
                                                                <!--begin::Modal title-->
                                                                <h2 class="fw-bold">Export Users</h2>
                                                                <!--end::Modal title-->
                                                                <!--begin::Close-->
                                                                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-users-modal-action="close">
                                                                    <i class="ki-outline ki-cross fs-1"></i>
                                                                </div>
                                                                <!--end::Close-->
                                                            </div>
                                                            <!--end::Modal header-->
                                                            <!--begin::Modal body-->
                                                            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                                                                <!--begin::Form-->
                                                                <form id="kt_modal_export_users_form" class="form" action="#">
                                                                    <!--begin::Input group-->
                                                                    <div class="fv-row mb-10">
                                                                        <!--begin::Label-->
                                                                        <label class="fs-6 fw-semibold form-label mb-2">Select Roles:</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <select name="role" data-control="select2" data-placeholder="Select a role" data-hide-search="true" class="form-select form-select-solid fw-bold">
                                                                            <option></option>
                                                                            <?php foreach ($roles as $role): ?>
                                                                            <option value="<?= htmlspecialchars($role['name']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Input group-->
                                                                    <!--begin::Input group-->
                                                                    <div class="fv-row mb-10">
                                                                        <!--begin::Label-->
                                                                        <label class="required fs-6 fw-semibold form-label mb-2">Select Export Format:</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <select name="format" data-control="select2" data-placeholder="Select a format" data-hide-search="true" class="form-select form-select-solid fw-bold">
                                                                            <option></option>
                                                                            <option value="excel">Excel</option>
                                                                            <option value="pdf">PDF</option>
                                                                            <option value="cvs">CVS</option>
                                                                            <option value="zip">ZIP</option>
                                                                        </select>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Input group-->
                                                                    <!--begin::Actions-->
                                                                    <div class="text-center">
                                                                        <button type="reset" class="btn btn-light me-3" data-kt-users-modal-action="cancel">Discard</button>
                                                                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                                                                            <span class="indicator-label">Submit</span>
                                                                            <span class="indicator-progress">Please wait... 
                                                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                                        </button>
                                                                    </div>
                                                                    <!--end::Actions-->
                                                                </form>
                                                                <!--end::Form-->
                                                            </div>
                                                            <!--end::Modal body-->
                                                        </div>
                                                        <!--end::Modal content-->
                                                    </div>
                                                    <!--end::Modal dialog-->
                                                </div>
                                                <!--end::Modal - New Card-->

    <div class="card-body py-4">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Name</th>
                    <th class="min-w-125px">Username</th>
                    <th class="min-w-125px">Email</th>
                    <th class="min-w-125px">Roles</th>
                    <th class="min-w-125px">Status</th>
                    <th class="text-end min-w-100px">Actions</th>
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
                                        <span class="badge badge-light-<?= $user['status'] === 'active' ? 'success' : 'danger' ?>">
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
<input type="hidden" id="csrf_token" value="<?= emr_csrf_token() ?>">