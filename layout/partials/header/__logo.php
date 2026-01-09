<?php
// layout/partials/header/__logo.php
$asset = defined('EMR_BASE_URL') ? (EMR_BASE_URL . 'assets/') : 'assets/';
?>
<!--begin::Logo-->
<!--begin::Header mobile toggle-->
<div class="d-flex align-items-center d-lg-none ms-n2 me-2" title="Show sidebar menu">
	<div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_header_menu_toggle">
		<i class="ki-outline ki-abstract-14 fs-2"></i>
	</div>
</div>
<!--end::Header mobile toggle-->
<a href="<?= defined('EMR_HOME_URL') ? EMR_HOME_URL : 'index.php' ?>" class="me-5 me-lg-9">
	<img alt="Logo" src="<?= $asset ?>media/logos/demo53.svg" class="h-25px h-lg-30px theme-light-show" />
	<img alt="Logo" src="<?= $asset ?>media/logos/demo53-dark.svg" class="h-25px h-lg-30px theme-dark-show" />
</a>
<!--end::Logo image-->