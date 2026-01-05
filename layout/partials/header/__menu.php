<?php
// /layout/partials/header/__menu.php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../../');
?>
<!--begin::Menu wrapper-->
<div class="d-flex align-items-stretch" id="kt_app_header_menu_wrapper">
	<!--begin::Menu holder-->
	<div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true"
		data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}"
		data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}"
		data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true"
		data-kt-swapper-mode="prepend"
		data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_menu_wrapper'}">
		<!--begin::Menu-->
		<div class="menu menu-rounded menu-column menu-lg-row menu-active-bg menu-title-gray-600 menu-state-primary menu-arrow-gray-500 fw-semibold fw-semibold fs-6 align-items-stretch my-5 my-lg-0 px-2 px-lg-0"
			id="#kt_app_header_menu" data-kt-menu="true">
			<!--begin:Menu item-->
			<div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
				data-kt-menu-offset="-250,0"
				class="menu-item here show menu-here-bg menu-lg-down-accordion me-0 me-lg-2">
				<span class="menu-link"><span class="menu-icon"><i class="ki-outline ki-category fs-3"></i></span><span
						class="menu-title">Dashboards</span><span class="menu-arrow d-lg-none"></span></span>
				<div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown p-0 w-100 w-lg-850px">
					<?php include $__emr_root . '/layout/partials/header/_menu/__dashboards.php'; ?>
				</div>
			</div>
			<!--end:Menu item-->

			<!--begin:Menu item-->
			<div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
				data-kt-menu-offset="-400,0" class="menu-item menu-lg-down-accordion me-0 me-lg-2">
				<span class="menu-link"><span class="menu-icon"><i class="ki-outline ki-notepad-bookmark fs-3"></i></span><span
						class="menu-title">Pages</span><span class="menu-arrow d-lg-none"></span></span>
				<div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown p-0">
					<?php include $__emr_root . '/layout/partials/header/_menu/__pages.php'; ?>
				</div>
			</div>
			<!--end:Menu item-->

			<!-- Remaining menu is kept as-is for now -->
			<!--begin:Menu item-->
			<div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
				data-kt-menu-offset="12,0"
				class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
				<span class="menu-link"><span class="menu-icon"><i class="ki-outline ki-abstract-41 fs-3"></i></span><span
						class="menu-title">Apps</span><span class="menu-arrow d-lg-none"></span></span>
				<div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-250px">
					<?php include $__emr_root . '/layout/partials/header/_menu/__apps.php'; ?>
				</div>
			</div>
			<!--end:Menu item-->
		</div>
		<!--end::Menu-->
	</div>
	<!--end::Menu holder-->
</div>
<!--end::Menu wrapper-->
