/**
 * SIPPINTER — Offline Register Manager
 * Auto-sync, UI indicator, form interceptor, dan cache manager
 *
 * Depends on: offline-db.js (OfflineDB)
 */
const OfflineRegister = (() => {
    // ── Config ─────────────────────────────────────────────────────────
    let _csrfToken  = null;
    let _syncUrl    = null;
    let _cacheUrl   = null;
    let _isSyncing  = false;

    // ── Init ───────────────────────────────────────────────────────────

    async function init(config = {}) {
        _csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
        _syncUrl   = config.syncUrl   || '/registration/offline-sync';
        _cacheUrl  = config.cacheUrl  || '/registration/cache-data';

        // Inisialisasi DB
        await OfflineDB.open();

        // Render badge pending count di sidebar & header
        await updatePendingBadge();

        // Pasang event listener online/offline
        window.addEventListener('online',  handleOnline);
        window.addEventListener('offline', handleOffline);

        // Set initial UI state
        updateNetworkUI(navigator.onLine);

        // Jika online saat load → coba sync pending
        if (navigator.onLine) {
            await syncPending();
            // Refresh cache data master setiap 30 menit (jika online)
            await refreshMasterCache();
        }

        console.log('[OfflineRegister] Initialized. Online:', navigator.onLine);
    }

    // ── Network Event Handlers ─────────────────────────────────────────

    function handleOnline() {
        updateNetworkUI(true);
        showNetworkToast('Koneksi pulih — menyinkronkan data...', 'success');
        syncPending().then(updatePendingBadge);
    }

    function handleOffline() {
        updateNetworkUI(false);
        showNetworkToast('Koneksi terputus — Data akan disimpan lokal', 'warning');
    }

    // ── UI: Network Status Indicators ─────────────────────────────────

    function updateNetworkUI(isOnline) {
        const banner = document.getElementById('offline-mode-banner');
        const statusDot = document.querySelectorAll('.network-status-dot');
        const statusText = document.querySelectorAll('.network-status-text');

        if (banner) {
            banner.style.display = isOnline ? 'none' : 'flex';
        }

        statusDot.forEach(el => {
            el.classList.toggle('status-online',  isOnline);
            el.classList.toggle('status-offline', !isOnline);
        });

        statusText.forEach(el => {
            el.textContent = isOnline ? 'Online' : 'Offline';
            el.classList.toggle('text-success', isOnline);
            el.classList.toggle('text-danger',  !isOnline);
        });
    }

    function showNetworkToast(message, type = 'info') {
        const existing = document.getElementById('offline-toast');
        if (existing) existing.remove();

        const colors = {
            success: { bg: '#10b981', icon: 'bx-wifi' },
            warning: { bg: '#f59e0b', icon: 'bx-wifi-off' },
            error:   { bg: '#ef4444', icon: 'bx-error-circle' },
            info:    { bg: '#6366f1', icon: 'bx-info-circle' },
        };
        const c = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.id = 'offline-toast';
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            background: ${c.bg}; color: #fff; padding: 14px 20px;
            border-radius: 14px; display: flex; align-items: center; gap: 10px;
            font-weight: 600; font-size: 0.875rem; box-shadow: 0 8px 24px rgba(0,0,0,.18);
            animation: slideInRight 0.3s ease; max-width: 340px;
        `;
        toast.innerHTML = `<i class="bx ${c.icon}" style="font-size:1.4rem;flex-shrink:0;"></i><span>${message}</span>`;
        document.body.appendChild(toast);

        // Inject keyframe once
        if (!document.getElementById('offline-toast-style')) {
            const style = document.createElement('style');
            style.id = 'offline-toast-style';
            style.textContent = `
                @keyframes slideInRight { from { transform: translateX(120%); opacity:0; } to { transform: translateX(0); opacity:1; } }
                @keyframes slideOutRight { from { transform: translateX(0); opacity:1; } to { transform: translateX(120%); opacity:0; } }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => toast.remove(), 350);
        }, 3500);
    }

    // ── Pending Badge Update ───────────────────────────────────────────

    async function updatePendingBadge() {
        try {
            const count = await OfflineDB.countPending();

            // Update localStorage supaya sidebar blade bisa baca
            localStorage.setItem('offline_pending_count', count);

            // Update semua badge di halaman (sidebar, header)
            document.querySelectorAll('.offline-pending-badge').forEach(el => {
                el.textContent = count;
                el.style.display = count > 0 ? 'inline-flex' : 'none';
            });
        } catch (e) {
            console.warn('[OfflineRegister] Badge update failed:', e);
        }
    }

    // ── Sync Pending to Server ─────────────────────────────────────────

    async function syncPending() {
        if (_isSyncing || !navigator.onLine) return { synced: 0, failed: 0 };
        _isSyncing = true;

        const pendingList = await OfflineDB.getAllPending();
        if (pendingList.length === 0) {
            _isSyncing = false;
            return { synced: 0, failed: 0 };
        }

        let synced = 0, failed = 0;

        for (const record of pendingList) {
            try {
                const response = await fetch(_syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrfToken,
                        'Accept':       'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        local_id:          record.local_id,
                        registration_type: record.registration_type,
                        form_data:         record.form_data,
                        patient_info:      record.patient_info,
                        created_at:        record.created_at,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    await OfflineDB.updatePendingStatus(record.local_id, 'synced', data);
                    await OfflineDB.addSyncLog({
                        local_id:          record.local_id,
                        patient_name:      record.patient_info?.full_name ?? '-',
                        registration_type: record.registration_type,
                        status:            'success',
                        message:           `Berhasil disync. Server ID: ${data.register_id ?? '-'}`,
                    });
                    synced++;
                } else {
                    throw new Error(data.message || 'Server error');
                }
            } catch (err) {
                await OfflineDB.updatePendingStatus(record.local_id, 'failed');
                await OfflineDB.addSyncLog({
                    local_id:          record.local_id,
                    patient_name:      record.patient_info?.full_name ?? '-',
                    registration_type: record.registration_type,
                    status:            'failed',
                    message:           err.message,
                });
                failed++;
                console.warn('[OfflineRegister] Sync failed for local_id:', record.local_id, err);
            }
        }

        _isSyncing = false;
        await updatePendingBadge();

        if (synced > 0) {
            showNetworkToast(`✅ ${synced} data berhasil disinkronkan ke server`, 'success');
        }
        if (failed > 0) {
            showNetworkToast(`⚠️ ${failed} data gagal disinkronkan`, 'error');
        }

        return { synced, failed };
    }

    // ── Cache Master Data ──────────────────────────────────────────────

    async function refreshMasterCache() {
        if (!navigator.onLine) return;
        try {
            const response = await fetch(_cacheUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': _csrfToken,
                }
            });

            if (!response.ok) return;
            const data = await response.json();

            if (data.polis)      await OfflineDB.setCacheData('polis',      'polis',      data.polis);
            if (data.categories) await OfflineDB.setCacheData('categories', 'categories', data.categories);
            if (data.residents) {
                // Cache dalam chunk per 100 untuk efisiensi
                await OfflineDB.setCacheData('residents', 'residents', data.residents);
            }

            console.log('[OfflineRegister] Master cache refreshed:', Object.keys(data));
        } catch (e) {
            console.warn('[OfflineRegister] Cache refresh failed:', e);
        }
    }

    // ── Form Intercept ─────────────────────────────────────────────────

    function interceptFormSubmit(formId, options = {}) {
        const form = document.getElementById(formId);
        if (!form) return;

        const patientInfo = options.patientInfo || {};
        const registrationType = options.registrationType || 'Rawat Jalan';

        form.addEventListener('submit', async (e) => {
            if (navigator.onLine) return; // Online → submit normal

            e.preventDefault();

            // Kumpulkan semua data form
            const formData = new FormData(form);
            const formObj  = {};
            formData.forEach((value, key) => {
                if (formObj[key] !== undefined) {
                    if (!Array.isArray(formObj[key])) formObj[key] = [formObj[key]];
                    formObj[key].push(value);
                } else {
                    formObj[key] = value;
                }
            });

            // Hapus CSRF token dari form data (tidak perlu disimpan)
            delete formObj['_token'];

            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn?.innerHTML;

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Menyimpan lokal...';
                }

                const localId = await OfflineDB.savePendingRegistration({
                    registration_type: registrationType,
                    form_data:         formObj,
                    patient_info:      patientInfo,
                });

                await updatePendingBadge();

                showOfflineSaveSuccess(localId, patientInfo.full_name || 'Pasien');

            } catch (err) {
                console.error('[OfflineRegister] Save failed:', err);
                showNetworkToast('Gagal menyimpan data lokal: ' + err.message, 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    function showOfflineSaveSuccess(localId, patientName) {
        // Hapus modal lama jika ada
        const existing = document.getElementById('offline-save-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'offline-save-modal';
        modal.style.cssText = `
            position: fixed; inset: 0; z-index: 10000; background: rgba(15,23,42,.55);
            display: flex; align-items: center; justify-content: center; padding: 20px;
        `;
        modal.innerHTML = `
            <div style="background:#fff; border-radius:20px; padding:2.5rem; max-width:420px; width:100%; text-align:center; box-shadow:0 25px 60px rgba(15,23,42,.25); animation: fadeInScale .25s ease">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;box-shadow:0 8px 24px rgba(245,158,11,.35)">
                    <i class="bx bx-cloud-upload" style="font-size:2.2rem;color:#fff;"></i>
                </div>
                <h4 style="font-weight:800;color:#0f172a;margin-bottom:.5rem;">Data Tersimpan Lokal</h4>
                <p style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem;">
                    Data <strong>${patientName}</strong> berhasil disimpan di perangkat ini (ID Lokal: #${localId}).<br>
                    <span style="color:#f59e0b;font-weight:600;">Data akan otomatis dikirim ke server saat koneksi pulih.</span>
                </p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <a href="/registration/offline-manager" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;padding:.7rem 1.5rem;border-radius:12px;text-decoration:none;font-weight:700;font-size:.875rem;display:inline-flex;align-items:center;gap:6px;">
                        <i class="bx bx-data"></i> Kelola Data Lokal
                    </a>
                    <button onclick="document.getElementById('offline-save-modal').remove(); window.history.back();"
                        style="background:#f1f5f9;color:#334155;padding:.7rem 1.5rem;border-radius:12px;border:none;font-weight:700;font-size:.875rem;cursor:pointer;">
                        Kembali
                    </button>
                </div>
            </div>
        `;

        if (!document.getElementById('offline-modal-style')) {
            const s = document.createElement('style');
            s.id    = 'offline-modal-style';
            s.textContent = `@keyframes fadeInScale { from{opacity:0;transform:scale(.92)} to{opacity:1;transform:scale(1)} }`;
            document.head.appendChild(s);
        }

        document.body.appendChild(modal);
    }

    // ── Public API ─────────────────────────────────────────────────────

    return {
        init,
        syncPending,
        updatePendingBadge,
        refreshMasterCache,
        interceptFormSubmit,
        showNetworkToast,
        updateNetworkUI,
    };
})();

window.OfflineRegister = OfflineRegister;
