/**
 * SIPPINTER — Offline IndexedDB Manager
 * Mengelola penyimpanan data registrasi lokal saat koneksi offline
 */
const OfflineDB = (() => {
    const DB_NAME    = 'sippinter_offline';
    const DB_VERSION = 2;
    let _db = null;

    // Stores:
    // pending_registrations  — form data yang belum tersinkron
    // sync_logs              — riwayat sync (berhasil/gagal)
    // master_cache           — cache data master (residents, polis, categories)

    function open() {
        return new Promise((resolve, reject) => {
            if (_db) { resolve(_db); return; }

            const req = indexedDB.open(DB_NAME, DB_VERSION);

            req.onupgradeneeded = (e) => {
                const db = e.target.result;

                // ── pending_registrations ──────────────────────
                if (!db.objectStoreNames.contains('pending_registrations')) {
                    const store = db.createObjectStore('pending_registrations', {
                        keyPath: 'local_id', autoIncrement: true
                    });
                    store.createIndex('registration_type', 'registration_type', { unique: false });
                    store.createIndex('created_at',        'created_at',        { unique: false });
                    store.createIndex('sync_status',       'sync_status',       { unique: false });
                }

                // ── sync_logs ──────────────────────────────────
                if (!db.objectStoreNames.contains('sync_logs')) {
                    const logStore = db.createObjectStore('sync_logs', {
                        keyPath: 'id', autoIncrement: true
                    });
                    logStore.createIndex('created_at', 'created_at', { unique: false });
                    logStore.createIndex('status',     'status',     { unique: false });
                }

                // ── master_cache ───────────────────────────────
                if (!db.objectStoreNames.contains('master_cache')) {
                    const cacheStore = db.createObjectStore('master_cache', { keyPath: 'cache_key' });
                    cacheStore.createIndex('cache_type', 'cache_type', { unique: false });
                }
            };

            req.onsuccess = (e) => {
                _db = e.target.result;
                resolve(_db);
            };

            req.onerror = (e) => reject(e.target.error);
        });
    }

    // ── Pending Registrations ──────────────────────────────────────────

    async function savePendingRegistration(data) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readwrite');
            const store = tx.objectStore('pending_registrations');
            const record = {
                ...data,
                sync_status: 'pending',
                created_at:  new Date().toISOString(),
                updated_at:  new Date().toISOString(),
            };
            const req = store.add(record);
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    async function getAllPending() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readonly');
            const store = tx.objectStore('pending_registrations');
            const index = store.index('sync_status');
            const req   = index.getAll('pending');
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    async function getAllRegistrations() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readonly');
            const store = tx.objectStore('pending_registrations');
            const req   = store.getAll();
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    async function updatePendingStatus(localId, status, serverResponse = null) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readwrite');
            const store = tx.objectStore('pending_registrations');
            const getReq = store.get(localId);
            getReq.onsuccess = () => {
                const record = getReq.result;
                if (!record) { reject(new Error('Record not found')); return; }
                record.sync_status    = status;
                record.updated_at     = new Date().toISOString();
                record.server_data    = serverResponse;
                const putReq = store.put(record);
                putReq.onsuccess = () => resolve(putReq.result);
                putReq.onerror   = () => reject(putReq.error);
            };
        });
    }

    async function deletePendingRegistration(localId) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readwrite');
            const store = tx.objectStore('pending_registrations');
            const req   = store.delete(localId);
            req.onsuccess = () => resolve();
            req.onerror   = () => reject(req.error);
        });
    }

    async function clearAllPending() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readwrite');
            const store = tx.objectStore('pending_registrations');
            const req   = store.clear();
            req.onsuccess = () => resolve();
            req.onerror   = () => reject(req.error);
        });
    }

    async function countPending() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('pending_registrations', 'readonly');
            const store = tx.objectStore('pending_registrations');
            const index = store.index('sync_status');
            const req   = index.count('pending');
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    // ── Sync Logs ──────────────────────────────────────────────────────

    async function addSyncLog(log) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('sync_logs', 'readwrite');
            const store = tx.objectStore('sync_logs');
            const record = { ...log, created_at: new Date().toISOString() };
            const req    = store.add(record);
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    async function getSyncLogs(limit = 50) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('sync_logs', 'readonly');
            const store = tx.objectStore('sync_logs');
            const req   = store.getAll();
            req.onsuccess = () => {
                const all = req.result.sort((a, b) => b.id - a.id);
                resolve(all.slice(0, limit));
            };
            req.onerror = () => reject(req.error);
        });
    }

    async function clearSyncLogs() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('sync_logs', 'readwrite');
            const store = tx.objectStore('sync_logs');
            const req   = store.clear();
            req.onsuccess = () => resolve();
            req.onerror   = () => reject(req.error);
        });
    }

    // ── Master Cache ───────────────────────────────────────────────────

    async function setCacheData(cacheKey, cacheType, data) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('master_cache', 'readwrite');
            const store = tx.objectStore('master_cache');
            const req   = store.put({
                cache_key:  cacheKey,
                cache_type: cacheType,
                data:       data,
                cached_at:  new Date().toISOString(),
            });
            req.onsuccess = () => resolve();
            req.onerror   = () => reject(req.error);
        });
    }

    async function getCacheData(cacheKey) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('master_cache', 'readonly');
            const store = tx.objectStore('master_cache');
            const req   = store.get(cacheKey);
            req.onsuccess = () => resolve(req.result ? req.result.data : null);
            req.onerror   = () => reject(req.error);
        });
    }

    async function clearCache() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx    = db.transaction('master_cache', 'readwrite');
            const store = tx.objectStore('master_cache');
            const req   = store.clear();
            req.onsuccess = () => resolve();
            req.onerror   = () => reject(req.error);
        });
    }

    // ── DB Info ────────────────────────────────────────────────────────

    async function getDBStats() {
        const [all, pending, logs] = await Promise.all([
            getAllRegistrations(),
            getAllPending(),
            getSyncLogs(100),
        ]);
        return {
            total:         all.length,
            pending:       pending.length,
            synced:        all.filter(r => r.sync_status === 'synced').length,
            failed:        all.filter(r => r.sync_status === 'failed').length,
            total_logs:    logs.length,
            success_logs:  logs.filter(l => l.status === 'success').length,
            failed_logs:   logs.filter(l => l.status === 'failed').length,
        };
    }

    return {
        open,
        savePendingRegistration,
        getAllPending,
        getAllRegistrations,
        updatePendingStatus,
        deletePendingRegistration,
        clearAllPending,
        countPending,
        addSyncLog,
        getSyncLogs,
        clearSyncLogs,
        setCacheData,
        getCacheData,
        clearCache,
        getDBStats,
    };
})();

// Expose globally
window.OfflineDB = OfflineDB;
