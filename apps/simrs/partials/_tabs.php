<?php
// apps/simrs/partials/_tabs.php (updated for iframe + sortable)

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
    .emr-tabbar { display:flex; align-items:center; gap:1rem; width:100%; border-bottom:1px solid var(--bs-border-color); }
    .emr-tabbar-menu { display:flex; align-items:center; gap:1rem; flex:1 1 auto; overflow-x:auto; white-space:nowrap; }
    .emr-tabbar-menu::-webkit-scrollbar { height:6px; }

    .emr-tablink { position:relative; display:inline-flex; align-items:center; gap:.5rem; padding:1rem 0; font-size:1.05rem; font-weight:600; color:var(--bs-gray-700); text-decoration:none; cursor:pointer; }
    .emr-tablink:hover { color: var(--bs-primary); }
    .emr-tablink.is-active { color: var(--bs-primary); }
    .emr-tablink.is-active::after { content:""; position:absolute; left:0; right:0; bottom:-1px; height:2px; background: var(--bs-primary); }

    .emr-tab-close { width:18px; height:18px; border-radius:6px; border:0; background:transparent; color: var(--bs-gray-500); line-height:1; display:inline-flex; align-items:center; justify-content:center; }
    .emr-tablink:hover .emr-tab-close { color: var(--bs-gray-700); background: var(--bs-gray-200); }
    .emr-tablink.is-active .emr-tab-close { color: var(--bs-gray-700); }

    .emr-tab-actions { flex:0 0 auto; }

    #emr-tab-content { background:#fff; }
    #emr-tab-content iframe { position:absolute; inset:0; width:100%; height:100%; border:0; display:none; }
    #emr-tab-content iframe.is-active { display:block; }
</style>

<div class="emr-tabbar">

    <div class="emr-tab-actions dropdown">
        <button class="btn btn-sm btn-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Close
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" data-emr-close-action="close_all">Close All</a></li>
        </ul>
    </div>

    <div class="emr-tabbar-menu" id="emr-tabbar-menu" role="tablist">
        <?php foreach ($tabs as $t):
            $isActive = !empty($t['is_active']);
            $url = $t['url'] ?? '#';
            $title = $t['title'] ?? ($t['tab_key'] ?? 'Tab');
            $key = $t['tab_key'] ?? '';
            $isDashboard = ($key === 'dashboard');
        ?>
            <a href="<?= htmlspecialchars($url) ?>" class="emr-tablink <?= $isActive ? 'is-active' : '' ?>" data-emr-tab="1" data-emr-tab-key="<?= htmlspecialchars($key) ?>" data-emr-tab-url="<?= htmlspecialchars($url) ?>" id="emr-tab-<?= htmlspecialchars($key) ?>">
                <span><?= htmlspecialchars($title) ?></span>
                <?php if (!$isDashboard): ?>
                <button type="button" class="emr-tab-close" aria-label="Close" data-emr-tab-close="1" data-emr-tab-key="<?= htmlspecialchars($key) ?>">&times;</button>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- <script src="<?= EMR_BASE_URL ?>assets/plugins/custom/sortablejs/sortable.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    function emrTabsPost(action, tabKey, extra) {
        var body = 'action=' + encodeURIComponent(action) + '&return=1';
        if (tabKey) body += '&tab_key=' + encodeURIComponent(tabKey);
        if (extra) {
            for (var k in extra) {
                body += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(extra[k]);
            }
        }

        return fetch('<?= EMR_BASE_URL ?>apps/simrs/tabs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        }).then(function (r) { return r.json(); });
    }

    function emrActivateTab(key, url) {
        if (!key) return;
        var content = document.getElementById('emr-tab-content');
        if (!content) return;

        // toggle active class on tab header
        document.querySelectorAll('.emr-tablink').forEach(function (el) {
            el.classList.toggle('is-active', el.getAttribute('data-emr-tab-key') === key);
        });

        // find or create iframe
        var iframeId = 'emr-iframe-' + key;
        var iframe = document.getElementById(iframeId);

        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = iframeId;
            iframe.src = url || '';
            iframe.setAttribute('data-emr-tab-key', key);
            content.appendChild(iframe);
        }

        // show active iframe, hide others
        content.querySelectorAll('iframe').forEach(function (f) {
            f.classList.toggle('is-active', f === iframe);
        });
    }

    document.addEventListener('click', function (e) {
        var el = e.target;
        if (!el) return;

        // close single tab
        if (el.getAttribute && el.getAttribute('data-emr-tab-close') === '1') {
            e.preventDefault();
            e.stopPropagation();

            var key = el.getAttribute('data-emr-tab-key');
            if (!key) return;

            emrTabsPost('close', key)
                .then(function (data) {
                    var iframe = document.getElementById('emr-iframe-' + key);
                    if (iframe) iframe.remove();

                    if (data && data.redirect) {
                        // when redirect is provided, navigate whole page (easier sync)
                        window.location.href = data.redirect;
                        return;
                    }
                    window.location.reload();
                })
                .catch(function(){ window.location.reload(); });
            return;
        }

        // click tab header -> iframe logic
        var tab = el.closest && el.closest('.emr-tablink');
        if (tab && tab.getAttribute('data-emr-tab') === '1') {
            e.preventDefault();

            var key = tab.getAttribute('data-emr-tab-key');
            var url = tab.getAttribute('data-emr-tab-url');
            emrActivateTab(key, url);
            return;
        }
    }, true);

    // Dropdown Close All / Other
    document.addEventListener('click', function (e) {
        var el = e.target;
        if (!el || !el.getAttribute) return;
        var action = el.getAttribute('data-emr-close-action');
        if (!action) return;

        e.preventDefault();
        e.stopPropagation();

        var active = document.querySelector('.emr-tablink.is-active');
        var activeKey = active ? active.getAttribute('data-emr-tab-key') : '';

        if (action === 'close_all') {
            emrTabsPost('close_all', '')
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

    // Init active iframe on first load (server-side active flag)
    document.addEventListener('DOMContentLoaded', function () {
        var active = document.querySelector('.emr-tablink.is-active');
        if (!active) return;
        emrActivateTab(
            active.getAttribute('data-emr-tab-key'),
            active.getAttribute('data-emr-tab-url')
        );
    });

    // SortableJS – drag to reorder tabs (UI only for now)
    document.addEventListener('DOMContentLoaded', function () {
        var tabbar = document.getElementById('emr-tabbar-menu');
        if (!tabbar || typeof Sortable === 'undefined') return;

        Sortable.create(document.getElementById('emr-tabbar-menu'), {
            animation: 150,
            direction: 'horizontal',
            draggable: '.emr-tablink',
            filter: '.emr-tab-close',
            preventOnFilter: false
        });
    });
</script>
