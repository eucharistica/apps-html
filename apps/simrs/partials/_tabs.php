<?php
// apps/simrs/partials/_tabs.php

$tabs = $GLOBALS['EMR_TABS'] ?? [];
$activeKey = null;
foreach ($tabs as $t) {
    if (!empty($t['is_active'])) {
        $activeKey = $t['tab_key'] ?? null;
        break;
    }
}
?>

<style>
    /* Make tabs look like old toolbar menu (underline connected line) */
    .emr-tabbar { display:flex; align-items:center; gap:1rem; width:100%; border-bottom:1px solid var(--bs-border-color); }
    .emr-tabbar-menu { display:flex; align-items:center; gap:1rem; flex:1 1 auto; overflow-x:auto; white-space:nowrap; }
    .emr-tabbar-menu::-webkit-scrollbar { height:6px; }

    .emr-tablink { position:relative; display:inline-flex; align-items:center; gap:.5rem; padding:1rem 0; font-size:1.05rem; font-weight:600; color:var(--bs-gray-700); text-decoration:none; }
    .emr-tablink:hover { color: var(--bs-primary); }
    .emr-tablink.is-active { color: var(--bs-primary); }
    .emr-tablink.is-active::after { content:""; position:absolute; left:0; right:0; bottom:-1px; height:2px; background: var(--bs-primary); }

    .emr-tab-close { width:18px; height:18px; border-radius:6px; border:0; background:transparent; color: var(--bs-gray-500); line-height:1; display:inline-flex; align-items:center; justify-content:center; }
    .emr-tablink:hover .emr-tab-close { color: var(--bs-gray-700); background: var(--bs-gray-200); }
    .emr-tablink.is-active .emr-tab-close { color: var(--bs-gray-700); }

    .emr-tab-actions { flex:0 0 auto; }
</style>

<div class="emr-tabbar">

    <!-- Close dropdown (AdminLTE-like) -->
    <div class="emr-tab-actions dropdown">
        <button class="btn btn-sm btn-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Close
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" data-emr-close-action="close_all">Close All</a></li>
            <li><a class="dropdown-item" href="#" data-emr-close-action="close_other">Close All Other</a></li>
        </ul>
    </div>

    <div class="emr-tabbar-menu" role="tablist">
        <?php foreach ($tabs as $t):
            $isActive = !empty($t['is_active']);
            $url = $t['url'] ?? '#';
            $title = $t['title'] ?? ($t['tab_key'] ?? 'Tab');
            $key = $t['tab_key'] ?? '';
        ?>
            <a href="<?= htmlspecialchars($url) ?>" class="emr-tablink <?= $isActive ? 'is-active' : '' ?>" data-emr-tab="1" data-emr-tab-key="<?= htmlspecialchars($key) ?>">
                <span><?= htmlspecialchars($title) ?></span>
                <button type="button" class="emr-tab-close" aria-label="Close" data-emr-tab-close="1" data-emr-tab-key="<?= htmlspecialchars($key) ?>">&times;</button>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function emrPost(action, tabKey) {
        var body = 'action=' + encodeURIComponent(action) + '&return=1';
        if (tabKey) body += '&tab_key=' + encodeURIComponent(tabKey);

        return fetch('<?= EMR_BASE_URL ?>apps/simrs/tabs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        }).then(function (r) { return r.json(); });
    }

    // Close single tab
    document.addEventListener('click', function (e) {
        var el = e.target;
        if (!el || !el.getAttribute) return;
        if (el.getAttribute('data-emr-tab-close') !== '1') return;

        e.preventDefault();
        e.stopPropagation();

        var key = el.getAttribute('data-emr-tab-key');
        if (!key) return;

        emrPost('close', key)
            .then(function (data) {
                if (data && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                window.location.reload();
            })
            .catch(function(){ window.location.reload(); });

    }, true);

    // Close dropdown actions
    document.addEventListener('click', function (e) {
        var el = e.target;
        if (!el || !el.getAttribute) return;
        var action = el.getAttribute('data-emr-close-action');
        if (!action) return;

        e.preventDefault();
        e.stopPropagation();

        var activeKey = '<?= htmlspecialchars((string)($activeKey ?? '')) ?>';

        if (action === 'close_other') {
            if (!activeKey) return;
            emrPost('close_other', activeKey)
                .then(function(){ window.location.reload(); })
                .catch(function(){ window.location.reload(); });
            return;
        }

        if (action === 'close_all') {
            emrPost('close_all', '')
                .then(function (data) {
                    if (data && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    window.location.href = '<?= EMR_BASE_URL ?>apps/simrs/index.php?page=dashboard';
                })
                .catch(function(){ window.location.href = '<?= EMR_BASE_URL ?>apps/simrs/index.php?page=dashboard'; });
            return;
        }

    }, true);
</script>
