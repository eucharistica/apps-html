<!--begin::Navbar-->
<div class="app-navbar flex-shrink-0">
    <!--begin::User menu-->
    <div class="app-navbar-item me-3" id="kt_header_user_menu_toggle">
        <!--begin::Menu wrapper-->
        <div class="btn btn-icon btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px"
            data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
            data-kt-menu-placement="bottom-end">
            <i class="ki-outline ki-user fs-2"></i>
        </div>

        <?php include 'partials/menus/_user-account-menu.php' ?>
        <!--end::Menu wrapper-->
    </div>
    <!--end::User menu-->

    <!--begin::My apps links-->
    <div class="app-navbar-item me-3">
        <!--begin::Menu wrapper-->
        <div class="btn btn-icon btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px"
            data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
            data-kt-menu-placement="bottom-end">
            <i class="ki-outline ki-element-11 fs-2"></i>
        </div>

        <?php include 'partials/menus/_my-apps-menu.php' ?>
        <!--end::Menu wrapper-->
    </div>
    <!--end::My apps links-->


    <!--begin::Sidebar menu toggle-->
    <!--end::Sidebar menu toggle-->
</div>
<!--end::Navbar-->