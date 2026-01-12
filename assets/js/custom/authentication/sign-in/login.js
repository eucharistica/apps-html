(function () {
    var params = new URLSearchParams(window.location.search);
    var err = params.get('error');
    if (!err || typeof Swal === 'undefined') return;

    var title = 'Login gagal';
    var text = 'Silakan coba lagi.';
    var logout = params.get('logout');
    if (err === 'empty') text = 'Username dan password wajib diisi.';
    if (err === 'invalid') text = 'Username atau password salah.';
    if (err === 'inactive') text = 'Akun tidak aktif.';
    if (err === 'csrf') text = 'Sesi tidak valid. Silakan refresh halaman dan coba lagi.';
    if (err === 'hubungi_it') text = 'Terlalu banyak percobaan. Hubungi Admin/IT.';
    if (err === 'locked') text = 'Akun/percobaan login sedang dikunci sementara. Coba lagi nanti.';
    if (err === 'server') text = 'Terjadi kesalahan server. Hubungi admin.';
    if (err === 'ip_not_allowed') text = 'Login ditolak: Tidak diizinkan untuk login diluar jaringan RS.';
    if (err === 'session_updated') text = 'Akses akun berubah. Silakan login ulang untuk memuat izin terbaru.';
    
    if (logout === '1') {
    Swal.fire({ icon: 'success', title: 'Logout berhasil', text: 'Sampai jumpa.', confirmButtonText: 'OK' });
    }
    Swal.fire({ icon: 'error', title: title, text: text, confirmButtonText: 'OK' });
})();

(function () {
    const form = document.getElementById('emr_login_form');
    if (!form || typeof Swal === 'undefined') return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const fd = new FormData(form);

        try {
            const res = await fetch(form.action, { method: 'POST', body: fd });
            const json = await res.json();

            if (!json.success) {
                Swal.fire({ icon: 'error', title: 'Login gagal', text: json.message || 'Silakan coba lagi.' });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Login berhasil',
                text: json.message || 'You have successfully logged in!',
                confirmButtonText: 'Ok, got it!',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary' }
            }).then(() => {
                window.location.href = (json.data && json.data.redirect) ? json.data.redirect : '<?= EMR_HOME_URL ?>';
            });

        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Server error. Coba lagi.' });
        }
    });
})();