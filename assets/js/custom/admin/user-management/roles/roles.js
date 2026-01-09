"use strict";

(function () {
    const qs = (s, r = document) => r.querySelector(s);
    const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

    const BASE = window.EMR?.baseUrl || "/";
    const csrf = () => qs("#csrf_token")?.value || "";

    const API = {
        listRoles: `${BASE}apps/admin/pages/roles/api/list-roles.php`,
        listPermissions: `${BASE}apps/admin/pages/roles/api/list-permissions.php`,
        createRole: `${BASE}apps/admin/pages/roles/api/create-role.php`,
        getRole: `${BASE}apps/admin/pages/roles/api/get-role.php`,
        updateRolePerms: `${BASE}apps/admin/pages/roles/api/update-role-permissions.php`,
        updateRole: `${BASE}apps/admin/pages/roles/api/update-role.php`,
    };

    async function apiJson(url, options = {}) {
        const res = await fetch(url, options);
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Non-JSON response:", { url, status: res.status, preview: text.slice(0, 300) });
            throw e;
        }
    }

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

    function wireSelectAll(formEl) {
        const all =
            qs("#kt_roles_select_all", formEl) ||
            qs("#kt_roles_select_all_add", formEl) ||
            qs("#kt_roles_select_all_update", formEl);

        const boxes = qsa('input[type="checkbox"][name="permissions[]"]', formEl);
        if (!all || boxes.length === 0) return;

        all.addEventListener("change", (e) => {
            boxes.forEach((cb) => (cb.checked = e.target.checked));
        });
    }

    function getCheckedPermissionIds(formEl) {
        return qsa('input[type="checkbox"][name="permissions[]"]:checked', formEl).map((cb) => cb.value);
    }

    function setCheckedPermissionIds(formEl, ids) {
        const set = new Set((ids || []).map(String));
        qsa('input[type="checkbox"][name="permissions[]"]', formEl).forEach((cb) => {
            cb.checked = set.has(String(cb.value));
        });

        const all =
            qs("#kt_roles_select_all", formEl) ||
            qs("#kt_roles_select_all_add", formEl) ||
            qs("#kt_roles_select_all_update", formEl);

        if (all) {
            const boxes = qsa('input[type="checkbox"][name="permissions[]"]', formEl);
            all.checked = boxes.length > 0 && boxes.every((cb) => cb.checked);
        }
    }

    function ensureHiddenRoleId(formEl) {
        let el = qs('input[name="role_id"]', formEl);
        if (!el) {
            el = document.createElement("input");
            el.type = "hidden";
            el.name = "role_id";
            formEl.appendChild(el);
        }
        return el;
    }

    // ====== Add Role modal ======
    const AddRole = (function () {
        let modalEl, formEl, modal;

        function setupValidation() {
            if (typeof FormValidation === "undefined" || !FormValidation.formValidation) return null;
            return FormValidation.formValidation(formEl, {
                fields: {
                    role_name: { validators: { notEmpty: { message: "Role name is required" } } },
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

        async function submit() {
            const name = (qs('[name="role_name"]', formEl)?.value || "").trim();
            if (!name) return swalErr("Validasi", "Role name wajib diisi.");

            const permissionIds = getCheckedPermissionIds(formEl);

            const body =
                `role_name=${encodeURIComponent(name)}` +
                `&permission_ids=${encodeURIComponent(JSON.stringify(permissionIds))}` +
                `&_csrf=${encodeURIComponent(csrf())}`;

            const json = await apiJson(API.createRole, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body,
            });

            if (!json.success) throw new Error(json.message || "Create role gagal");

            await swalOk("Berhasil", json.message || "Role berhasil dibuat");

            // reset form state
            formEl.reset();
            setCheckedPermissionIds(formEl, []);

            // reset select2 too
            const copySel = qs("#copy_from_role_id", formEl);
            if (copySel && window.jQuery && jQuery(copySel).hasClass("select2-hidden-accessible")) {
                jQuery(copySel).val("").trigger("change");
            }

            modal.hide();
            await RolesTable.reload();
        }

        function bindCopyRole() {
            const copySelect = qs("#copy_from_role_id", formEl);
            if (!copySelect) return;

            const doCopy = async () => {
                const roleId = (copySelect.value || "").trim();
                if (!roleId) return;

                const json = await apiJson(`${API.getRole}?id=${encodeURIComponent(roleId)}`);
                if (!json.success || !json.data) throw new Error(json.message || "Gagal load role sumber");

                setCheckedPermissionIds(formEl, json.data.permission_ids || []);
            };

            // native change (works for both normal select and select2)
            copySelect.addEventListener("change", () => {
                doCopy().catch((err) => {
                    console.error(err);
                    swalErr("Gagal", err.message || "Gagal copy permissions");
                });
            });

            // if select2: also bind on namespaced event to be safe
            if (window.jQuery) {
                jQuery(copySelect).on("change.select2", () => {
                    doCopy().catch((err) => {
                        console.error(err);
                        swalErr("Gagal", err.message || "Gagal copy permissions");
                    });
                });
            }
        }

        function initSelect2InModal() {
            const copySelect = qs("#copy_from_role_id", formEl);
            if (!copySelect) return;

            // Select2 common issue inside modal: dropdown appended to body (outside modal)
            // fix: dropdownParent must be inside modal
            if (window.jQuery && jQuery.fn.select2) {
                const $el = jQuery(copySelect);

                const init = () => {
                    if ($el.hasClass("select2-hidden-accessible")) return;

                    $el.select2({
                        dropdownParent: jQuery(copySelect.dataset.dropdownParent || modalEl),
                        width: "100%",
                        allowClear: true,
                        placeholder: copySelect.dataset.placeholder || "Pilih role",
                    });

                };

                modalEl.addEventListener("shown.bs.modal", init);

                // cleanup to avoid duplicate init if modal reopened
                modalEl.addEventListener("hidden.bs.modal", () => {
                    if ($el.hasClass("select2-hidden-accessible")) $el.select2("destroy");
                });
            }
        }

        function bind() {
            modalEl = qs("#kt_modal_add_role");
            if (!modalEl) return;

            formEl = qs("#kt_modal_add_role_form", modalEl);
            if (!formEl) return;

            modal = new bootstrap.Modal(modalEl);

            initSelect2InModal();
            const fv = setupValidation();

            wireSelectAll(formEl);
            bindCopyRole();
            bindPermissionSearch(modalEl, "#kt_role_permissions_search_add");

            const btnClose = qs('[data-kt-roles-modal-action="close"]', modalEl);
            const btnCancel = qs('[data-kt-roles-modal-action="cancel"]', modalEl);
            const btnSubmit = qs('[data-kt-roles-modal-action="submit"]', modalEl);

            const askClose = (e) => {
                e.preventDefault();
                Swal.fire({
                    text: "Tutup form?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, tutup",
                    cancelButtonText: "Batal",
                    customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-active-light" },
                }).then((r) => r.isConfirmed && modal.hide());
            };

            if (btnClose) btnClose.addEventListener("click", askClose);

            if (btnCancel)
                btnCancel.addEventListener("click", (e) => {
                    e.preventDefault();
                    formEl.reset();
                    setCheckedPermissionIds(formEl, []);

                    const copySel = qs("#copy_from_role_id", formEl);
                    if (copySel && window.jQuery && jQuery(copySel).hasClass("select2-hidden-accessible")) {
                        jQuery(copySel).val("").trigger("change");
                    }

                    modal.hide();
                });

            if (btnSubmit)
                btnSubmit.addEventListener("click", async (e) => {
                    e.preventDefault();

                    if (fv) {
                        const status = await fv.validate();
                        if (status !== "Valid") return;
                    }

                    btnSubmit.setAttribute("data-kt-indicator", "on");
                    btnSubmit.disabled = true;

                    try {
                        await submit();
                    } catch (err) {
                        console.error(err);
                        swalErr("Gagal", err.message || "Create role gagal");
                    } finally {
                        btnSubmit.removeAttribute("data-kt-indicator");
                        btnSubmit.disabled = false;
                    }
                });
        }

        return { init: bind };
    })();

    // ====== Update Role modal ======
    const UpdateRole = (function () {
        let modalEl, formEl, modal;

        function setupValidation() {
            if (typeof FormValidation === "undefined" || !FormValidation.formValidation) return null;
            return FormValidation.formValidation(formEl, {
                fields: {
                    role_name: { validators: { notEmpty: { message: "Role name is required" } } },
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

        async function openWithRoleId(roleId) {
            const hid = ensureHiddenRoleId(formEl);
            hid.value = roleId;

            const json = await apiJson(`${API.getRole}?id=${encodeURIComponent(roleId)}`);
            if (!json.success || !json.data) throw new Error(json.message || "Gagal load role");

            const name = json.data.name || json.data.role_name || "";
            const ids = json.data.permission_ids || [];

            const nameInput = qs('[name="role_name"]', formEl);
            if (nameInput) nameInput.value = name;

            setCheckedPermissionIds(formEl, ids);

            modal.show();
        }

        async function submit() {
            const roleId = qs('input[name="role_id"]', formEl)?.value || "";
            if (!roleId) throw new Error("role_id tidak ditemukan");

            const roleName = (qs('[name="role_name"]', formEl)?.value || "").trim();
            if (!roleName) throw new Error("Role name wajib diisi");

            const permissionIds = getCheckedPermissionIds(formEl);

            // 1) update role name
            const bodyName =
                `role_id=${encodeURIComponent(roleId)}` +
                `&role_name=${encodeURIComponent(roleName)}` +
                `&_csrf=${encodeURIComponent(csrf())}`;

            const jsonName = await apiJson(API.updateRole, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: bodyName,
            });

            if (!jsonName.success) throw new Error(jsonName.message || "Update nama role gagal");

            // 2) update permissions
            const bodyPerms =
                `role_id=${encodeURIComponent(roleId)}` +
                `&permission_ids=${encodeURIComponent(JSON.stringify(permissionIds))}` +
                `&_csrf=${encodeURIComponent(csrf())}`;

            const jsonPerms = await apiJson(API.updateRolePerms, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: bodyPerms,
            });

            if (!jsonPerms.success) throw new Error(jsonPerms.message || "Update permissions gagal");

            await swalOk("Berhasil", "Role berhasil diupdate");
            modal.hide();
            await RolesTable.reload();
        }

        function bind() {
            modalEl = qs("#kt_modal_update_role");
            if (!modalEl) return;

            formEl = qs("#kt_modal_update_role_form", modalEl);
            if (!formEl) return;

            modal = new bootstrap.Modal(modalEl);
            ensureHiddenRoleId(formEl);

            const fv = setupValidation();
            wireSelectAll(formEl);
            bindPermissionSearch(modalEl, "#kt_role_permissions_search_update");

            const btnClose = qs('[data-kt-roles-modal-action="close"]', modalEl);
            const btnCancel = qs('[data-kt-roles-modal-action="cancel"]', modalEl);
            const btnSubmit = qs('[data-kt-roles-modal-action="submit"]', modalEl);

            const askClose = (e) => {
                e.preventDefault();
                Swal.fire({
                    text: "Tutup form?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, tutup",
                    cancelButtonText: "Batal",
                    customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-active-light" },
                }).then((r) => r.isConfirmed && modal.hide());
            };

            if (btnClose) btnClose.addEventListener("click", askClose);

            if (btnCancel)
                btnCancel.addEventListener("click", (e) => {
                    e.preventDefault();
                    modal.hide();
                });

            if (btnSubmit)
                btnSubmit.addEventListener("click", async (e) => {
                    e.preventDefault();

                    if (fv) {
                        const status = await fv.validate();
                        if (status !== "Valid") return;
                    }

                    btnSubmit.setAttribute("data-kt-indicator", "on");
                    btnSubmit.disabled = true;

                    try {
                        await submit();
                    } catch (err) {
                        console.error(err);
                        swalErr("Gagal", err.message || "Update role gagal");
                    } finally {
                        btnSubmit.removeAttribute("data-kt-indicator");
                        btnSubmit.disabled = false;
                    }
                });

            // Delegation: tombol edit role dari card/list
            document.addEventListener("click", async (e) => {
                const btn = e.target.closest('[data-action="edit-role"][data-role-id]');
                if (!btn) return;
                e.preventDefault();

                try {
                    await openWithRoleId(btn.dataset.roleId);
                } catch (err) {
                    console.error(err);
                    swalErr("Error", err.message || "Gagal membuka role");
                }
            });
        }

        return { init: bind };
    })();

    const RolesTable = (function () {
        async function reload() {
            window.location.reload();
        }
        return { reload };
    })();

    function bindPermissionSearch(modalEl, inputSelector) {
    const input = modalEl.querySelector(inputSelector);
    if (!input) return;

    const tbody = modalEl.querySelector("tbody");
    if (!tbody) return;

    const rows = () =>
        Array.from(tbody.querySelectorAll('input[type="checkbox"][name="permissions[]"]'))
        .map((cb) => cb.closest("tr"))
        .filter(Boolean);

    const apply = () => {
        const q = (input.value || "").trim().toLowerCase();
        rows().forEach((tr) => {
        const txt = (tr.textContent || "").toLowerCase();
        const show = !q || txt.includes(q);
        tr.style.display = show ? "" : "none";
        });
    };

    input.addEventListener("input", apply);

    // reset saat modal ditutup
    modalEl.addEventListener("hidden.bs.modal", () => {
        input.value = "";
        apply();
    });

    // apply saat modal dibuka
    modalEl.addEventListener("shown.bs.modal", () => {
        apply();
    });
    }

    function boot() {
        AddRole.init();
        UpdateRole.init();
    }

    if (typeof KTUtil !== "undefined" && typeof KTUtil.onDOMContentLoaded === "function") {
        KTUtil.onDOMContentLoaded(boot);
    } else {
        document.addEventListener("DOMContentLoaded", boot);
    }
})();
