@extends('layouts.app')

@section('title', 'Approval Line & Persetujuan Dana')

@section('content')
<style>
  .approval-nav-tabs {
    border-bottom: none;
    gap: 0.45rem;
    background: #ffffff;
    padding: 0.45rem;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
  }
  .approval-nav-tabs .nav-link {
    border: none !important;
    border-radius: 10px !important;
    padding: 0.6rem 1.15rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
  }
  .approval-nav-tabs .nav-link:hover {
    color: #1f398a;
    background: #f1f5f9;
  }
  .approval-nav-tabs .nav-link.active {
    background: #1f398a !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(31, 57, 138, 0.22);
  }

  /* Compact Step Pill on Table */
  .step-pill-chain {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
  }
  .step-pill-node {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.38rem 0.7rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  .step-pill-number {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #1f398a;
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  /* Clean Modal Styling */
  .modal-approval-clean .modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    overflow: hidden;
  }
  .modal-approval-header {
    background: linear-gradient(135deg, #1f398a 0%, #172b6b 100%);
    color: #ffffff;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .modal-approval-close {
    background: rgba(255, 255, 255, 0.14);
    border: none;
    color: #ffffff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    cursor: pointer;
    transition: background 0.2s ease;
  }
  .modal-approval-close:hover {
    background: rgba(255, 255, 255, 0.28);
  }

  /* Vertical Stepper inside Modal */
  .approval-stepper-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .approval-stepper-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1.1rem;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    transition: border-color 0.2s ease;
  }
  .approval-stepper-row:hover {
    border-color: #cbd5e1;
  }
  .approval-stepper-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }
  .approval-stepper-badge {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: #1f398a;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .approval-stepper-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f8fafc;
    padding: 0.5rem 0.85rem;
    border-radius: 11px;
    border: 1px solid #eef2f6;
    min-width: 230px;
  }
  .approver-avatar-mini {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #e0e7ff;
    color: #1f398a;
    font-weight: 700;
    font-size: 0.78rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  /* Quick Guide Banner */
  .guide-step-box {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.55rem 0.9rem;
    font-size: 0.8rem;
    color: #334155;
  }
  .guide-step-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #1f398a;
    color: #fff;
    font-weight: 700;
    font-size: 0.72rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
</style>

<!-- Header & Tombol Aksi Utama -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-check-shield text-primary me-1"></i> Approval Line & Persetujuan Dana
    </h4>
    <p class="text-muted mb-0 small">
      Atur urutan pejabat penyetuju per modul dan proses pengajuan dana melalui Web atau Mobile.
    </p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahRule">
      <i class="bx bx-plus me-1"></i> Atur Pejabat Approval
    </button>
    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalBuatPengajuan">
      <i class="bx bx-paper-plane me-1"></i> Ajukan Dana Baru
    </button>
  </div>
</div>

<!-- Cara Pakai Singkat (3 Langkah) -->
<div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 16px;">
  <div class="row g-2 align-items-center">
    <div class="col-12 col-md-4">
      <div class="guide-step-box">
        <span class="guide-step-num">1</span>
        <div><strong>Cek Modul:</strong> Klik <em>Detail</em> pada modul untuk melihat urutan pejabat penyetuju.</div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="guide-step-box">
        <span class="guide-step-num">2</span>
        <div><strong>Ajukan Dana:</strong> Klik <em>Ajukan Dana Baru</em> untuk mengirim permohonan pencairan.</div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="guide-step-box">
        <span class="guide-step-num">3</span>
        <div><strong>Approve:</strong> Pejabat menyetujui berurutan (Tahap 1 → 2 → 3) lewat Web / Mobile.</div>
      </div>
    </div>
  </div>
</div>

<!-- Ringkasan 4 Kartu Statistik -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card p-3 h-100 border-0 shadow-sm" style="border-radius: 16px;">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small d-block">Modul & Tahap</span>
          <h4 class="fw-extrabold mb-0 mt-1 text-dark">{{ count($moduleCatalog) }} Modul · {{ $summary['total_aturan'] }} Level</h4>
        </div>
        <div class="avatar bg-label-primary rounded-3 p-2">
          <i class="bx bx-git-branch fs-3"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card p-3 h-100 border-0 shadow-sm" style="border-radius: 16px;">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small d-block">Menunggu Persetujuan</span>
          <h4 class="fw-extrabold mb-0 mt-1 text-warning">{{ $summary['menunggu'] }} Pengajuan</h4>
        </div>
        <div class="avatar bg-label-warning rounded-3 p-2">
          <i class="bx bx-time-five fs-3"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card p-3 h-100 border-0 shadow-sm" style="border-radius: 16px;">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small d-block">Total Diajukan</span>
          <h4 class="fw-extrabold mb-0 mt-1 text-dark">Rp {{ number_format($summary['total_nominal_diajukan'], 0, ',', '.') }}</h4>
        </div>
        <div class="avatar bg-label-info rounded-3 p-2">
          <i class="bx bx-wallet fs-3"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card p-3 h-100 border-0 shadow-sm" style="border-radius: 16px;">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small d-block">Total Disetujui</span>
          <h4 class="fw-extrabold mb-0 mt-1 text-success">Rp {{ number_format($summary['total_nominal_disetujui'], 0, ',', '.') }}</h4>
        </div>
        <div class="avatar bg-label-success rounded-3 p-2">
          <i class="bx bx-check-double fs-3"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- =========================================================================
     NAVIGASI TAB PER SECTION
     ========================================================================= -->
<ul class="nav nav-pills approval-nav-tabs mb-4" id="approvalSectionTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link {{ $activeTab === 'modul' ? 'active' : '' }}"
            id="tab-modul-btn"
            data-bs-toggle="pill"
            data-bs-target="#pane-modul"
            type="button"
            role="tab">
      <i class="bx bx-category-alt"></i> 1. Modul & Pejabat Approval
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link {{ $activeTab === 'pengajuan' ? 'active' : '' }}"
            id="tab-pengajuan-btn"
            data-bs-toggle="pill"
            data-bs-target="#pane-pengajuan"
            type="button"
            role="tab">
      <i class="bx bx-transfer-alt"></i> 2. Pengajuan & Proses Approve
      @if($summary['menunggu'] > 0)
        <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $summary['menunggu'] }}</span>
      @endif
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link {{ $activeTab === 'aturan' ? 'active' : '' }}"
            id="tab-aturan-btn"
            data-bs-toggle="pill"
            data-bs-target="#pane-aturan"
            type="button"
            role="tab">
      <i class="bx bx-slider-alt"></i> 3. Pengaturan Level ({{ $summary['total_aturan'] }})
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link {{ $activeTab === 'database' ? 'active' : '' }}"
            id="tab-database-btn"
            data-bs-toggle="pill"
            data-bs-target="#pane-database"
            type="button"
            role="tab">
      <i class="bx bx-data"></i> 4. Data Tabel Keuangan
    </button>
  </li>
</ul>

<div class="tab-content" id="approvalSectionTabContent">

  <!-- =========================================================================
       TAB 1: DAFTAR MODUL & URUTAN PEJABAT APPROVAL LINE
       ========================================================================= -->
  <div class="tab-pane fade {{ $activeTab === 'modul' ? 'show active' : '' }}" id="pane-modul" role="tabpanel">
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Nama Modul</th>
              <th>Alur Pejabat Penyetuju (Tahap 1 → Selesai)</th>
              <th class="text-center">Jumlah Tahap</th>
              <th class="text-center">Pengajuan Aktif</th>
              <th class="text-end pe-4">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($moduleCatalog as $modKey => $mod)
              <tr>
                <td class="ps-4" style="min-width: 240px;">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-sm bg-label-{{ $mod['badge_color'] }} rounded-3 p-2">
                      <i class="bx {{ $mod['icon'] }} fs-5"></i>
                    </div>
                    <div>
                      <div class="fw-bold text-dark">{{ $mod['nama_modul'] }}</div>
                      <small class="text-muted">Tabel: <code>{{ $mod['tabel_utama'] }}</code></small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="step-pill-chain">
                    @forelse($mod['rules'] as $r)
                      <div class="step-pill-node">
                        <span class="step-pill-number">{{ $r->step_order }}</span>
                        <div>
                          <div class="fw-bold text-dark" style="font-size: 0.8rem;">
                            {{ $r->approverUser?->name ?? strtoupper($r->approver_role ?: 'SUPER_ADMIN') }}
                          </div>
                          <div class="text-muted" style="font-size: 0.7rem;">{{ $r->nama_level }}</div>
                        </div>
                      </div>
                      @if(! $loop->last)
                        <i class="bx bx-chevron-right text-muted"></i>
                      @endif
                    @empty
                      <span class="text-muted small">Belum ada pejabat diatur</span>
                    @endforelse
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge rounded-pill bg-label-primary px-3 py-2 fw-bold">
                    {{ $mod['rules']->count() }} Tahap
                  </span>
                </td>
                <td class="text-center">
                  @if($mod['pending_count'] > 0)
                    <span class="badge bg-warning text-dark">{{ $mod['pending_count'] }} Menunggu</span>
                  @else
                    <span class="text-muted small">{{ $mod['requests_count'] }} Selesai</span>
                  @endif
                </td>
                <td class="text-end pe-4">
                  <button type="button"
                          class="btn btn-sm btn-primary px-3 rounded-3"
                          data-bs-toggle="modal"
                          data-bs-target="#modalDetailModul_{{ $modKey }}">
                    <i class="bx bx-show me-1"></i> Detail
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       TAB 2: DAFTAR PENGAJUAN & PROSES APPROVE
       ========================================================================= -->
  <div class="tab-pane fade {{ $activeTab === 'pengajuan' ? 'show active' : '' }}" id="pane-pengajuan" role="tabpanel">
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Pengajuan</th>
              <th>Modul</th>
              <th>Pengaju</th>
              <th>Nominal</th>
              <th>Giliran Pejabat Saat Ini</th>
              <th>Status</th>
              <th class="text-end pe-4">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($approvalRequests as $req)
              @php
                $activeStep = $req->activeStep();
              @endphp
              <tr>
                <td class="ps-4">
                  <span class="badge bg-label-primary fw-bold mb-1">{{ $req->nomor_pengajuan }}</span>
                  <div class="fw-bold text-dark">{{ $req->judul_pengajuan }}</div>
                </td>
                <td><span class="badge bg-label-info">{{ $req->label_modul }}</span></td>
                <td>
                  <div class="fw-semibold text-dark">{{ $req->pengaju?->name ?? '-' }}</div>
                  <small class="text-muted">{{ $req->pengaju?->jabatan ?: ($req->pengaju?->role ?? 'Pegawai') }}</small>
                </td>
                <td>
                  <div class="fw-bold text-primary">Rp {{ number_format($req->nominal_diajukan, 0, ',', '.') }}</div>
                  <small class="text-muted">{{ $req->sumber_dana }}</small>
                </td>
                <td>
                  @if($req->status === 'disetujui')
                    <span class="text-success fw-semibold small">
                      <i class="bx bx-check-double"></i> Selesai ({{ $req->total_steps }}/{{ $req->total_steps }} Tahap)
                    </span>
                  @elseif($activeStep)
                    <div class="fw-bold text-dark small">
                      Tahap {{ $activeStep->step_order }}: {{ $activeStep->assignedApprover?->name ?? strtoupper($activeStep->approver_role ?: 'SUPER_ADMIN') }}
                    </div>
                    <small class="text-muted">{{ $activeStep->nama_level }}</small>
                  @else
                    <small class="text-muted">Tahap {{ $req->current_step }}/{{ $req->total_steps }}</small>
                  @endif
                </td>
                <td>
                  @if($req->status === 'disetujui')
                    <span class="badge bg-success">Disetujui</span>
                  @elseif($req->status === 'ditolak')
                    <span class="badge bg-danger">Ditolak</span>
                  @elseif($req->status === 'revisi')
                    <span class="badge bg-warning text-dark">Revisi</span>
                  @else
                    <span class="badge bg-warning text-dark">Tahap {{ $req->current_step }}/{{ $req->total_steps }}</span>
                  @endif
                </td>
                <td class="text-end pe-4">
                  <button type="button"
                          class="btn btn-sm {{ in_array($req->status, ['menunggu', 'dalam_proses']) ? 'btn-success' : 'btn-outline-primary' }} px-3 rounded-3"
                          data-bs-toggle="modal"
                          data-bs-target="#modalDetailRequest_{{ $req->id }}">
                    {{ in_array($req->status, ['menunggu', 'dalam_proses']) ? 'Proses Approve' : 'Detail' }}
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan persetujuan dana.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       TAB 3: PENGATURAN LEVEL PER MODUL
       ========================================================================= -->
  <div class="tab-pane fade {{ $activeTab === 'aturan' ? 'show active' : '' }}" id="pane-aturan" role="tabpanel">
    @foreach($moduleCatalog as $modKey => $mod)
      <div class="card border-0 shadow-sm mb-3" style="border-radius: 18px;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bx {{ $mod['icon'] }} text-primary fs-5"></i>
            <h6 class="fw-bold mb-0 text-dark">{{ $mod['nama_modul'] }}</h6>
          </div>
          <span class="badge bg-label-primary">{{ $mod['rules']->count() }} Tahap</span>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4" style="width: 110px;">Tahap</th>
                <th>Nama Level Persetujuan</th>
                <th>Pejabat Approver</th>
                <th>Batas Plafon</th>
                <th>Akses</th>
                <th class="text-end pe-4">Hapus</th>
              </tr>
            </thead>
            <tbody>
              @forelse($mod['rules'] as $rule)
                <tr>
                  <td class="ps-4">
                    <span class="badge rounded-pill bg-primary px-3 py-1">Tahap {{ $rule->step_order }}</span>
                  </td>
                  <td class="fw-bold text-dark">{{ $rule->nama_level }}</td>
                  <td>
                    <div class="fw-semibold text-dark">
                      {{ $rule->approverUser?->name ?? strtoupper($rule->approver_role ?: 'SUPER_ADMIN') }}
                    </div>
                    <small class="text-muted">{{ $rule->approverUser?->jabatan ?: ($rule->approverUser?->role ?? $rule->approver_role) }}</small>
                  </td>
                  <td>
                    @if($rule->nominal_maksimal)
                      Rp {{ number_format($rule->nominal_minimal, 0, ',', '.') }} - Rp {{ number_format($rule->nominal_maksimal, 0, ',', '.') }}
                    @else
                      Semua Nominal
                    @endif
                  </td>
                  <td>
                    @if($rule->allow_web)<span class="badge bg-label-info me-1">Web</span>@endif
                    @if($rule->allow_mobile)<span class="badge bg-label-success">Mobile</span>@endif
                  </td>
                  <td class="text-end pe-4">
                    <form action="{{ route('approval.rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus level ini?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-danger">
                        <i class="bx bx-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-3">Belum ada level diatur.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  </div>

  <!-- =========================================================================
       TAB 4: DATA TABEL KEUANGAN `sippinter_new`
       ========================================================================= -->
  <div class="tab-pane fade {{ $activeTab === 'database' ? 'show active' : '' }}" id="pane-database" role="tabpanel">
    <div class="row g-3">
      <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 18px;">
          <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">1. Tabel <code>budget_plans</code> (Rencana Anggaran BOK)</h6>
            <span class="badge bg-label-info">{{ $sharedDbRecords['budget_plans']->count() }} Data</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Tahun</th>
                  <th>Rincian Menu Kegiatan</th>
                  <th>Kode Rekening</th>
                  <th>Total Anggaran</th>
                  <th>Approver</th>
                  <th class="pe-4">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($sharedDbRecords['budget_plans'] as $bp)
                  <tr>
                    <td class="ps-4 fw-bold">{{ $bp['fiscal_year'] }}</td>
                    <td>
                      <div class="fw-semibold text-dark">{{ $bp['rincian_menu'] }}</div>
                      <small class="text-muted">{{ $bp['sub_bidang'] }}</small>
                    </td>
                    <td><code>{{ $bp['kode_rekening'] }}</code></td>
                    <td class="fw-bold text-primary">Rp {{ number_format($bp['total_amount'], 0, ',', '.') }}</td>
                    <td>{{ $bp['approved_by_name'] }}</td>
                    <td class="pe-4">
                      <span class="badge bg-label-success">{{ strtoupper($bp['status']) }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted py-3">Kosong</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 18px;">
          <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">2. Tabel <code>bok_spjs</code> & <code>bok_spj_details</code> (Kuitansi SPJ BOK)</h6>
            <span class="badge bg-label-success">{{ $sharedDbRecords['bok_spjs']->count() }} Data</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">No. Kuitansi</th>
                  <th>Keperluan</th>
                  <th>Nominal</th>
                  <th>Penanda Tangan (`signatories`)</th>
                  <th class="pe-4">Penerima</th>
                </tr>
              </thead>
              <tbody>
                @forelse($sharedDbRecords['bok_spjs'] as $spj)
                  <tr>
                    <td class="ps-4 fw-bold">{{ $spj['receipt_number'] }}</td>
                    <td>{{ $spj['purpose'] }}</td>
                    <td class="fw-bold text-success">Rp {{ number_format($spj['amount'], 0, ',', '.') }}</td>
                    <td>
                      <div class="small">1. {{ $spj['signatory_left'] }}</div>
                      <div class="small">2. {{ $spj['signatory_center'] }}</div>
                      <div class="small">3. {{ $spj['signatory_right'] }}</div>
                    </td>
                    <td class="pe-4">
                      @foreach($spj['participants'] as $pt)
                        <div class="small">• {{ $pt->name }} (Rp {{ number_format((float) $pt->total, 0, ',', '.') }})</div>
                      @endforeach
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-3">Kosong</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- =========================================================================
     MODAL DETAIL APPROVAL LINE PER MODUL (RAPI, RINGKAS & MUDAH DIBACA)
     ========================================================================= -->
@foreach($moduleCatalog as $modKey => $mod)
  <div class="modal fade modal-approval-clean" id="modalDetailModul_{{ $modKey }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
      <div class="modal-content">
        <!-- Header Navy -->
        <div class="modal-approval-header">
          <div>
            <span class="badge bg-white text-dark fw-bold mb-1" style="font-size: 0.7rem;">
              ALUR PERSETUJUAN MODUL
            </span>
            <h5 class="fw-bold text-white mb-0">{{ $mod['nama_modul'] }}</h5>
          </div>
          <button type="button" class="modal-approval-close" data-bs-dismiss="modal" aria-label="Close">
            <i class="bx bx-x"></i>
          </button>
        </div>

        <div class="modal-body p-4">
          <!-- Urutan Pejabat Approver -->
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="fw-bold text-dark small">
              <i class="bx bx-user-check text-primary me-1"></i> Urutan Pejabat Penyetuju ({{ $mod['rules']->count() }} Tahap)
            </span>
            <span class="badge bg-label-secondary">Tabel: <code>{{ $mod['tabel_utama'] }}</code></span>
          </div>

          <div class="approval-stepper-list">
            @forelse($mod['rules'] as $rule)
              @php
                $pejabatNama = $rule->approverUser?->name ?? strtoupper($rule->approver_role ?: 'SUPER_ADMIN');
                $pejabatJabatan = $rule->approverUser?->jabatan ?: ($rule->approverUser?->role ?? $rule->approver_role);
                $inisial = strtoupper(substr($pejabatNama, 0, 2));
              @endphp
              <div class="approval-stepper-row">
                <!-- Kiri: Tahap & Nama Level -->
                <div class="approval-stepper-left">
                  <div class="approval-stepper-badge">
                    {{ $rule->step_order }}
                  </div>
                  <div>
                    <div class="text-primary fw-bold" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">
                      Tahap {{ $rule->step_order }}
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 0.94rem;">
                      {{ $rule->nama_level }}
                    </div>
                    <div class="d-flex gap-1 mt-1">
                      @if($rule->allow_web)
                        <span class="badge bg-label-info" style="font-size: 0.68rem;">Web</span>
                      @endif
                      @if($rule->allow_mobile)
                        <span class="badge bg-label-success" style="font-size: 0.68rem;">Mobile</span>
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Kanan: Pejabat Approver -->
                <div class="approval-stepper-right">
                  <div class="approver-avatar-mini">{{ $inisial }}</div>
                  <div>
                    <div class="text-muted" style="font-size: 0.68rem;">Pejabat Penyetuju:</div>
                    <div class="fw-bold text-dark" style="font-size: 0.85rem; line-height: 1.2;">
                      {{ $pejabatNama }}
                    </div>
                    <div class="text-secondary" style="font-size: 0.73rem;">
                      {{ $pejabatJabatan }}
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-3">Belum ada pejabat diatur pada modul ini.</div>
            @endforelse
          </div>
        </div>

        <div class="modal-footer bg-light border-top py-2 px-4 d-flex justify-content-between">
          <small class="text-muted">Persetujuan berjalan berurutan mulai dari Tahap 1.</small>
          <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

<!-- =========================================================================
     MODAL DETAIL PENGAJUAN & EKSEKUSI APPROVAL (RAPI & RINGKAS)
     ========================================================================= -->
@foreach($approvalRequests as $req)
  <div class="modal fade modal-approval-clean" id="modalDetailRequest_{{ $req->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 660px;">
      <div class="modal-content">
        <div class="modal-approval-header">
          <div>
            <span class="badge bg-white text-dark fw-bold mb-1" style="font-size: 0.7rem;">
              {{ $req->nomor_pengajuan }} · {{ strtoupper($req->sumber_pengajuan) }}
            </span>
            <h5 class="fw-bold text-white mb-0">{{ $req->judul_pengajuan }}</h5>
          </div>
          <button type="button" class="modal-approval-close" data-bs-dismiss="modal" aria-label="Close">
            <i class="bx bx-x"></i>
          </button>
        </div>

        <div class="modal-body p-4">
          <!-- Ringkasan Nominal & Pengaju -->
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 rounded-3 mb-3 border" style="background: #f8fafc;">
            <div>
              <div class="text-muted small">Diajukan Oleh:</div>
              <div class="fw-bold text-dark">{{ $req->pengaju?->name ?? '-' }}</div>
              <small class="text-muted">{{ $req->kegiatan?->nama_kegiatan ?? $req->label_modul }}</small>
            </div>
            <div class="text-md-end">
              <div class="text-muted small">Nominal Dana ({{ $req->sumber_dana }})</div>
              <div class="fs-5 fw-extrabold text-primary">Rp {{ number_format($req->nominal_diajukan, 0, ',', '.') }}</div>
            </div>
          </div>

          <!-- Daftar Tahap & Status Pejabat -->
          <div class="fw-bold text-dark small mb-2">
            <i class="bx bx-git-branch text-primary me-1"></i> Progres Persetujuan Pejabat:
          </div>
          <div class="approval-stepper-list mb-3">
            @foreach($req->steps as $step)
              <div class="approval-stepper-row {{ $req->current_step === $step->step_order && in_array($req->status, ['menunggu', 'dalam_proses']) ? 'border-primary' : '' }}">
                <div class="approval-stepper-left">
                  <div class="approval-stepper-badge" style="background: {{ $step->status === 'disetujui' ? '#10b981' : ($step->status === 'ditolak' ? '#ef4444' : '#1f398a') }};">
                    @if($step->status === 'disetujui')
                      <i class="bx bx-check"></i>
                    @else
                      {{ $step->step_order }}
                    @endif
                  </div>
                  <div>
                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                      Tahap {{ $step->step_order }}: {{ $step->nama_level }}
                    </div>
                    <div class="text-muted small">
                      Pejabat: <strong>{{ $step->assignedApprover?->name ?? strtoupper($step->approver_role ?: 'SUPER_ADMIN') }}</strong>
                    </div>
                    @if($step->catatan)
                      <div class="text-secondary fst-italic" style="font-size: 0.76rem;">"{{ $step->catatan }}"</div>
                    @endif
                  </div>
                </div>

                <div class="text-end">
                  @if($step->status === 'disetujui')
                    <span class="badge bg-success">Disetujui</span>
                    @if($step->acted_via)
                      <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">via {{ strtoupper($step->acted_via) }}</small>
                    @endif
                  @elseif($step->status === 'ditolak')
                    <span class="badge bg-danger">Ditolak</span>
                  @elseif($step->status === 'revisi')
                    <span class="badge bg-warning text-dark">Revisi</span>
                  @else
                    <span class="badge bg-label-warning">Menunggu</span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>

          <!-- Form Eksekusi bila masih menunggu -->
          @if(in_array($req->status, ['menunggu', 'dalam_proses']))
            <form action="{{ route('approval.requests.action', $req) }}" method="POST" class="border-top pt-3">
              @csrf
              <div class="row g-2 mb-3">
                <div class="col-12 col-md-5">
                  <label class="form-label small fw-semibold mb-1">Nominal Disetujui (Rp)</label>
                  <input type="number" name="nominal_disetujui" class="form-control form-control-sm" value="{{ (int) $req->nominal_diajukan }}" min="0" step="1000">
                </div>
                <div class="col-12 col-md-7">
                  <label class="form-label small fw-semibold mb-1">Catatan (Opsional)</label>
                  <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Tambahkan catatan persetujuan...">
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" name="keputusan" value="ditolak" class="btn btn-sm btn-outline-danger px-3">
                  Tolak
                </button>
                <button type="submit" name="keputusan" value="revisi" class="btn btn-sm btn-warning px-3">
                  Revisi
                </button>
                <button type="submit" name="keputusan" value="disetujui" class="btn btn-sm btn-success px-4">
                  <i class="bx bx-check me-1"></i> Setujui Tahap {{ $req->current_step }}
                </button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
@endforeach

<!-- =========================================================================
     MODAL TAMBAH ATURAN LEVEL APPROVAL LINE
     ========================================================================= -->
<div class="modal fade modal-approval-clean" id="modalTambahRule" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="{{ route('approval.rules.store') }}">
      @csrf
      <div class="modal-approval-header">
        <h5 class="fw-bold text-white mb-0">Atur Pejabat Level Approval</h5>
        <button type="button" class="modal-approval-close" data-bs-dismiss="modal" aria-label="Close">
          <i class="bx bx-x"></i>
        </button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" name="nama_aturan" value="Aturan Approval Puskesmas">
        <input type="hidden" name="nominal_minimal" value="0">
        <input type="hidden" name="approver_type" value="role_or_user">
        <input type="hidden" name="allow_web" value="1">
        <input type="hidden" name="allow_mobile" value="1">
        <input type="hidden" name="is_active" value="1">

        <div class="row g-3">
          <div class="col-12 col-md-8">
            <label class="form-label fw-semibold small">Pilih Modul</label>
            <select name="modul" class="form-select" required>
              <option value="pencairan_dana">Pencairan Dana Kegiatan (BOK & BLUD)</option>
              <option value="kegiatan_rpk">Perencanaan RUK / RPK & Budget Plan</option>
              <option value="pelaksanaan_spj">SPJ BOK, Kuitansi & Laporan Pelaksanaan</option>
              <option value="penugasan">Surat Tugas & Aktivitas Lapangan</option>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold small">Tahap Ke-</label>
            <input type="number" name="step_order" class="form-control" value="1" min="1" max="10" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold small">Nama Tahap / Jabatan Verifikator</label>
            <input type="text" name="nama_level" class="form-control" placeholder="Contoh: Verifikasi Bendahara / Pengesahan Kepala Puskesmas" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold small">Pilih Pejabat Penyetuju (Dari Data Pegawai)</label>
            <select name="approver_user_id" class="form-select" required>
              @foreach($approverUsers as $u)
                <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->jabatan ?: $u->role }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold small">Role Fallback</label>
            <select name="approver_role" class="form-select">
              <option value="super_admin">super_admin</option>
              @foreach($availableRoles as $r)
                <option value="{{ $r }}">{{ $r }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-sm btn-primary px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- =========================================================================
     MODAL AJUKAN DANA BARU
     ========================================================================= -->
<div class="modal fade modal-approval-clean" id="modalBuatPengajuan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="{{ route('approval.requests.store') }}">
      @csrf
      <div class="modal-approval-header">
        <h5 class="fw-bold text-white mb-0">Ajukan Persetujuan Dana Baru</h5>
        <button type="button" class="modal-approval-close" data-bs-dismiss="modal" aria-label="Close">
          <i class="bx bx-x"></i>
        </button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold small">Judul Pengajuan</label>
            <input type="text" name="judul_pengajuan" class="form-control" placeholder="Contoh: Pencairan Transport Posyandu Balita" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold small">Modul</label>
            <select name="modul" class="form-select" required>
              <option value="pencairan_dana">Pencairan Dana Kegiatan</option>
              <option value="kegiatan_rpk">Anggaran RUK / RPK</option>
              <option value="pelaksanaan_spj">SPJ Pelaksanaan</option>
              <option value="penugasan">Surat Tugas</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold small">Sumber Dana</label>
            <select name="sumber_dana" class="form-select" required>
              <option value="BOK Puskesmas">BOK Puskesmas</option>
              <option value="BLUD Puskesmas">BLUD Puskesmas</option>
              <option value="JKN Kapitasi">JKN Kapitasi</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold small">Nominal Diajukan (Rp)</label>
            <input type="number" name="nominal_diajukan" class="form-control" value="1500000" min="0" step="10000" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold small">Kegiatan Terkait (Opsional)</label>
            <select name="kegiatan_id" class="form-select">
              <option value="">-- Pilih Kegiatan --</option>
              @foreach($kegiatans as $keg)
                <option value="{{ $keg->id }}">{{ $keg->kode_kegiatan }} - {{ Str::limit($keg->nama_kegiatan, 32) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold small">Keterangan Singkat</label>
            <textarea name="uraian_pengajuan" class="form-control" rows="2" placeholder="Rincian singkat penggunaan dana..." required></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-sm btn-primary px-4">Kirim Pengajuan</button>
      </div>
    </form>
  </div>
</div>
@endsection
