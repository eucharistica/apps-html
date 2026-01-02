<?php
// layout/partials/header/__logo.php
$asset = defined('EMR_BASE_URL') ? (EMR_BASE_URL . 'assets/') : 'assets/';
?>
<!--begin::Logo-->
<div class="app-header-logo d-flex flex-stack px-lg-11 mb-2" id="kt_app_header_logo">
	<a href="<?= defined('EMR_HOME_URL') ? EMR_HOME_URL : 'index.php' ?>">
		<img alt="Logo" src="<?= $asset ?>media/logos/demo53.svg" class="h-20px h-lg-30px" />
	</a>
</div>
<!--end::Logo-->
