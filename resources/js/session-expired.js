/**
 * Custom Session Expired & CSRF Mismatch Handler (Anti AI-Slop, Enterprise Grade).
 *
 * Menggantikan dialog native browser `confirm("This page has expired...")` bawaan Livewire
 * dengan modal dialog terintegrasi yang elegan, berestetika brand ICM, dan mendukung
 * tema terang & gelap secara konsisten.
 */

let modalElement = null;
let isReloading = false;

/**
 * Tampilkan modal Sesi Telah Berakhir.
 */
export function showSessionExpiredModal() {
  if (modalElement && document.body.contains(modalElement)) {
    return;
  }

  // Bersihkan elemen lama jika ada
  const existing = document.getElementById('session-expired-modal');
  if (existing) {
    existing.remove();
  }

  modalElement = document.createElement('div');
  modalElement.id = 'session-expired-modal';
  modalElement.className = 'session-expired-backdrop';
  modalElement.setAttribute('role', 'dialog');
  modalElement.setAttribute('aria-modal', 'true');
  modalElement.setAttribute('aria-labelledby', 'session-expired-title');

  modalElement.innerHTML = `
    <div class="session-expired-card" id="session-expired-card">
      <div class="session-expired-badge-row">
        <div class="session-expired-badge">
          <span class="session-expired-badge-dot"></span>
          <span>Protokol Keamanan</span>
        </div>
        <span class="session-expired-code">HTTP 419 • CSRF</span>
      </div>

      <div class="session-expired-hero">
        <div class="session-expired-icon-wrap">
          <svg class="session-expired-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <path d="M12 8v4"/>
            <path d="M12 16h.01"/>
          </svg>
        </div>
        <div class="session-expired-title-group">
          <h3 id="session-expired-title" class="session-expired-title">Sesi Anda Telah Berakhir</h3>
          <p class="session-expired-subtitle">Halaman atau token autentikasi telah kedaluwarsa</p>
        </div>
      </div>

      <div class="session-expired-body">
        <p>
          Demi melindungi kerahasiaan data sponsor dan menjaga keamanan akun Anda, sistem memerlukan token sesi baru.
          Silakan muat ulang halaman untuk memperbarui token dan melanjutkan aktivitas.
        </p>
      </div>

      <div class="session-expired-footer">
        <button type="button" class="session-expired-btn-cancel" id="session-expired-btn-cancel">
          Tutup
        </button>
        <button type="button" class="session-expired-btn-reload" id="session-expired-btn-reload">
          <svg class="session-expired-reload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span id="session-expired-reload-text">Muat Ulang Halaman</span>
        </button>
      </div>
    </div>
  `;

  document.body.appendChild(modalElement);

  // Focus trap ke tombol reload
  const reloadBtn = document.getElementById('session-expired-btn-reload');
  const cancelBtn = document.getElementById('session-expired-btn-cancel');
  const card = document.getElementById('session-expired-card');

  if (reloadBtn) {
    reloadBtn.focus();
  }

  const handleReload = () => {
    if (isReloading) return;
    isReloading = true;
    if (reloadBtn) {
      reloadBtn.disabled = true;
      reloadBtn.classList.add('is-loading');
      const text = document.getElementById('session-expired-reload-text');
      if (text) text.textContent = 'Memuat Ulang...';
    }
    window.location.reload();
  };

  const handleClose = () => {
    if (isReloading) return;
    if (modalElement) {
      modalElement.classList.add('is-closing');
      setTimeout(() => {
        if (modalElement && modalElement.parentNode) {
          modalElement.parentNode.removeChild(modalElement);
          modalElement = null;
        }
      }, 200);
    }
  };

  if (reloadBtn) {
    reloadBtn.addEventListener('click', handleReload);
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', handleClose);
  }

  // Backdrop click menutup modal
  modalElement.addEventListener('click', (e) => {
    if (e.target === modalElement) {
      handleClose();
    }
  });

  // Keyboard navigation
  const keyHandler = (e) => {
    if (!modalElement || !document.body.contains(modalElement)) {
      document.removeEventListener('keydown', keyHandler);
      return;
    }
    if (e.key === 'Escape') {
      e.preventDefault();
      handleClose();
    } else if (e.key === 'Enter' && e.target !== cancelBtn) {
      e.preventDefault();
      handleReload();
    }
  };

  document.addEventListener('keydown', keyHandler);
}

/**
 * Inisialisasi interceptor Livewire & fallback window.confirm.
 */
export function initSessionExpiredInterceptor() {
  // 1. Livewire 3 Hook Interception (mencegah confirm bawaan Livewire)
  const setupLivewireHooks = () => {
    if (!window.Livewire) return;

    // A. Menggunakan interceptRequest resmi Livewire 3
    if (typeof window.Livewire.interceptRequest === 'function') {
      window.Livewire.interceptRequest(({ onError }) => {
        if (typeof onError === 'function') {
          onError(({ response, preventDefault }) => {
            if (response && response.status === 419) {
              if (typeof preventDefault === 'function') {
                preventDefault();
              }
              showSessionExpiredModal();
            }
          });
        }
      });
    }

    // B. Menggunakan hook('request') fallback untuk kompatibilitas ganda
    if (typeof window.Livewire.hook === 'function') {
      try {
        window.Livewire.hook('request', ({ fail }) => {
          if (typeof fail === 'function') {
            fail(({ status, preventDefault }) => {
              if (status === 419) {
                if (typeof preventDefault === 'function') {
                  preventDefault();
                }
                showSessionExpiredModal();
              }
            });
          }
        });
      } catch (e) {
        // Safe ignore jika hook request sudah didaftarkan
      }
    }
  };

  if (window.Livewire) {
    setupLivewireHooks();
  } else {
    document.addEventListener('livewire:init', setupLivewireHooks, { once: true });
  }

  // 2. Fallback impenetrable: Cegah window.confirm jika memuat teks kedaluwarsa sesi
  const originalConfirm = window.confirm;
  window.confirm = function (message) {
    if (
      typeof message === 'string' &&
      (message.includes('This page has expired') ||
        message.includes('The server returned an empty response') ||
        message.includes('page has expired'))
    ) {
      showSessionExpiredModal();
      return false; // Jangan reload otomatis lewat alert native browser
    }
    return originalConfirm.apply(this, arguments);
  };
}

// Ekspos global untuk testing langsung via console browser
window.showSessionExpiredModal = showSessionExpiredModal;
window.testSessionExpiredModal = showSessionExpiredModal;

