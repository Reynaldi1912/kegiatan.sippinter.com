{{-- Global Modal: Import File JSON Offline dengan Pratinjau & Edit Data --}}
<script src="{{ asset('js/crypto-js.min.js') }}"></script>
<script>
    if (typeof CryptoJS === 'undefined') {
        var cdnScript = document.createElement('script');
        cdnScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js';
        document.head.appendChild(cdnScript);
    }
</script>

<div class="modal fade" id="globalImportJsonModal" tabindex="-1" aria-labelledby="globalImportJsonModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
            <!-- Header -->
            <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px; height:42px; border-radius:12px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center;">
                        <i class="bx bx-import fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="globalImportJsonModalLabel" style="font-size:1.15rem;">
                            Import Data Offline (.JSON)
                        </h5>
                        <small class="text-white-50">Pratinjau, periksa, edit, dan sinkronkan data hasil ekspor mobile ke database</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4" style="background: #f8fafc; max-height: calc(85vh - 120px); overflow-y: auto;">
                <!-- 1. SELECT & PREVIEW/EDIT STATE -->
                <div id="importStateSelect">
                    <!-- Dropzone -->
                    <div id="globalDropzoneBox" onclick="document.getElementById('globalJsonFileInput').click()"
                        style="border:2px dashed #cbd5e1; border-radius:16px; padding:2rem 1.5rem; text-align:center; cursor:pointer; background:#ffffff; transition:all .2s;">
                        <input type="file" id="globalJsonFileInput" accept=".json,application/json" style="display:none;" onchange="handleGlobalFileSelected(this.files[0])">
                        <div style="width:60px; height:60px; border-radius:16px; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; margin:0 auto 0.8rem; font-size:1.8rem;">
                            <i class="bx bx-cloud-upload"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color:#0f172a;">Klik atau Tarik File .JSON ke Sini</h6>
                        <p class="text-muted small mb-0">Contoh format: <code>yati_20261022_91.json</code> atau <code>*.json</code></p>
                    </div>

                    <!-- File Preview & Editor Card (Hidden until file selected) -->
                    <div id="globalFilePreviewCard" class="card border-0 shadow-sm mt-3" style="border-radius:16px; display:none; background:#ffffff;">
                        <div class="card-body p-3 p-md-4">
                            <!-- Top File Info Row -->
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <div style="width:44px; height:44px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center;">
                                        <i class="bx bxs-file-json text-primary fs-3"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate" id="globalPreviewFileName" style="font-size:1.05rem;">filename.json</div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted" style="font-size:0.8rem;">
                                            <span id="globalPreviewFileSize">0 KB</span>
                                            <span>•</span>
                                            <span><i class="bx bx-user text-primary me-1"></i><span id="globalPreviewUser">-</span></span>
                                            <span>•</span>
                                            <span><i class="bx bx-calendar text-primary me-1"></i><span id="globalPreviewDate">-</span></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="resetGlobalImportModal()" title="Ganti File">
                                    <i class="bx bx-trash me-1"></i> Ganti File
                                </button>
                            </div>

                            <!-- Item Counters -->
                            <div class="row g-2 text-center mb-3" id="globalItemCountsRow">
                                <div class="col">
                                    <div class="p-2 rounded-3 border" style="background:#f8fafc;">
                                        <div class="fw-bold text-dark fs-5" id="cntTotal">0</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Total Data</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-2 rounded-3 border" style="background:#f0fdf4; border-color:#bbf7d0!important;">
                                        <div class="fw-bold text-success fs-5" id="cntSurvey">0</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Survey</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-2 rounded-3 border" style="background:#eff6ff; border-color:#bfdbfe!important;">
                                        <div class="fw-bold text-primary fs-5" id="cntLink">0</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Taut Tag</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-2 rounded-3 border" style="background:#faf5ff; border-color:#e9d5ff!important;">
                                        <div class="fw-bold fs-5" style="color:#8b5cf6;" id="cntQuickAdd">0</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Penduduk Baru</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-2 rounded-3 border" style="background:#fffbeb; border-color:#fef08a!important;">
                                        <div class="fw-bold text-warning fs-5" id="cntQuickReg">0</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Pasien Sakit</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Interactive Data Viewer & Editor Section -->
                            <div class="pt-3 border-top" id="globalDataListSection">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:34px; height:34px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center;">
                                            <i class="bx bxs-file-json fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block" style="font-size: 0.95rem;">Pratinjau & Edit Data JSON Sebelum Di-Import</span>
                                            <small class="text-muted">Data didekripsi otomatis. Anda dapat langsung mengedit/memperbaiki isi JSON di bawah ini.</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill" id="editorItemCountBadge" style="font-size:0.75rem;">0 Data Siap Di-Import</span>
                                </div>

                                <!-- Tab Switch: Editor JSON Langsung vs Kartu Form -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <div class="btn-group p-1 bg-light border rounded-pill" role="group">
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-dark" id="btnTabJson" onclick="switchEditorMode('json')">
                                            <i class="bx bx-code-alt me-1"></i> Editor JSON (Langsung)
                                        </button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light text-secondary" id="btnTabCards" onclick="switchEditorMode('cards')">
                                            <i class="bx bx-grid-alt me-1"></i> Tampilan Kartu Form
                                        </button>
                                    </div>
                                </div>

                                <!-- VIEW 1: JSON TEXT EDITOR (DEFAULT & DIRECT) -->
                                <div id="viewModeJsonEditor">
                                    <!-- Toolbar -->
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 p-2 bg-light rounded-3 border">
                                        <div class="d-flex align-items-center gap-2">
                                            <span id="jsonValidationBadge" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bx bx-check-circle me-1"></i>Sintaks JSON Valid
                                            </span>
                                            <span class="text-muted small" id="jsonLineCount">0 baris</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="formatJsonEditor()" title="Rapikan struktur indentasi JSON">
                                                <i class="bx bx-code-block me-1"></i>Rapikan JSON
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" id="btnCopyJson" onclick="copyJsonEditor()" title="Salin isi JSON ke Clipboard">
                                                <i class="bx bx-copy me-1"></i>Salin JSON
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2" onclick="resetJsonEditorToOriginal()" title="Kembalikan ke isi awal file">
                                                <i class="bx bx-undo me-1"></i>Reset Asli
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Code Box Header & Textarea -->
                                    <div style="border-radius:12px; overflow:hidden; border:1px solid #1e293b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                        <div style="background:#1e293b; color:#94a3b8; font-size:0.75rem; padding:8px 14px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #334155;">
                                            <span class="d-flex align-items-center gap-2">
                                                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#ef4444;"></span>
                                                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#f59e0b;"></span>
                                                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#10b981;"></span>
                                                <span class="text-white-50 ms-2 font-monospace">data_offline_editor.json (Dapat langsung diedit)</span>
                                            </span>
                                            <span class="badge bg-dark text-info font-monospace py-0 px-2" style="font-size:0.7rem;">JSON • Auto-Sync</span>
                                        </div>
                                        <textarea id="globalJsonCodeEditor"
                                            spellcheck="false"
                                            style="width:100%; height:390px; font-family:'Consolas', 'Fira Code', 'Monaco', 'Courier New', monospace; font-size:0.85rem; line-height:1.55; background:#0f172a; color:#38bdf8; border:none; padding:14px; resize:vertical; outline:none; tab-size:2; white-space:pre;"
                                            placeholder="{ &quot;kader_items&quot;: [ ... ] }"
                                            oninput="handleJsonEditorInput()"></textarea>
                                    </div>

                                    <!-- Syntax error alert banner -->
                                    <div id="jsonSyntaxErrorAlert" class="alert alert-danger py-2 px-3 mt-2 mb-0 small" style="display:none; border-radius:10px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-error-circle fs-5"></i>
                                            <div>
                                                <b>Kesalahan Format JSON:</b>
                                                <span id="jsonSyntaxErrorMessage">Format JSON tidak valid.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- VIEW 2: VISUAL CARDS (SECONDARY / ALTERNATIVE) -->
                                <div id="viewModeVisualCards" style="display:none;">
                                    <!-- Filter category pills -->
                                    <div class="d-flex gap-1 mb-3 overflow-auto pb-1" id="categoryFilterBar">
                                        <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 fw-semibold filter-btn active" onclick="filterEditableItems('ALL', this)">Semua</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 filter-btn" onclick="filterEditableItems('quick_add', this)">Penduduk Baru</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 filter-btn" onclick="filterEditableItems('link', this)">Tautkan Tag</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 filter-btn" onclick="filterEditableItems('survey', this)">Survey</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 filter-btn" onclick="filterEditableItems('quick_register', this)">Pasien Sakit</button>
                                    </div>

                                    <!-- Scrollable Items Container -->
                                    <div id="editableItemsList" style="max-height: 420px; overflow-y: auto; padding-right: 4px;">
                                        <!-- Rendered dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. LOADING STATE -->
                <div id="importStateLoading" style="display:none; text-align:center; padding:3rem 1.5rem;">
                    <div class="spinner-border text-primary mb-3" style="width:3.5rem; height:3.5rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Sedang Menyimpan Data ke Database...</h5>
                    <p class="text-muted small mb-0">Menyimpan data penduduk, survey, penautan tag, dan foto ke server SIPPINTER.</p>
                </div>

                <!-- 3. SUCCESS STATE -->
                <div id="importStateSuccess" style="display:none;">
                    <div class="text-center mb-3">
                        <div style="width:64px; height:64px; border-radius:50%; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; margin:0 auto 0.8rem; font-size:2.2rem;">
                            <i class="bx bx-check"></i>
                        </div>
                        <h5 class="fw-bold text-success mb-1" id="successTitle">Import Data Berhasil!</h5>
                        <p class="text-muted small mb-0" id="successMessage">Seluruh data offline berhasil disinkronkan ke database.</p>
                    </div>

                    <!-- Breakdown Box -->
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:14px; background:#ffffff;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark mb-2" style="font-size:0.85rem;">Ringkasan Data Masuk:</h6>
                            <div class="d-flex flex-wrap gap-2" id="successBadges">
                                <!-- Dynamically filled -->
                            </div>
                        </div>
                    </div>

                    <!-- Warning for skipped/failed items if any -->
                    <div id="partialFailSection" class="alert alert-warning mb-3" style="border-radius:12px; display:none;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bx bx-info-circle fs-5 text-warning"></i>
                            <b id="partialFailTitle">Catatan Perhatian: Beberapa item dilewati</b>
                        </div>
                        <ul class="mb-0 ps-3 small text-dark" id="partialFailList" style="max-height:120px; overflow-y:auto;">
                            <!-- Dynamically filled -->
                        </ul>
                    </div>

                    <!-- Details Accordion -->
                    <div class="accordion" id="accordionImportDetails">
                        <div class="accordion-item border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 px-3 small fw-bold bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImportLog">
                                    <i class="bx bx-list-check me-2 text-primary"></i> Rincian Log Aktivitas (<span id="logCount">0</span>)
                                </button>
                            </h2>
                            <div id="collapseImportLog" class="accordion-collapse collapse" data-bs-parent="#accordionImportDetails">
                                <div class="accordion-body p-3 small" style="max-height:160px; overflow-y:auto; background:#f8fafc;">
                                    <ul class="mb-0 ps-3" id="importLogList">
                                        <!-- Dynamically filled -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. FAILED STATE -->
                <div id="importStateError" style="display:none;">
                    <div class="text-center mb-3">
                        <div style="width:64px; height:64px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; margin:0 auto 0.8rem; font-size:2.2rem;">
                            <i class="bx bx-x"></i>
                        </div>
                        <h5 class="fw-bold text-danger mb-1" id="errorTitle">Import Gagal!</h5>
                        <p class="text-muted small mb-0" id="errorMessage">Data offline tidak dapat diimpor ke database.</p>
                    </div>

                    <!-- Failure Reasons Box -->
                    <div class="card border-danger border mb-3" style="border-radius:14px; background:#fff5f5;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-error-circle text-danger fs-5"></i>
                                <h6 class="fw-bold text-danger mb-0" style="font-size:0.88rem;">Penyebab Kegagalan (Kenapa Gagal?):</h6>
                            </div>
                            <ul class="mb-0 ps-3 text-danger small" id="errorReasonsList" style="line-height:1.6;">
                                <li>Format file tidak sesuai atau koneksi terputus.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 py-3 px-4 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Batal
                </button>
                <div class="d-flex gap-2">
                    <button type="button" id="btnResetImport" class="btn btn-light rounded-pill px-3" onclick="resetGlobalImportModal()" style="display:none;">
                        <i class="bx bx-refresh me-1"></i> Pilih File Lain
                    </button>
                    <button type="button" id="btnSubmitImport" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" onclick="submitGlobalImportJson()" disabled>
                        <i class="bx bx-upload me-1"></i> Proses Import Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // State data in-memory yang dapat diedit
    let globalActiveCategory = 'ALL';
    let globalActiveEditorMode = 'json';
    let globalEditableKader = [];
    let globalEditableAttendance = [];
    let globalEditableRegistrations = [];
    let globalSelectedFile = null;
    let globalOriginalDecryptedObj = null;
    let globalCurrentParsedJson = null;

    const OFFLINE_SECRET = 'SippinterOfflineEncryptedKey2026_SecureSyncKey#99';

    function openGlobalImportJsonModal() {
        resetGlobalImportModal();
        const modalEl = document.getElementById('globalImportJsonModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function resetGlobalImportModal() {
        globalEditableKader = [];
        globalEditableAttendance = [];
        globalEditableRegistrations = [];
        globalSelectedFile = null;
        globalOriginalDecryptedObj = null;
        globalCurrentParsedJson = null;
        globalActiveCategory = 'ALL';
        globalActiveEditorMode = 'json';

        const fileInput = document.getElementById('globalJsonFileInput');
        if (fileInput) fileInput.value = '';

        const codeEditor = document.getElementById('globalJsonCodeEditor');
        if (codeEditor) codeEditor.value = '';

        document.getElementById('importStateSelect').style.display = 'block';
        document.getElementById('importStateLoading').style.display = 'none';
        document.getElementById('importStateSuccess').style.display = 'none';
        document.getElementById('importStateError').style.display = 'none';

        document.getElementById('globalFilePreviewCard').style.display = 'none';
        document.getElementById('btnSubmitImport').style.display = 'inline-block';
        document.getElementById('btnSubmitImport').disabled = true;
        document.getElementById('btnResetImport').style.display = 'none';

        switchEditorMode('json');
    }

    function switchEditorMode(mode) {
        globalActiveEditorMode = mode;
        const btnJson = document.getElementById('btnTabJson');
        const btnCards = document.getElementById('btnTabCards');
        const viewJson = document.getElementById('viewModeJsonEditor');
        const viewCards = document.getElementById('viewModeVisualCards');

        if (mode === 'json') {
            const currentSynced = {
                user: (globalCurrentParsedJson && globalCurrentParsedJson.user) ? globalCurrentParsedJson.user : { name: 'Kader Mobile' },
                exported_at: (globalCurrentParsedJson && globalCurrentParsedJson.exported_at) ? globalCurrentParsedJson.exported_at : new Date().toISOString(),
                kader_items: globalEditableKader,
                attendance_items: globalEditableAttendance,
                registration_items: globalEditableRegistrations
            };
            const editorEl = document.getElementById('globalJsonCodeEditor');
            if (editorEl && (!editorEl.value || globalActiveEditorMode !== 'json')) {
                editorEl.value = JSON.stringify(currentSynced, null, 2);
            }
            handleJsonEditorInput();

            if (btnJson) btnJson.className = 'btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-dark';
            if (btnCards) btnCards.className = 'btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light text-secondary';
            if (viewJson) viewJson.style.display = 'block';
            if (viewCards) viewCards.style.display = 'none';
        } else {
            const text = document.getElementById('globalJsonCodeEditor')?.value || '';
            try {
                const parsed = JSON.parse(text);
                globalCurrentParsedJson = parsed;
                globalEditableKader = Array.isArray(parsed.kader_items) ? parsed.kader_items : [];
                globalEditableAttendance = Array.isArray(parsed.attendance_items) ? parsed.attendance_items : [];
                globalEditableRegistrations = Array.isArray(parsed.registration_items || parsed.registrations) ? (parsed.registration_items || parsed.registrations) : [];
                renderEditableList();

                if (btnJson) btnJson.className = 'btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light text-secondary';
                if (btnCards) btnCards.className = 'btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-dark';
                if (viewJson) viewJson.style.display = 'none';
                if (viewCards) viewCards.style.display = 'block';
            } catch (err) {
                alert('Sintaks JSON tidak valid! Harap perbaiki kesalahan JSON terlebih dahulu sebelum membuka kartu form:\n' + err.message);
            }
        }
    }

    function handleJsonEditorInput() {
        const editor = document.getElementById('globalJsonCodeEditor');
        if (!editor) return;
        const text = editor.value;

        const lineCount = text ? text.split('\n').length : 0;
        const lineCountEl = document.getElementById('jsonLineCount');
        if (lineCountEl) lineCountEl.textContent = `${lineCount} baris`;

        const badge = document.getElementById('jsonValidationBadge');
        const errAlert = document.getElementById('jsonSyntaxErrorAlert');
        const errMsg = document.getElementById('jsonSyntaxErrorMessage');
        const submitBtn = document.getElementById('btnSubmitImport');

        try {
            const parsed = JSON.parse(text);
            globalCurrentParsedJson = parsed;

            if (badge) {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1';
                badge.innerHTML = '<i class="bx bx-check-circle me-1"></i>Sintaks JSON Valid';
            }
            if (errAlert) errAlert.style.display = 'none';

            let cntSurvey = 0, cntLink = 0, cntQuickAdd = 0, cntQuickReg = 0;
            const kItems = Array.isArray(parsed.kader_items) ? parsed.kader_items : [];
            const aItems = Array.isArray(parsed.attendance_items) ? parsed.attendance_items : [];
            const rItems = Array.isArray(parsed.registration_items || parsed.registrations) ? (parsed.registration_items || parsed.registrations) : [];

            kItems.forEach(i => {
                if (i.type === 'survey') cntSurvey++;
                else if (i.type === 'link') cntLink++;
                else if (i.type === 'quick_add') cntQuickAdd++;
                else if (i.type === 'quick_register') cntQuickReg++;
            });

            const total = kItems.length + aItems.length + rItems.length;
            document.getElementById('cntTotal').textContent = total;
            document.getElementById('cntSurvey').textContent = cntSurvey;
            document.getElementById('cntLink').textContent = cntLink;
            document.getElementById('cntQuickAdd').textContent = cntQuickAdd;
            document.getElementById('cntQuickReg').textContent = cntQuickReg;
            document.getElementById('editorItemCountBadge').textContent = `${total} Data Siap Di-Import`;

            if (submitBtn) submitBtn.disabled = (total === 0);

            globalEditableKader = kItems;
            globalEditableAttendance = aItems;
            globalEditableRegistrations = rItems;

        } catch (err) {
            if (badge) {
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1';
                badge.innerHTML = '<i class="bx bx-x-circle me-1"></i>Sintaks JSON Tidak Valid';
            }
            if (errAlert) {
                errAlert.style.display = 'block';
                if (errMsg) errMsg.textContent = err.message;
            }
            if (submitBtn) submitBtn.disabled = true;
        }
    }

    function formatJsonEditor() {
        const editor = document.getElementById('globalJsonCodeEditor');
        if (!editor) return;
        try {
            const parsed = JSON.parse(editor.value);
            editor.value = JSON.stringify(parsed, null, 2);
            handleJsonEditorInput();
        } catch (err) {
            alert('Tidak dapat merapikan karena sintaks JSON masih ada kesalahan:\n' + err.message);
        }
    }

    function copyJsonEditor() {
        const editor = document.getElementById('globalJsonCodeEditor');
        if (!editor) return;
        navigator.clipboard.writeText(editor.value).then(() => {
            const btn = document.getElementById('btnCopyJson');
            if (btn) {
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-check text-success me-1"></i>Tersalin!';
                setTimeout(() => { btn.innerHTML = oldHtml; }, 1800);
            }
        });
    }

    function resetJsonEditorToOriginal() {
        if (!globalOriginalDecryptedObj) return;
        if (!confirm('Kembalikan isi JSON ke versi awal saat file diunggah?')) return;
        const cleanObj = {
            user: globalOriginalDecryptedObj.user || { name: globalOriginalDecryptedObj._raw_exported_by || 'Kader Mobile' },
            exported_at: globalOriginalDecryptedObj.exported_at || globalOriginalDecryptedObj._raw_exported_at || new Date().toISOString(),
            kader_items: Array.isArray(globalOriginalDecryptedObj.kader_items) ? JSON.parse(JSON.stringify(globalOriginalDecryptedObj.kader_items)) : [],
            attendance_items: Array.isArray(globalOriginalDecryptedObj.attendance_items) ? JSON.parse(JSON.stringify(globalOriginalDecryptedObj.attendance_items)) : [],
            registration_items: Array.isArray(globalOriginalDecryptedObj.registration_items || globalOriginalDecryptedObj.registrations) ? JSON.parse(JSON.stringify(globalOriginalDecryptedObj.registration_items || globalOriginalDecryptedObj.registrations)) : []
        };
        document.getElementById('globalJsonCodeEditor').value = JSON.stringify(cleanObj, null, 2);
        handleJsonEditorInput();
    }

    function handleGlobalFileSelected(file) {
        if (!file) return;

        if (!file.name.endsWith('.json') && file.type !== 'application/json') {
            showGlobalImportError('Tipe File Tidak Sesuai', 'File yang dipilih bukan berformat .json', [
                'Pastikan file yang dipilih berekstensi .json.',
                'File yang didukung adalah file hasil ekspor dari aplikasi Sippinter Mobile atau backup Sippinter Web.'
            ]);
            return;
        }

        globalSelectedFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const text = e.target.result;
                let json = JSON.parse(text);

                // Jika file terenkripsi (AES-256 Lock) -> Dekripsi di browser agar bisa dilihat & diedit!
                if (json.is_encrypted) {
                    try {
                        const key = CryptoJS.SHA256(OFFLINE_SECRET);
                        const iv = CryptoJS.enc.Base64.parse(json.iv);
                        const decrypted = CryptoJS.AES.decrypt(json.ciphertext, key, {
                            iv: iv,
                            mode: CryptoJS.mode.CBC,
                            padding: CryptoJS.pad.Pkcs7
                        });
                        const utf8Str = decrypted.toString(CryptoJS.enc.Utf8);
                        if (!utf8Str) {
                            throw new Error('Kunci enkripsi tidak cocok atau isi file terenkripsi rusak.');
                        }
                        const decryptedObj = JSON.parse(utf8Str);
                        decryptedObj._raw_exported_by = json.exported_by;
                        decryptedObj._raw_exported_at = json.exported_at;
                        decryptedObj._is_locked = true;
                        json = decryptedObj;
                    } catch (decErr) {
                        showGlobalImportError('Gagal Membuka Kunci File Terenkripsi', 'Data di dalam file JSON terkunci dan tidak dapat didekripsi.', [
                            'Pesan error dekripsi: ' + decErr.message,
                            'Pastikan file diekspor resmi dari aplikasi Sippinter Mobile.'
                        ]);
                        return;
                    }
                }

                // Inisialisasi data yang dapat diedit
                globalEditableKader = Array.isArray(json.kader_items) ? JSON.parse(JSON.stringify(json.kader_items)) : [];
                globalEditableAttendance = Array.isArray(json.attendance_items) ? JSON.parse(JSON.stringify(json.attendance_items)) : [];
                globalEditableRegistrations = Array.isArray(json.registration_items || json.registrations) ? JSON.parse(JSON.stringify(json.registration_items || json.registrations)) : [];

                const total = globalEditableKader.length + globalEditableAttendance.length + globalEditableRegistrations.length;
                if (total === 0) {
                    showGlobalImportError('File JSON Kosong', 'File ini tidak memiliki antrian data offline untuk diimpor.', [
                        'File JSON berhasil dibaca, namun array kader_items dan attendance_items kosong.',
                        'Pastikan Anda mengekspor data saat terdapat antrian pending di aplikasi mobile.'
                    ]);
                    return;
                }

                // Meta Info
                document.getElementById('globalPreviewFileName').textContent = file.name;
                document.getElementById('globalPreviewFileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
                
                let uName = json.user ? (json.user.name || json.user.username) : (json._raw_exported_by || 'Kader Mobile');
                let lockBadge = json._is_locked ? `<span class="badge bg-success py-1 px-2 me-1"><i class="bx bx-lock-alt me-1"></i>Terkunci (AES-256)</span> ` : '';
                document.getElementById('globalPreviewUser').innerHTML = lockBadge + uName;

                let expDate = '-';
                let rawDate = json.exported_at || json._raw_exported_at;
                if (rawDate) {
                    try {
                        const d = new Date(rawDate);
                        expDate = d.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
                    } catch(err) { expDate = rawDate; }
                }
                document.getElementById('globalPreviewDate').textContent = expDate;

                // Siapkan JSON bersih yang siap dibaca & diedit langsung
                globalOriginalDecryptedObj = JSON.parse(JSON.stringify(json));
                const cleanObj = {
                    user: json.user || { name: json._raw_exported_by || 'Kader Mobile' },
                    exported_at: json.exported_at || json._raw_exported_at || new Date().toISOString(),
                    kader_items: globalEditableKader,
                    attendance_items: globalEditableAttendance,
                    registration_items: globalEditableRegistrations
                };
                globalCurrentParsedJson = cleanObj;

                document.getElementById('globalJsonCodeEditor').value = JSON.stringify(cleanObj, null, 2);
                handleJsonEditorInput();
                switchEditorMode('json');

                document.getElementById('globalFilePreviewCard').style.display = 'block';
                document.getElementById('btnSubmitImport').disabled = false;

            } catch (err) {
                showGlobalImportError('Format JSON Rusak', 'File tidak dapat dibaca karena format teks rusak.', [
                    'Sintaks file JSON tidak valid: ' + err.message,
                    'Pastikan file tidak terpotong saat proses download atau kirim lewat WhatsApp.'
                ]);
            }
        };
        reader.readAsText(file);
    }

    // Hitung ulang dan render daftar item yang dapat diedit
    function updateCountersAndList() {
        let cntSurvey = 0, cntLink = 0, cntQuickAdd = 0, cntQuickReg = 0;
        globalEditableKader.forEach(i => {
            if (i.type === 'survey') cntSurvey++;
            else if (i.type === 'link') cntLink++;
            else if (i.type === 'quick_add') cntQuickAdd++;
            else if (i.type === 'quick_register') cntQuickReg++;
        });

        const total = globalEditableKader.length + globalEditableAttendance.length + globalEditableRegistrations.length;

        document.getElementById('cntTotal').textContent = total;
        document.getElementById('cntSurvey').textContent = cntSurvey;
        document.getElementById('cntLink').textContent = cntLink;
        document.getElementById('cntQuickAdd').textContent = cntQuickAdd;
        document.getElementById('cntQuickReg').textContent = cntQuickReg;
        document.getElementById('editorItemCountBadge').textContent = `${total} Data Siap Di-Import`;

        document.getElementById('btnSubmitImport').disabled = (total === 0);

        renderEditableList();
    }

    function filterEditableItems(category, btnEl) {
        globalActiveCategory = category;
        document.querySelectorAll('#categoryFilterBar .filter-btn').forEach(b => {
            b.classList.remove('active', 'btn-dark');
            b.classList.add('btn-outline-secondary');
        });
        if (btnEl) {
            btnEl.classList.remove('btn-outline-secondary');
            btnEl.classList.add('active', 'btn-dark');
        }
        renderEditableList();
    }

    function renderEditableList() {
        const listContainer = document.getElementById('editableItemsList');
        listContainer.innerHTML = '';

        let itemsToRender = [];

        // 1. Kader Items
        globalEditableKader.forEach((item, idx) => {
            if (globalActiveCategory === 'ALL' || globalActiveCategory === item.type) {
                itemsToRender.push({ item, idx, category: 'kader' });
            }
        });

        // 2. Attendance Items
        globalEditableAttendance.forEach((item, idx) => {
            if (globalActiveCategory === 'ALL' || globalActiveCategory === 'attendance') {
                itemsToRender.push({ item, idx, category: 'attendance' });
            }
        });

        // 3. Registration Items
        globalEditableRegistrations.forEach((item, idx) => {
            if (globalActiveCategory === 'ALL' || globalActiveCategory === 'registration') {
                itemsToRender.push({ item, idx, category: 'registration' });
            }
        });

        if (itemsToRender.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center py-4 text-muted small bg-light rounded-3 border">
                    <i class="bx bx-folder-open fs-3 d-block mb-1"></i>
                    Tidak ada data pada kategori ini.
                </div>
            `;
            return;
        }

        itemsToRender.forEach(({ item, idx, category }) => {
            const cardEl = createItemCardElement(item, idx, category);
            listContainer.appendChild(cardEl);
        });
    }

    function createItemCardElement(item, idx, category) {
        const card = document.createElement('div');
        card.className = 'card border mb-2 shadow-xs';
        card.style.borderRadius = '12px';
        card.style.background = '#ffffff';

        const type = item.type || (category === 'attendance' ? 'attendance' : 'data');
        const payload = item.payload || {};

        let badgeHtml = '';
        let titleHtml = '';
        let formFieldsHtml = '';

        if (type === 'quick_add') {
            badgeHtml = `<span class="badge bg-purple px-2 py-1" style="background:#8b5cf6!important;"><i class="bx bx-user-plus me-1"></i>Tambah Penduduk</span>`;
            titleHtml = `<b>${escapeHtml(payload.full_name || 'Penduduk Baru')}</b> (Tag: ${escapeHtml(item.tag || '-')})`;
            formFieldsHtml = `
                <div class="row g-2 mt-1">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(payload.full_name || '')}" oninput="updateItemPayloadField(${idx}, 'full_name', this.value)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">NIK (16 Digit)</label>
                        <input type="text" maxlength="16" class="form-control form-control-sm font-monospace" value="${escapeHtml(payload.nik || '')}" oninput="updateItemPayloadField(${idx}, 'nik', this.value)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">No KK</label>
                        <input type="text" maxlength="16" class="form-control form-control-sm font-monospace" value="${escapeHtml(payload.kk || '')}" oninput="updateItemPayloadField(${idx}, 'kk', this.value)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Jenis Kelamin</label>
                        <select class="form-select form-select-sm" onchange="updateItemPayloadField(${idx}, 'gender', this.value)">
                            <option value="L" ${payload.gender === 'L' ? 'selected' : ''}>Laki-laki (L)</option>
                            <option value="P" ${payload.gender === 'P' ? 'selected' : ''}>Perempuan (P)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Tanggal Lahir</label>
                        <input type="date" class="form-control form-control-sm" value="${escapeHtml(payload.birth_date || '')}" oninput="updateItemPayloadField(${idx}, 'birth_date', this.value)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Desa / Kelurahan</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(payload.village || 'KRASAK')}" oninput="updateItemPayloadField(${idx}, 'village', this.value)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Barcode Tag</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(item.tag || '')}" oninput="updateItemRootField(${idx}, 'tag', this.value)">
                    </div>
                </div>
            `;
        } else if (type === 'link') {
            badgeHtml = `<span class="badge bg-primary px-2 py-1"><i class="bx bx-link me-1"></i>Penautan Tag</span>`;
            titleHtml = `Tautkan ke <b>Tag ${escapeHtml(item.tag || '-')}</b> (NIK: ${escapeHtml(payload.nik || '-')})`;
            formFieldsHtml = `
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">Barcode Tagging</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(item.tag || '')}" oninput="updateItemRootField(${idx}, 'tag', this.value)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">NIK Penduduk</label>
                        <input type="text" class="form-control form-control-sm font-monospace" value="${escapeHtml(payload.nik || '')}" oninput="updateItemPayloadField(${idx}, 'nik', this.value)">
                    </div>
                </div>
            `;
        } else if (type === 'quick_register') {
            badgeHtml = `<span class="badge bg-warning text-dark px-2 py-1"><i class="bx bx-plus-medical me-1"></i>Pasien Sakit</span>`;
            titleHtml = `Pendaftaran Pasien Sakit (Tag: ${escapeHtml(item.tag || '-')})`;
            formFieldsHtml = `
                <div class="row g-2 mt-1">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold mb-1">Anamnesa / Keluhan Pasien</label>
                        <textarea rows="2" class="form-control form-control-sm" oninput="updateItemPayloadField(${idx}, 'anamnesa', this.value)">${escapeHtml(payload.anamnesa || '')}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Tekanan Darah (Tensi)</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Contoh: 120/80" value="${escapeHtml(payload.blood_pressure || '')}" oninput="updateItemPayloadField(${idx}, 'blood_pressure', this.value)">
                    </div>
                </div>
            `;
        } else if (type === 'survey') {
            badgeHtml = `<span class="badge bg-success px-2 py-1"><i class="bx bx-clipboard me-1"></i>Survey Kesehatan</span>`;
            titleHtml = `Survey Tag <b>${escapeHtml(item.tag || '-')}</b>`;
            
            // Photo thumbnail preview
            let photoThumbHtml = '';
            if (item.photos_base64 && item.photos_base64.length > 0) {
                photoThumbHtml = `
                    <div class="d-flex gap-2 align-items-center mt-2">
                        <span class="small text-muted me-1">Foto Survey (${item.photos_base64.length}):</span>
                        ${item.photos_base64.map((p, pIdx) => `
                            <img src="${p}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;" title="Foto #${pIdx+1}">
                        `).join('')}
                    </div>
                `;
            }

            formFieldsHtml = `
                <div class="row g-2 mt-1">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Barcode Tag</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(item.tag || '')}" oninput="updateItemRootField(${idx}, 'tag', this.value)">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-bold mb-1">Catatan Survey</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(payload.survey_note || payload.note || '')}" oninput="updateItemPayloadField(${idx}, 'survey_note', this.value)">
                    </div>
                    <div class="col-12 mt-2">
                        <div class="d-flex flex-wrap gap-3 small">
                            <label class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" ${payload.survey_balita === 'ya' || payload.survey_balita === true ? 'checked' : ''} onchange="updateItemPayloadField(${idx}, 'survey_balita', this.checked ? 'ya' : 'tidak')">
                                <span class="form-check-label">Ada Balita</span>
                            </label>
                            <label class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" ${payload.survey_merokok === 'ya' || payload.survey_merokok === true ? 'checked' : ''} onchange="updateItemPayloadField(${idx}, 'survey_merokok', this.checked ? 'ya' : 'tidak')">
                                <span class="form-check-label">Anggota Merokok</span>
                            </label>
                            <label class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" ${payload.survey_jamban === 'ya' || payload.survey_jamban === true ? 'checked' : ''} onchange="updateItemPayloadField(${idx}, 'survey_jamban', this.checked ? 'ya' : 'tidak')">
                                <span class="form-check-label">Jamban Sehat</span>
                            </label>
                            <label class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" ${payload.survey_air_bersih === 'ya' || payload.survey_air_bersih === true ? 'checked' : ''} onchange="updateItemPayloadField(${idx}, 'survey_air_bersih', this.checked ? 'ya' : 'tidak')">
                                <span class="form-check-label">Air Bersih</span>
                            </label>
                        </div>
                    </div>
                    ${photoThumbHtml}
                </div>
            `;
        } else if (category === 'registration') {
            badgeHtml = `<span class="badge bg-secondary px-2 py-1"><i class="bx bx-file me-1"></i>Registrasi</span>`;
            titleHtml = `<b>${escapeHtml(item.name || item.full_name || 'Pasien')}</b> (NIK: ${escapeHtml(item.nik || '-')})`;
            formFieldsHtml = `
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">Nama Pasien</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(item.name || item.full_name || '')}" oninput="updateRegistrationField(${idx}, 'name', this.value)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">NIK</label>
                        <input type="text" maxlength="16" class="form-control form-control-sm font-monospace" value="${escapeHtml(item.nik || '')}" oninput="updateRegistrationField(${idx}, 'nik', this.value)">
                    </div>
                </div>
            `;
        } else {
            // Attendance or generic
            badgeHtml = `<span class="badge bg-secondary px-2 py-1"><i class="bx bx-time me-1"></i>Absensi / Data</span>`;
            titleHtml = `Item Absensi (${escapeHtml(item.type || 'absen')})`;
            formFieldsHtml = `
                <div class="small text-muted mt-1">
                    Waktu: ${escapeHtml(item.capturedAt || '-')} • ID: ${escapeHtml(item.id || '-')}
                </div>
            `;
        }

        card.innerHTML = `
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        ${badgeHtml}
                        <span class="small text-dark">${titleHtml}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeItemFromImport('${category}', ${idx})" title="Hapus item ini agar tidak di-import">
                        <i class="bx bx-trash" style="font-size:0.85rem;"></i>
                    </button>
                </div>
                ${formFieldsHtml}
            </div>
        `;

        return card;
    }

    function updateItemPayloadField(idx, field, value) {
        if (globalEditableKader[idx]) {
            if (!globalEditableKader[idx].payload) globalEditableKader[idx].payload = {};
            globalEditableKader[idx].payload[field] = value;
        }
    }

    function updateItemRootField(idx, field, value) {
        if (globalEditableKader[idx]) {
            globalEditableKader[idx][field] = value;
        }
    }

    function updateRegistrationField(idx, field, value) {
        if (globalEditableRegistrations[idx]) {
            globalEditableRegistrations[idx][field] = value;
            if (field === 'name') globalEditableRegistrations[idx]['full_name'] = value;
        }
    }

    function removeItemFromImport(category, idx) {
        if (!confirm('Hapus item ini dari antrian yang akan di-import?')) return;
        if (category === 'kader') {
            globalEditableKader.splice(idx, 1);
        } else if (category === 'attendance') {
            globalEditableAttendance.splice(idx, 1);
        } else if (category === 'registration') {
            globalEditableRegistrations.splice(idx, 1);
        }
        updateCountersAndList();
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return String(str || '');
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function submitGlobalImportJson() {
        let jsonPayloadString = '';

        if (globalActiveEditorMode === 'cards') {
            const currentSynced = {
                user: (globalCurrentParsedJson && globalCurrentParsedJson.user) ? globalCurrentParsedJson.user : { name: 'Kader Mobile' },
                exported_at: (globalCurrentParsedJson && globalCurrentParsedJson.exported_at) ? globalCurrentParsedJson.exported_at : new Date().toISOString(),
                kader_items: globalEditableKader,
                attendance_items: globalEditableAttendance,
                registration_items: globalEditableRegistrations
            };
            jsonPayloadString = JSON.stringify(currentSynced);
        } else {
            const rawText = document.getElementById('globalJsonCodeEditor')?.value || '';
            try {
                JSON.parse(rawText);
                jsonPayloadString = rawText;
            } catch(e) {
                alert('Sintaks JSON tidak valid! Harap perbaiki sebelum melakukan import:\n' + e.message);
                return;
            }
        }

        // Pastikan tidak kosong
        try {
            const checkObj = JSON.parse(jsonPayloadString);
            const total = (checkObj.kader_items?.length || 0) + (checkObj.attendance_items?.length || 0) + ((checkObj.registration_items || checkObj.registrations)?.length || 0);
            if (total === 0) {
                alert('Tidak ada data yang dapat di-import di dalam JSON.');
                return;
            }
        } catch(e) {}

        // Switch to loading
        document.getElementById('importStateSelect').style.display = 'none';
        document.getElementById('importStateLoading').style.display = 'block';
        document.getElementById('btnSubmitImport').disabled = true;

        const formData = new FormData();
        formData.append('json_content', jsonPayloadString);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch("{{ route('registration.offline.import_json') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();
            document.getElementById('importStateLoading').style.display = 'none';

            if (response.ok && data.success) {
                // SUCCESS STATE
                document.getElementById('importStateSuccess').style.display = 'block';
                document.getElementById('successTitle').textContent = 'Import Data Berhasil!';
                document.getElementById('successMessage').textContent = data.message || 'Data berhasil disinkronkan ke database.';

                // Badges
                const badgeContainer = document.getElementById('successBadges');
                badgeContainer.innerHTML = '';
                const imported = data.imported || {};
                const badgeDefs = [
                    { label: 'Total', count: imported.total, color: 'bg-primary' },
                    { label: 'Tambah Penduduk', count: imported.quick_add, color: 'bg-info' },
                    { label: 'Tautkan Tag', count: imported.link, color: 'bg-primary' },
                    { label: 'Survey Kesehatan', count: imported.survey, color: 'bg-success' },
                    { label: 'Pasien Sakit', count: imported.quick_register, color: 'bg-warning text-dark' },
                    { label: 'Absensi', count: imported.attendance, color: 'bg-secondary' },
                ];
                badgeDefs.forEach(b => {
                    if (b.count > 0 || b.label === 'Total') {
                        const span = document.createElement('span');
                        span.className = `badge ${b.color} px-3 py-2 fw-semibold`;
                        span.style.fontSize = '0.8rem';
                        span.textContent = `${b.label}: ${b.count || 0}`;
                        badgeContainer.appendChild(span);
                    }
                });

                // Partial fail/skipped warnings
                const partialFailSection = document.getElementById('partialFailSection');
                const partialFailList = document.getElementById('partialFailList');
                partialFailList.innerHTML = '';
                if (data.failed_details && data.failed_details.length > 0) {
                    partialFailSection.style.display = 'block';
                    document.getElementById('partialFailTitle').textContent = `Catatan Perhatian (${data.failed_details.length} data dilewati/gagal):`;
                    data.failed_details.forEach(f => {
                        const li = document.createElement('li');
                        li.textContent = f;
                        partialFailList.appendChild(li);
                    });
                } else {
                    partialFailSection.style.display = 'none';
                }

                // Log details
                const logList = document.getElementById('importLogList');
                logList.innerHTML = '';
                const details = data.details || [];
                document.getElementById('logCount').textContent = details.length;
                details.forEach(d => {
                    const li = document.createElement('li');
                    li.className = 'text-muted mb-1';
                    li.textContent = d;
                    logList.appendChild(li);
                });

                document.getElementById('btnSubmitImport').style.display = 'none';
                document.getElementById('btnResetImport').style.display = 'inline-block';

            } else {
                // FAIL STATE
                const failReasons = data.fail_reasons || (data.failed_details) || [data.message || 'Terjadi kesalahan tidak diketahui saat memproses data ke database.'];
                showGlobalImportError('Gagal Mengimpor File JSON', data.message || 'Data gagal disimpan ke database.', failReasons);
            }

        } catch (netErr) {
            document.getElementById('importStateLoading').style.display = 'none';
            showGlobalImportError('Kesalahan Jaringan / Server', 'Tidak dapat menghubungi server SIPPINTER.', [
                'Koneksi ke server terputus atau respon server tidak valid: ' + netErr.message,
                'Pastikan server web lokal (Laragon/Nginx/Apache) berjalan normal.'
            ]);
        }
    }

    function showGlobalImportError(title, message, reasons) {
        document.getElementById('importStateSelect').style.display = 'none';
        document.getElementById('importStateLoading').style.display = 'none';
        document.getElementById('importStateSuccess').style.display = 'none';
        document.getElementById('importStateError').style.display = 'block';

        document.getElementById('errorTitle').textContent = title || 'Import Gagal!';
        document.getElementById('errorMessage').textContent = message || 'Terjadi kesalahan saat memproses file JSON.';

        const reasonsList = document.getElementById('errorReasonsList');
        reasonsList.innerHTML = '';
        if (Array.isArray(reasons) && reasons.length > 0) {
            reasons.forEach(r => {
                const li = document.createElement('li');
                li.className = 'mb-1';
                li.textContent = r;
                reasonsList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = typeof reasons === 'string' ? reasons : 'Struktur data tidak sesuai atau terjadi kendala validasi database.';
            reasonsList.appendChild(li);
        }

        document.getElementById('btnSubmitImport').style.display = 'none';
        document.getElementById('btnResetImport').style.display = 'inline-block';
    }

    // Drag and drop events
    document.addEventListener('DOMContentLoaded', function() {
        const dropzone = document.getElementById('globalDropzoneBox');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#4f46e5';
                    dropzone.style.background = '#eef2ff';
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.background = '#ffffff';
                }, false);
            });
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files[0]) {
                    handleGlobalFileSelected(files[0]);
                }
            }, false);
        }
    });
</script>
