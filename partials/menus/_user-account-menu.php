<?php
require_once __DIR__ . '/../../apps/config/bootstrap.php';

// Basic session user info
$user = $_SESSION['emr_user'] ?? [];

$username = $user['username'] ?? 'User';
$email = $user['email'] ?? null;

// Photo
$photoPath = $user['profile_photo_path'] ?? null;
// Support storing relative path like: uploads/profile_photo/admin.jpg
$photoUrl = $photoPath
    ? (EMR_BASE_URL . ltrim($photoPath, '/'))
    : (EMR_BASE_URL . 'assets/media/avatars/300-5.jpg');

$signOutUrl = EMR_BASE_URL . 'apps/auth/sign-out.php';
?>

<!--begin::User account menu-->
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
    data-kt-menu="true">

    <!--begin::Menu item-->
    <div class="menu-item px-3">
        <div class="menu-content d-flex align-items-center px-3">
            <!--begin::Avatar-->
            <div class="symbol symbol-50px me-5">
                <img alt="Profile photo" src="<?= htmlspecialchars($photoUrl) ?>" />
            </div>
            <!--end::Avatar-->

            <!--begin::Username-->
            <div class="d-flex flex-column">
                <div class="fw-bold d-flex align-items-center fs-5">
                    <?= htmlspecialchars($username) ?>
                </div>
                <?php if ($email): ?>
                    <span class="fw-semibold text-muted fs-7"><?= htmlspecialchars($email) ?></span>
                <?php else: ?>
                    <span class="fw-semibold text-muted fs-7">&nbsp;</span>
                <?php endif; ?>
            </div>
            <!--end::Username-->
        </div>
    </div>
    <!--end::Menu item-->

    <div class="separator my-2"></div>

    <!--begin::Menu item-->
    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
        data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
        <a href="#" class="menu-link px-5">
            <span class="menu-title position-relative">
                Mode
                <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                    <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                    <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                </span>
            </span>
        </a>

        <?php include EMR_ROOT . '/partials/theme-mode/__menu.php'; ?>
    </div>
    <!--end::Menu item-->

    <div class="separator my-2"></div>

    <!--begin::Menu item-->
    <div class="menu-item px-5">
        <a href="<?= htmlspecialchars($signOutUrl) ?>" class="menu-link px-5">
            Sign Out
        </a>
    </div>
    <!--end::Menu item-->
</div>
<!--end::User account menu-->
