<footer class="content-footer footer border-top mt-auto"
    style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-color: rgba(226, 232, 240, 0.8) !important; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
    <div class="container-xxl">
        <div
            class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column gap-2">

            {{-- Left: Copyright & App Name --}}
            <div class="d-flex align-items-center flex-wrap gap-2 text-muted" style="font-size: 0.82rem;">
                <div class="d-flex align-items-center gap-1_5 fw-bold text-dark">
                    <div
                        style="width: 22px; height: 22px; border-radius: 6px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 0.75rem; box-shadow: 0 2px 5px rgba(99, 102, 241, 0.25);">
                        <i class="bx bxs-shield-plus"></i>
                    </div>
                    <span style="letter-spacing: -0.01em;">Sippinter</span>
                </div>
                <span class="text-secondary opacity-50 d-none d-sm-inline">•</span>
                <span class="text-secondary fw-medium d-none d-sm-inline">Sistem Informasi Pelayanan Publik Integrasi
                    Terpadu</span>
                <span class="text-secondary opacity-50">•</span>
                <span class="text-muted">© {{ date('Y') }}</span>
            </div>

            {{-- Right: System Status & Version --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge rounded-pill bg-label-success d-inline-flex align-items-center gap-1 py-1 px-2"
                    style="font-size: 0.72rem; font-weight: 600;">
                    <span
                        style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981; display: inline-block;"></span>
                    <span>Sistem Aktif</span>
                </span>
                <span class="badge rounded-pill bg-label-primary py-1 px-2"
                    style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.03em;">
                    v2.5 PRO
                </span>
            </div>

        </div>
    </div>
</footer>