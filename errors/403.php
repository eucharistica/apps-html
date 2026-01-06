<?php
require_once __DIR__ . '../../apps/config/bootstrap.php';

http_response_code(404);
$asset = EMR_BASE_URL . 'assets/';
$homeUrl = EMR_HOME_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>403 - Restricted Page</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?= $asset ?>media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="<?= $asset ?>plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>css/style.bundle.css" rel="stylesheet" type="text/css" />
    <script>
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">

<div class="d-flex flex-column flex-root" id="kt_app_root">
    <style>
        body { background-image: url('<?= $asset ?>media/auth/bg1.jpg'); }
        [data-bs-theme="dark"] body { background-image: url('<?= $asset ?>media/auth/bg1-dark.jpg'); }
    </style>

    <div class="d-flex flex-column flex-center flex-column-fluid">
        <div class="d-flex flex-column flex-center text-center p-10">
            <div class="card card-flush w-lg-650px py-5">
                <div class="card-body py-15 py-lg-20">
                    <h1 class="fw-bolder fs-2hx text-gray-900 mb-4">Oops!</h1>
                    <div class="fw-semibold fs-6 text-gray-500 mb-7">You are not allowed to access this page.</div>

                    <div class="mb-3">
                        <img src="<?= $asset ?>media/auth/404-error.png" class="mw-100 mh-300px theme-light-show" alt="" />
                        <img src="<?= $asset ?>media/auth/404-error-dark.png" class="mw-100 mh-300px theme-dark-show" alt="" />
                    </div>

                    <div class="mb-0">
                        <a href="<?= htmlspecialchars($homeUrl) ?>" class="btn btn-sm btn-primary">Return Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>var hostUrl = "<?= $asset ?>";</script>
<script src="<?= $asset ?>plugins/global/plugins.bundle.js"></script>
<script src="<?= $asset ?>js/scripts.bundle.js"></script>

</body>
</html>
