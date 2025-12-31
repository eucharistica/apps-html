<?php
// apps/simrs/partials/_tabs.php

$tabs = $GLOBALS['EMR_TABS'] ?? [];
?>

<style>
    .emr-tabs { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
    .emr-tab { display:inline-flex; align-items:center; gap:.5rem; }
    .emr-tab-close { cursor:pointer; border:0; background:transparent; line-height:1; }
</style>

<div class="emr-tabs">
    <?php foreach ($tabs as $t):
        $isActive = !empty($t['is_active']);
        $url = $t['url'] ?? '#';
        $title = $t['title'] ?? ($t['tab_key'] ?? 'Tab');
        $key = $t['tab_key'] ?? '';
    ?>
        <a href="<?= htmlspecialchars($url) ?>" class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-light' ?> emr-tab" data-emr-tab="1">
            <span><?= htmlspecialchars($title) ?></span>
            <button type="button" class="emr-tab-close" aria-label="Close" data-emr-tab-close="1" data-emr-tab-key="<?= htmlspecialchars($key) ?>">&times;</button>
        </a>
    <?php endforeach; ?>
</div>

<script>
    document.addEventListener('click', function (e) {
        var closeBtn = e.target && e.target.getAttribute ? (e.target.getAttribute('data-emr-tab-close') === '1' ? e.target : null) : null;
        if (!closeBtn) return;

        // Stop anchor navigation
        e.preventDefault();
        e.stopPropagation();

        var key = closeBtn.getAttribute('data-emr-tab-key');
        if (!key) return;

        fetch('<?= EMR_BASE_URL ?>apps/simrs/tabs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=close&tab_key=' + encodeURIComponent(key) + '&return=1'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            // If server suggests redirect target, go there.
            if (data && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            window.location.reload();
        })
        .catch(function(){ window.location.reload(); });
    }, true);
</script>
