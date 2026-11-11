<!-- Modal Pilih Display Kasir -->
@if(in_array(Auth::user()->role ?? '', ['super_admin', 'kasir']))
@php
    $cashiers = \DB::table('users')
        ->where('role', 'kasir')
        ->orWhere('role', 'super_admin')
        ->select('id', 'username', 'fullname', 'role')
        ->get();
@endphp
<div class="modal fade" id="selectCashierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
            
            {{-- Modal Header --}}
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 38px; height: 38px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                        <i class="fa-solid fa-cash-register text-white" style="font-size: 1.05rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0" style="font-size: 1.08rem; letter-spacing: -0.01em;">Pilih Layar Display Kasir</h5>
                        <div class="text-white-50 small" style="font-size: 0.75rem;">Buka monitor display persetujuan pembayaran pasien di tab baru</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4" style="background-color: #f8fafc; max-height: 72vh; overflow-y: auto;">
                
                {{-- Pilihan 1: Layar Display Umum --}}
                <div class="mb-4">
                    <div class="text-uppercase text-secondary fw-bold mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        Layar Utama / Gabungan
                    </div>
                    <a href="{{ route('cashier.consent.display') }}" target="_blank" class="ass1-cashier-choice-card active-card">
                        <div class="ass1-cashier-icon-box active-icon">
                            <i class="fa-solid fa-desktop"></i>
                        </div>
                        <div class="ass1-cashier-info">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="ass1-cashier-name mb-0">Display Kasir Utama (Layar Umum)</h6>
                                <span class="badge rounded-pill bg-label-primary px-2" style="font-size: 0.68rem; font-weight: 700;">DEFAULT</span>
                            </div>
                            <p class="ass1-cashier-desc mt-1 mb-0">Tampilkan monitor persetujuan pembayaran kasir umum untuk pasien</p>
                        </div>
                        <div class="ass1-cashier-arrow">
                            <span class="d-none d-sm-inline">Buka Layar</span>
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </div>
                    </a>
                </div>

                {{-- Pilihan 2: Per Petugas Kasir --}}
                <div>
                    <div class="text-uppercase text-secondary fw-bold mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        Layar Khusus Petugas Kasir
                    </div>
                    <div class="row g-2_5">
                        @foreach($cashiers as $c)
                            <div class="col-12">
                                <a href="{{ route('cashier.consent.display') }}?cashier_id={{ $c->id }}" target="_blank" class="ass1-cashier-choice-card">
                                    <div class="ass1-cashier-icon-box">
                                        <i class="fa-solid fa-user-check"></i>
                                    </div>
                                    <div class="ass1-cashier-info">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h6 class="ass1-cashier-name mb-0">{{ $c->fullname ?? $c->username }}</h6>
                                            @if($c->role === 'super_admin')
                                                <span class="badge rounded-pill bg-label-warning px-2" style="font-size: 0.65rem; font-weight: 700;">Super Admin</span>
                                            @else
                                                <span class="badge rounded-pill bg-label-success px-2" style="font-size: 0.65rem; font-weight: 700;">Kasir</span>
                                            @endif
                                        </div>
                                        <div class="ass1-cashier-meta mt-1">
                                            <span><i class="bx bx-id-card me-1"></i>ID Petugas: <strong>#{{ $c->id }}</strong></span>
                                            <span class="opacity-50 mx-1">•</span>
                                            <span class="text-muted"><i class="bx bx-user me-1"></i>{{ $c->username }}</span>
                                        </div>
                                    </div>
                                    <div class="ass1-cashier-arrow">
                                        <span class="d-none d-sm-inline">Buka Layar</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer py-3 px-4 bg-white border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="text-muted small" style="font-size: 0.76rem;">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i> Layar display kasir akan dibuka pada jendela browser baru.
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" style="font-weight: 600; font-size: 0.8rem;">
                    Batal
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .ass1-cashier-choice-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        text-decoration: none !important;
        position: relative;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    .ass1-cashier-choice-card:hover {
        transform: translateY(-2px);
        border-color: #818cf8;
        box-shadow: 0 8px 20px -3px rgba(99, 102, 241, 0.15);
        background: #ffffff;
    }
    .ass1-cashier-choice-card.active-card {
        border-color: #c7d2fe;
        background: linear-gradient(180deg, #ffffff 0%, #f5f3ff 100%);
    }
    .ass1-cashier-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: #ede9fe;
        color: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }
    .ass1-cashier-choice-card:hover .ass1-cashier-icon-box {
        transform: scale(1.08);
    }
    .ass1-cashier-icon-box.active-icon {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.28);
    }
    .ass1-cashier-info {
        flex-grow: 1;
        min-width: 0;
    }
    .ass1-cashier-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .ass1-cashier-choice-card:hover .ass1-cashier-name {
        color: #4f46e5;
    }
    .ass1-cashier-desc {
        font-size: 0.74rem;
        color: #64748b;
        line-height: 1.35;
    }
    .ass1-cashier-meta {
        font-size: 0.74rem;
        color: #64748b;
    }
    .ass1-cashier-arrow {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #94a3b8;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .ass1-cashier-choice-card:hover .ass1-cashier-arrow {
        color: #4f46e5;
        transform: translate(2px, -2px);
    }
</style>
@endif
