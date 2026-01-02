<?php
// apps/home/page.php
$apps = $GLOBALS['EMR_HOME_APPS'] ?? [];
?>

<div class="d-flex flex-column flex-center" style="min-height: calc(100vh - 180px);">
    <div class="w-100" style="max-width: 1000px;">
        <div class="text-center mb-10">
            <h1 class="fw-bold text-gray-900 mb-2">Aplikasi</h1>
            <div class="text-muted fw-semibold">Pilih aplikasi untuk melanjutkan</div>
        </div>

        <div class="row g-6 justify-content-center">
            <?php foreach ($apps as $app): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?= EMR_BASE_URL . ltrim(($app['url'] ?? '#'), '/') ?>"
                       class="card card-flush h-100 border-hover-primary text-decoration-none">
                        <div class="card-body d-flex flex-column flex-center text-center p-6">
                            <div class="symbol symbol-60px mb-4">
                                <span class="symbol-label bg-light-primary">
                                    <i class="<?= htmlspecialchars($app['icon'] ?? 'ki-outline ki-grid') ?> fs-2x text-primary">
                                    </i>
                                </span>
                            </div>
                            <div class="fw-bold text-gray-900"><?= htmlspecialchars($app['title'] ?? '-') ?></div>
                            <?php if (!empty($app['subtitle'])): ?>
                                <div class="text-muted fw-semibold fs-7 mt-1"><?= htmlspecialchars($app['subtitle']) ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
