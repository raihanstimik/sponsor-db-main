/**
 * Silent Session Keep-Alive & Standby Handler
 *
 * Menjaga sesi tetap aktif di latar belakang (standby sampai logout),
 * mencegah dialog kedaluwarsa sesi bawaan Livewire/browser,
 * dan memastikan pengalaman kerja tanpa interupsi popup.
 */

let lastPing = Date.now();
const PING_INTERVAL = 5 * 60 * 1000; // 5 menit

/**
 * Kirim heartbeat ping hening untuk memperbarui sesi di latar belakang.
 */
export function keepSessionAlive() {
  const now = Date.now();
  // Cegah spam ping berulang dalam selang kurang dari 30 detik
  if (now - lastPing < 30000) {
    return;
  }
  lastPing = now;

  fetch('/livewire-keepalive', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    cache: 'no-store',
  }).catch(() => {
    // Abaikan kegagalan jaringan sementara tanpa memunculkan dialog
  });
}

/**
 * Inisialisasi keep-alive dan penanganan hening sesi kedaluwarsa (tanpa popup).
 */
export function initSessionKeepalive() {
  // 1. Jalankan ping awal dan pasang interval berkala
  keepSessionAlive();
  setInterval(keepSessionAlive, PING_INTERVAL);

  // 2. Sentuh sesi saat tab aktif kembali (window focus atau visibilitychange)
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      keepSessionAlive();
    }
  });
  window.addEventListener('focus', keepSessionAlive);

  // 3. Tangani kegagalan status 419 di Livewire secara hening (tanpa popup atau confirm)
  const setupLivewireHooks = () => {
    if (!window.Livewire) return;

    if (typeof window.Livewire.hook === 'function') {
      try {
        window.Livewire.hook('request', ({ fail }) => {
          if (typeof fail === 'function') {
            fail(({ status, preventDefault }) => {
              if (status === 419) {
                if (typeof preventDefault === 'function') {
                  preventDefault();
                }
                // Muat ulang halaman secara hening untuk menyegarkan token CSRF
                window.location.reload();
              }
            });
          }
        });
      } catch (e) {
        // Safe ignore
      }
    }
  };

  if (window.Livewire) {
    setupLivewireHooks();
  } else {
    document.addEventListener('livewire:init', setupLivewireHooks, { once: true });
  }

  // 4. Cegah window.confirm bawaan browser jika Livewire memanggil pesan kedaluwarsa
  const originalConfirm = window.confirm;
  window.confirm = function (message) {
    if (
      typeof message === 'string' &&
      (message.includes('This page has expired') ||
        message.includes('The server returned an empty response') ||
        message.includes('page has expired') ||
        message.includes('expired'))
    ) {
      // Muat ulang langsung tanpa popup
      window.location.reload();
      return false;
    }
    return originalConfirm.apply(this, arguments);
  };
}

