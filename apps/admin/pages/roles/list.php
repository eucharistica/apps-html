<?php
// apps/admin/pages/roles/list.php

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../auth/rbac.php';

emr_require_permission('admin.roles.view');

$pdo = emr_pdo();

// roles + jumlah user per role (opsional) + jumlah permission
$roles = $pdo->query("
    SELECT
        r.id,
        r.name,
        (SELECT COUNT(*) FROM emr_user_has_roles uhr WHERE uhr.role_id = r.id) AS user_count,
        (SELECT COUNT(*) FROM emr_role_has_permissions rhp WHERE rhp.role_id = r.id) AS perm_count
    FROM emr_roles r
    ORDER BY r.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

// semua permissions untuk render checkbox list
$permissions = $pdo->query("
    SELECT id, name
    FROM emr_permissions
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// helper: ambil beberapa permission name untuk preview card
$stmtPreview = $pdo->prepare("
    SELECT p.name
    FROM emr_role_has_permissions rhp
    JOIN emr_permissions p ON p.id = rhp.permission_id
    WHERE rhp.role_id = ?
    ORDER BY p.name ASC
    LIMIT 6
");
?>
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
    <?php foreach ($roles as $r): ?>
            <?php
            $stmtPreview->execute([$r['id']]);
            $previewPerms = $stmtPreview->fetchAll(PDO::FETCH_COLUMN);
            ?>
            <div class="col-md-4">
                <div class="card card-flush h-md-100">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><?= htmlspecialchars($r['name']) ?></h2>
                        </div>
                    </div>

                    <div class="card-body pt-1">
                        <div class="fw-bold text-gray-600 mb-5">
                            Total users with this role: <?= (int) $r['user_count'] ?>
                        </div>

                        <div class="d-flex flex-column text-gray-600">
                            <?php if (count($previewPerms) === 0): ?>
                                    <div class="d-flex align-items-center py-2">
                                        <span class="bullet bg-primary me-3"></span><em>No permissions</em>
                                    </div>
                            <?php else: ?>
                                    <?php foreach ($previewPerms as $pname): ?>
                                            <div class="d-flex align-items-center py-2">
                                                <span class="bullet bg-primary me-3"></span><?= htmlspecialchars($pname) ?>
                                            </div>
                                    <?php endforeach; ?>
                                    <?php if ((int) $r['perm_count'] > count($previewPerms)): ?>
                                            <div class="d-flex align-items-center py-2">
                                                <span class="bullet bg-primary me-3"></span>
                                                <em>and <?= (int) $r['perm_count'] - count($previewPerms) ?> more...</em>
                                            </div>
                                    <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer flex-wrap pt-0">
                        <button
                            type="button"
                            class="btn btn-light btn-active-light-primary my-1"
                            data-action="edit-role"
                            data-role-id="<?= (int) $r['id'] ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#kt_modal_update_role"
                        >
                            Edit Role
                        </button>
                    </div>
                </div>
            </div>
    <?php endforeach; ?>

    <!-- Add new card -->
    <div class="col-md-4">
        <div class="card h-md-100">
            <div class="card-body d-flex flex-center">
                <button type="button" class="btn btn-clear d-flex flex-column flex-center" data-bs-toggle="modal" data-bs-target="#kt_modal_add_role">
                    <img src="<?= htmlspecialchars(EMR_BASE_URL . 'assets/media/illustrations/sketchy-1/4.png') ?>" alt="" class="mw-100 mh-150px mb-7" />
                    <div class="fw-bold fs-3 text-gray-600 text-hover-primary">Add New Role</div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add role -->
<div class="modal fade" id="kt_modal_add_role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_role_header">
                <h2 class="fw-bold">Add a Role</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-roles-modal-action="close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body scroll-y mx-lg-5 my-7">
                <form id="kt_modal_add_role_form" class="form" action="#">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_role_scroll"
                         data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}"
                         data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_role_header"
                         data-kt-scroll-wrappers="#kt_modal_add_role_scroll"
                         data-kt-scroll-offset="300px">

                        <div class="fv-row mb-10">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required">Role name</span></label>
                            <input class="form-control form-control-solid" placeholder="Enter a role name" name="role_name" />
                        </div>

                        <div class="fv-row mb-10">
                        <label class="fs-5 fw-bold form-label mb-2">Copy permissions from role (optional)</label>
                        <select
                        id="copy_from_role_id"
                        class="form-select form-select-solid"
                        name="copy_from_role_id"
                        data-control="select2"
                        data-placeholder="Pilih role"
                        data-dropdown-parent="#kt_modal_add_role"
                        >
                        <option value=""></option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        </div>
                        
                        <div class="fv-row">
                            <label class="fs-5 fw-bold form-label mb-2">Role Permissions</label>

                            <div class="mb-5">
                            <input
                                type="text"
                                id="kt_role_permissions_search_add"
                                class="form-control form-control-solid"
                                placeholder="Ketik untuk cari permission"
                            />
                            </div>


                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <tbody class="text-gray-600 fw-semibold">
                                        <tr>
                                            <td class="text-gray-800">Permissions</td>
                                            <td>
                                                <label class="form-check form-check-custom form-check-solid me-9">
                                                    <input class="form-check-input" type="checkbox" id="kt_roles_select_all_add" />
                                                    <span class="form-check-label" for="kt_roles_select_all_add">Select all</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <?php foreach ($permissions as $p): ?>
                                                <tr>
                                                    <td class="text-gray-800"><?= htmlspecialchars($p['name']) ?></td>
                                                    <td>
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= (int) $p['id'] ?>" />
                                                            <span class="form-check-label">Allow</span>
                                                        </label>
                                                    </td>
                                                </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-kt-roles-modal-action="cancel">Discard</button>
                        <button type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Update role -->
<div class="modal fade" id="kt_modal_update_role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_update_role_header">
                <h2 class="fw-bold">Update Role</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-roles-modal-action="close">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body scroll-y mx-5 my-7">
                <form id="kt_modal_update_role_form" class="form" action="#">
                    <input type="hidden" name="role_id" value="" />

                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_role_scroll"
                         data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}"
                         data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_update_role_header"
                         data-kt-scroll-wrappers="#kt_modal_update_role_scroll"
                         data-kt-scroll-offset="300px">

                        <div class="fv-row mb-10">
                            <label class="fs-5 fw-bold form-label mb-2"><span class="required">Role name</span></label>
                            <input class="form-control form-control-solid" placeholder="Role name" name="role_name" value="" />
                        </div>

                        <div class="fv-row">
                            <label class="fs-5 fw-bold form-label mb-2">Role Permissions</label>
                            <div class="mb-5">
                            <input
                                type="text"
                                id="kt_role_permissions_search_update"
                                class="form-control form-control-solid"
                                placeholder="Ketik untuk cari permission"
                            />
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <tbody class="text-gray-600 fw-semibold">
                                        <tr>
                                            <td class="text-gray-800">Permissions</td>
                                            <td>
                                                <label class="form-check form-check-sm form-check-custom form-check-solid me-9">
                                                    <input class="form-check-input" type="checkbox" id="kt_roles_select_all_update" />
                                                    <span class="form-check-label" for="kt_roles_select_all_update">Select all</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <?php foreach ($permissions as $p): ?>
                                                <tr>
                                                    <td class="text-gray-800"><?= htmlspecialchars($p['name']) ?></td>
                                                    <td>
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= (int) $p['id'] ?>" />
                                                            <span class="form-check-label">Allow</span>
                                                        </label>
                                                    </td>
                                                </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-kt-roles-modal-action="cancel">Discard</button>
                        <button type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
