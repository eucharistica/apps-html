<?php
// apps/admin/pages/permissions/list.php
?>
<div class="card card-flush">
  <div class="card-header mt-6">
    <div class="card-title">
      <div class="d-flex align-items-center position-relative my-1 me-5">
        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
        <input type="text"
          data-kt-permissions-table-filter="search"
          class="form-control form-control-solid w-250px ps-13"
          placeholder="Search Permissions" />
      </div>
    </div>

    <div class="card-toolbar">
      <button type="button"
        class="btn btn-light me-3"
        id="kt_permissions_sync_btn">
        <i class="ki-outline ki-arrows-loop fs-3"></i>Sync Registry
      </button>

      <button type="button"
        class="btn btn-light-primary"
        data-bs-toggle="modal"
        data-bs-target="#kt_modal_add_permission">
        <i class="ki-outline ki-plus-square fs-3"></i>Add Permission
      </button>
    </div>
  </div>

  <div class="card-body pt-0">
    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_permissions_table">
      <thead>
        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
          <th class="min-w-250px">Name</th>
          <th class="min-w-250px">Assigned to</th>
          <th class="min-w-175px">Created Date</th>
        </tr>
      </thead>
      <tbody class="fw-semibold text-gray-600">
        <!-- Data di-render via JS dari API -->
      </tbody>
    </table>
  </div>
</div>

<!-- Modal - Add Permission -->
<div class="modal fade" id="kt_modal_add_permission" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered mw-650px">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="fw-bold">Add a Permission</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-permissions-modal-action="close">
          <i class="ki-outline ki-cross fs-1"></i>
        </div>
      </div>

      <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
        <form id="kt_modal_add_permission_form" class="form" action="#">
          <div class="fv-row mb-7">
            <label class="fs-6 fw-semibold form-label mb-2">
              <span class="required">Permission Name</span>
            </label>
            <input class="form-control form-control-solid"
              placeholder="Enter a permission name"
              name="permission_name" />
          </div>

          <div class="text-center pt-15">
            <button type="reset" class="btn btn-light me-3" data-kt-permissions-modal-action="cancel">Discard</button>
            <button type="submit" class="btn btn-primary" data-kt-permissions-modal-action="submit">
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
