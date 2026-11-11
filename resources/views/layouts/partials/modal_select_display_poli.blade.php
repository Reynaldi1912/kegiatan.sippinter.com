<!-- Modal Pilih Display Poli -->
@php
    $allPolisForDisplay = \DB::table('polis')
        ->where('name', 'not like', '%pustu%')
        ->where('name', 'not like', '%desa%')
        ->where('name', 'not like', '%poskesdes%')
        ->where('name', 'not like', '%ponkesdes%')
        ->orderBy('name', 'asc')
        ->get();
@endphp
<div class="modal fade" id="modalSelectDisplayPoli" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-tv text-white" style="font-size: 0.95rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0" style="font-size: 1.05rem; letter-spacing: -0.01em;">Pilih Layar Display TV</h5>
                        <div class="text-white-50 small" style="font-size: 0.74rem;">Buka tampilan display publik antrian poli di tab baru</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc; max-height: 70vh; overflow-y: auto;">
                <div class="row g-3">
                    <!-- Semua Poli (Default Dashboard) -->
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('antrian.poli.display') }}" target="_blank" class="ass1-display-choice-card active-card">
                            <div class="ass1-display-icon-box active-icon">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div class="ass1-display-info">
                                <h6 class="ass1-display-name">Semua Poli</h6>
                                <p class="ass1-display-desc">Display antrian gabungan seluruh poli</p>
                            </div>
                            <span class="ass1-display-badge">Utama</span>
                        </a>
                    </div>

                    <!-- Per Poli -->
                    @foreach($allPolisForDisplay as $poli)
                        <div class="col-md-4 col-sm-6">
                            <a href="{{ route('antrian.poli.display', ['poli' => $poli->name]) }}" target="_blank" class="ass1-display-choice-card">
                                <div class="ass1-display-icon-box">
                                    <i class="fa-solid fa-hospital-user"></i>
                                </div>
                                <div class="ass1-display-info">
                                    <h6 class="ass1-display-name">{{ $poli->name }}</h6>
                                    <p class="ass1-display-desc">Layar khusus {{ $poli->name }}</p>
                                </div>
                                <span class="ass1-display-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer py-2.5 px-4 bg-white border-top d-flex justify-content-between">
                <span class="text-muted small" style="font-size: 0.74rem;">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> Klik pilihan untuk membuka layar display di jendela baru.
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600; font-size: 0.78rem;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .ass1-display-choice-card {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        text-decoration: none !important;
        position: relative;
        height: 100%;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 6px -1px rgba(15, 23, 42, 0.03);
    }
    .ass1-display-choice-card:hover {
        transform: translateY(-3px);
        border-color: #818cf8;
        box-shadow: 0 8px 20px -3px rgba(99, 102, 241, 0.18);
    }
    .ass1-display-choice-card.active-card {
        border-color: #c7d2fe;
        background: linear-gradient(180deg, #ffffff 0%, #f5f3ff 100%);
    }
    .ass1-display-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #ede9fe;
        color: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        margin-bottom: 12px;
        transition: transform 0.2s ease;
    }
    .ass1-display-choice-card:hover .ass1-display-icon-box {
        transform: scale(1.08);
    }
    .ass1-display-icon-box.active-icon {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
    }
    .ass1-display-info {
        flex-grow: 1;
    }
    .ass1-display-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .ass1-display-choice-card:hover .ass1-display-name {
        color: #4f46e5;
    }
    .ass1-display-desc {
        font-size: 0.72rem;
        color: #64748b;
        margin-bottom: 0;
        line-height: 1.3;
    }
    .ass1-display-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 9999px;
        background: #e0e7ff;
        color: #4338ca;
        letter-spacing: 0.02em;
    }
    .ass1-display-arrow {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 0.72rem;
        color: #94a3b8;
        transition: all 0.2s ease;
    }
    .ass1-display-choice-card:hover .ass1-display-arrow {
        color: #4f46e5;
        transform: translate(2px, -2px);
    }
</style>
