<?php 
 $page = $_GET['page'] ?? 'index';
?>

<!--begin::Toolbar menu-->
<div class="app-toolbar-menu menu menu-title-gray-800 menu-state-primary flex-wrap fs-5 fw-semibold w-100">
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 active" href="?page=index">
			<span class="menu-title">
				Summary </span>
		</a>
	</div>
	<!--end::Menu item-->
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 " href="?page=apps/projects/list">
			<span class="menu-title">
				Projects </span>
		</a>
	</div>
	<!--end::Menu item-->
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 " href="?page=apps/subscriptions/list">
			<span class="menu-title">
				Subscriptions </span>
		</a>
	</div>
	<!--end::Menu item-->
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 " href="?page=apps/file-manager/folders">
			<span class="menu-title">
				Files </span>
		</a>
	</div>
	<!--end::Menu item-->
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 " href="?page=apps/support-center/contact">
			<span class="menu-title">
				Support </span>
		</a>
	</div>
	<!--end::Menu item-->
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 " href="?page=apps/customers/list">
			<span class="menu-title">
				Customers </span>
		</a>
	</div>
	<!--end::Menu item-->
</div>
<!--begin::Toolbar menu-->