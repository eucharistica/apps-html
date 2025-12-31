<?php
// apps/simrs/partials/_tabs.php

$tabs = $GLOBALS['EMR_TABS'] ?? [];

?>
<div class="d-flex align-items-center gap-2 flex-wrap">
    <?php foreach ($tabs as $t):
        $isActive = !empty($t['is_active']);
        $url = $t['url'] ?? '#';
        $title = $t['title'] ?? ($t['tab_key'] ?? 'Tab');
        $key = $t['tab_key'] ?? '';
    ?>
        <a href="<?= htmlspecialchars($url) ?>" class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-light' ?>">
            <?= htmlspecialchars($title) ?>
            <span class="ms-2" data-emr-tab-close="1" data-emr-tab-key="<?= htmlspecialchars($key) ?>" style="cursor:pointer">&times;</span>
        </a>
    <?php endforeach; ?>

    <script>
        document.addEventListener('click', function (e) {
            var el = e.target;
            if (!el || !el.getAttribute) return;
            if (el.getAttribute('data-emr-tab-close') !== '1') return;
            e.preventDefault();
            e.stopPropagation();

            var key = el.getAttribute('data-emr-tab-key');
            if (!key) return;

            fetch('<?= EMR_BASE_URL ?>apps/simrs/tabs.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=close&tab_key=' + encodeURIComponent(key)
            }).then(function(){
                window.location.reload();
            });
        });
    </script>
</div>
