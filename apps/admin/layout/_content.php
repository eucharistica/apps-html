<?php
// Guard: Pastikan ini tidak di-include berkali-kali
if (defined('EMR_CONTENT_LOADED')) {
    return; // Sudah di-load, jangan load lagi
}
define('EMR_CONTENT_LOADED', true);

$contentFile = $GLOBALS['EMR_CONTENT_FILE'] ?? null;

if (is_string($contentFile) && file_exists($contentFile)):
    // Set scope terbatas untuk avoid variable collision
    $_emr_content_path = $contentFile;
    unset($contentFile);
    
    // Include dengan scope terisolasi
    include $_emr_content_path;
else: ?>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title">Content not found</h3>
        </div>
        <div class="card-body">
            <p>File tidak ditemukan: <?= htmlspecialchars($contentFile ?? 'undefined') ?></p>
        </div>
    </div>
<?php endif; ?>
