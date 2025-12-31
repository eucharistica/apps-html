<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head>
		<title>EMR SIMRS - Sign In</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="shortcut icon" href="assets/media/logos/favicon.ico" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle-->
		<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
		<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->

		<?php include 'partials/theme-mode/_init.php' ?>
		<script>
			if (window.top != window.self) {
				window.top.location.replace(window.self.location.href);
			}
		</script>
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="app-blank">
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<!--begin::Authentication - Sign-in -->
			<div class="d-flex flex-column flex-lg-row flex-column-fluid">
				<!--begin::Logo-->
				<a href="apps/simrs/home/index.php" class="d-block d-lg-none mx-auto py-20">
					<img alt="Logo" src="assets/media/logos/default.svg" class="theme-light-show h-25px" />
					<img alt="Logo" src="assets/media/logos/default-dark.svg" class="theme-dark-show h-25px" />
				</a>
				<!--end::Logo-->
				<!--begin::Aside-->
				<div class="d-flex flex-column flex-column-fluid flex-center w-lg-50 p-10">
					<!--begin::Wrapper-->
					<div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
						<!--begin::Header-->
						<div class="d-flex flex-stack py-2">
							<div class="me-2"></div>
							<div class="m-0"></div>
						</div>
						<!--end::Header-->
						<!--begin::Body-->
						<div class="py-20">
							<!--begin::Form-->
							<form class="form w-100" method="post" action="apps/auth/sign-in.php">
								<div class="card-body">
									<div class="text-start mb-10">
										<h1 class="text-gray-900 mb-3 fs-3x">Sign In</h1>
										<div class="text-gray-500 fw-semibold fs-6">Silakan masuk untuk melanjutkan</div>
									</div>

									<div class="fv-row mb-8">
										<input type="text" placeholder="Username" name="username" autocomplete="off" class="form-control form-control-solid" required />
									</div>

									<div class="fv-row mb-7">
										<input type="password" placeholder="Password" name="password" autocomplete="off" class="form-control form-control-solid" required />
									</div>

									<div class="d-flex flex-stack">
										<button type="submit" class="btn btn-primary me-2 flex-shrink-0">
											<span class="indicator-label">Sign In</span>
										</button>
									</div>
								</div>
							</form>
							<!--end::Form-->
						</div>
						<!--end::Body-->
						<!--begin::Footer-->
						<div class="m-0">
							<small class="text-gray-500">EMR SIMRS</small>
						</div>
						<!--end::Footer-->
					</div>
					<!--end::Wrapper-->
				</div>
				<!--end::Aside-->
				<!--begin::Body-->
				<div class="d-none d-lg-flex flex-lg-row-fluid w-50 bgi-size-cover bgi-position-y-center bgi-position-x-start bgi-no-repeat" style="background-image: url(assets/media/auth/bg11.png)"></div>
				<!--begin::Body-->
			</div>
			<!--end::Authentication - Sign-in-->
		</div>
		<!--end::Root-->

		<!--begin::Javascript-->
		<script>var hostUrl = "assets/";</script>
		<script src="assets/plugins/global/plugins.bundle.js"></script>
		<script src="assets/js/scripts.bundle.js"></script>
		<script>
			// SweetAlert smoke test (will be used after validation flow)
			window.EMR = window.EMR || {};
		</script>
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>
