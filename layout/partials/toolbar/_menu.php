<?php 
 $page = $_GET['page'] ?? 'index';
?>

<!--begin::Toolbar menu-->
<div class="app-toolbar-menu menu menu-title-gray-800 menu-state-primary flex-wrap fs-5 fw-semibold w-100">
	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 <?= $page === 'index' ? 'active' : '' ?>" href="?page=index">
			<span class="menu-title">Dashboard</span>
		</a>
	</div>
	<!--end::Menu item-->

	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 <?= $page === 'rawat-jalan' ? 'active' : '' ?>" href="?page=rawat-jalan">
			<span class="menu-title">Rawat Jalan</span>
		</a>
	</div>
	<!--end::Menu item-->

	<!--begin::Menu item-->
	<div class="menu-item">
		<a class="menu-link py-4 <?= $page === 'rawat-inap' ? 'active' : '' ?>" href="?page=rawat-inap">
			<span class="menu-title">Rawat Inap</span>
		</a>
	</div>
	<!--end::Menu item-->
</div>
<!--begin::Toolbar menu-->
