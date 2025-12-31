<?php
require_once __DIR__ . '/apps/auth/guest.php';
require_once __DIR__ . '/apps/config/security.php';

emr_redirect_if_logged_in();
$csrf = emr_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>EMR SIMRS - Sign In</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="shortcut icon" href="assets/media/logos/favicon.ico" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
		<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />

		<?php include 'partials/theme-mode/_init.php' ?>
		<script>
			if (window.top != window.self) {
				window.top.location.replace(window.self.location.href);
			}
		</script>
	</head>
	<body id="kt_body" class="app-blank">
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<div class="d-flex flex-column flex-lg-row flex-column-fluid">
				<a href="apps/simrs/home/index.php" class="d-block d-lg-none mx-auto py-20">
					<img alt="Logo" src="assets/media/logos/default.svg" class="theme-light-show h-25px" />
					<img alt="Logo" src="assets/media/logos/default-dark.svg" class="theme-dark-show h-25px" />
				</a>

				<div class="d-flex flex-column flex-column-fluid flex-center w-lg-50 p-10">
					<div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
						<div class="d-flex flex-stack py-2">
							<div class="me-2"></div>
							<div class="m-0"></div>
						</div>

						<div class="py-20">
							<form class="form w-100" method="post" action="apps/auth/sign-in.php">
								<input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>" />
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
						</div>

						<div class="m-0">
							<small class="text-gray-500">EMR SIMRS</small>
						</div>
					</div>
				</div>

				<div class="d-none d-lg-flex flex-lg-row-fluid w-50 bgi-size-cover bgi-position-y-center bgi-position-x-start bgi-no-repeat" style="background-image: url(assets/media/auth/bg11.png)"></div>
			</div>
		</div>

		<script>var hostUrl = "assets/";</script>
		<script src="assets/plugins/global/plugins.bundle.js"></script>
		<script src="assets/js/scripts.bundle.js"></script>
		<script>
			(function () {
				var params = new URLSearchParams(window.location.search);
				var err = params.get('error');
				if (!err || typeof Swal === 'undefined') return;

				var title = 'Login gagal';
				var text = 'Silakan coba lagi.';
				if (err === 'empty') text = 'Username dan password wajib diisi.';
				if (err === 'invalid') text = 'Username atau password salah.';
				if (err === 'inactive') text = 'Akun tidak aktif.';
				if (err === 'csrf') text = 'Sesi tidak valid. Silakan refresh halaman dan coba lagi.';
				if (err === 'hubungi_it') text = 'Terlalu banyak percobaan. Hubungi Admin/IT.';
				if (err === 'locked') text = 'Akun/percobaan login sedang dikunci sementara. Coba lagi nanti.';
				if (err === 'server') text = 'Terjadi kesalahan server. Hubungi admin.';

				Swal.fire({ icon: 'error', title: title, text: text, confirmButtonText: 'OK' });
			})();
		</script>
	</body>
</html>
