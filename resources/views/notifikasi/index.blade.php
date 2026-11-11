@extends('layouts.app')

@section('title', 'Pusat Notifikasi Pegawai')

@section('content')
<style>
  /* ============================================================
     PUSAT NOTIFIKASI PEGAWAI - MODERN CLEAN HEALTHCARE DESIGN
     ============================================================ */
  .notif-main-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(20, 40, 90, 0.03);
    padding: 1.5rem;
  }

  .notif-filter-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 18px;
    box-shadow: 0 4px 15px rgba(20, 40, 90, 0.02);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
  }

  /* Segmented Pill Tabs */
  .notif-segmented-nav {
    display: inline-flex;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 25px;
    gap: 4px;
  }

  .notif-segmented-nav .nav-link {
    color: #64748b;
    font-weight: 600;
    font-size: 0.825rem;
    padding: 0.45rem 1.15rem;
    border-radius: 20px;
    border: none;
    text-decoration: none;
    transition: all 0.2s ease;
  }

  .notif-segmented-nav .nav-link.active {
    background: #1f398a;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(31, 57, 138, 0.25);
  }

  .notif-segmented-nav .nav-link:hover:not(.active) {
    color: #1e293b;
    background: #e2e8f0;
  }

  /* Action Buttons */
  .btn-notif-action {
    background: #1f398a;
    color: #ffffff;
    border: none;
    border-radius: 20px;
    padding: 0.5rem 1.25rem;
    font-size: 0.835rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
    text-decoration: none !important;
  }
  .btn-notif-action:hover {
    background: #172b6b;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(31, 57, 138, 0.2);
  }

  .btn-notif-outline-warning {
    background: #ffffff;
    color: #d97706;
    border: 1px solid #fcd34d;
    border-radius: 20px;
    padding: 0.5rem 1.25rem;
    font-size: 0.835rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
  }
  .btn-notif-outline-warning:hover {
    background: #fffbeb;
    color: #b45309;
    border-color: #f59e0b;
  }

  /* Notification Items */
  .notif-card-item {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 16px;
    padding: 1.15rem 1.35rem;
    margin-bottom: 0.85rem;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 1.15rem;
  }

  .notif-card-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(30, 58, 138, 0.05);
    border-color: #cbd5e1;
  }

  /* Unread Accent Highlight */
  .notif-card-item.is-unread {
    background: #fbfcfe;
    border-left: 4px solid #1f398a !important;
    border-color: #e2e8f0;
  }

  /* Notification Category Squircle Icons */
  .notif-squircle-icon {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
  }

  .icon-teal {
    background: #e0f2fe;
    color: #0284c7;
  }
  .icon-amber {
    background: #fef3c7;
    color: #d97706;
  }
  .icon-indigo {
    background: #e0e7ff;
    color: #4338ca;
  }
  .icon-purple {
    background: #f3e8ff;
    color: #7c3aed;
  }
  .icon-emerald {
    background: #dcfce7;
    color: #16a34a;
  }
  .icon-rose {
    background: #fee2e2;
    color: #dc2626;
  }
  .icon-orange {
    background: #ffedd5;
    color: #ea580c;
  }
  .icon-secondary {
    background: #f1f5f9;
    color: #64748b;
  }

  .notif-title-text {
    font-size: 0.94rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.25rem;
    line-height: 1.35;
  }

  .notif-desc-text {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.45;
    margin-bottom: 0;
  }

  /* Unread Dot */
  .notif-unread-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #ef4444;
    margin-right: 4px;
  }

  /* Open Detail Pill Button */
  .btn-open-detail {
    background: #1f398a;
    color: #ffffff;
    border: none;
    border-radius: 20px;
    padding: 0.45rem 1.15rem;
    font-size: 0.82rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
    text-decoration: none !important;
    white-space: nowrap;
  }
  .btn-open-detail:hover {
    background: #172b6b;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(31, 57, 138, 0.2);
  }

  /* Form Control Refinement */
  .notif-form-select {
    border-radius: 12px !important;
    border: 1px solid #cbd5e1 !important;
    font-size: 0.85rem !important;
    padding: 0.55rem 0.95rem !important;
    color: #334155 !important;
  }
  .notif-form-select:focus {
    border-color: #1f398a !important;
    box-shadow: 0 0 0 3px rgba(31, 57, 138, 0.12) !important;
  }
</style>

@php
  $jenisMeta = [
      'penugasan_baru' => ['label' => 'Penugasan Baru', 'class' => 'badge-soft-primary', 'icon' => 'bx-task', 'color' => 'icon-indigo'],
      'disposisi_baru' => ['label' => 'Disposisi Baru', 'class' => 'badge-soft-purple', 'icon' => 'bx-share-alt', 'color' => 'icon-purple'],
      'perubahan_jadwal' => ['label' => 'Perubahan Jadwal', 'class' => 'badge-soft-warning', 'icon' => 'bx-calendar-edit', 'color' => 'icon-amber'],
      'perubahan_lokasi' => ['label' => 'Perubahan Lokasi', 'class' => 'badge-soft-info', 'icon' => 'bx-map-pin', 'color' => 'icon-teal'],
      'perubahan_instruksi' => ['label' => 'Perubahan Instruksi', 'class' => 'badge-soft-warning', 'icon' => 'bx-message-square-edit', 'color' => 'icon-orange'],
      'pengingat_tenggat' => ['label' => 'Pengingat Tenggat Waktu', 'class' => 'badge-soft-danger', 'icon' => 'bx-alarm-exclamation', 'color' => 'icon-rose'],
      'laporan_diterima' => ['label' => 'Laporan Diterima', 'class' => 'badge-soft-info', 'icon' => 'bx-file-blank', 'color' => 'icon-teal'],
      'laporan_perlu_diperbaiki' => ['label' => 'Laporan Perlu Diperbaiki', 'class' => 'badge-soft-danger', 'icon' => 'bx-error-circle', 'color' => 'icon-rose'],
      'laporan_disetujui' => ['label' => 'Laporan Disetujui', 'class' => 'badge-soft-success', 'icon' => 'bx-check-shield', 'color' => 'icon-emerald'],
  ];
@endphp

<!-- Page Header Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
      <i class="bx bx-bell text-primary"></i> Pusat Notifikasi Pegawai
    </h4>
    <p class="text-muted mb-0 small">
      Pemberitahuan penugasan baru, perubahan jadwal kegiatan, pengingat tenggat waktu, dan update status verifikasi laporan
    </p>
  </div>

  <!-- Header Quick Actions -->
  <div class="d-flex flex-wrap gap-2">
    <form method="POST" action="{{ route('notifikasi.pengingat-tenggat') }}">
      @csrf
      <button type="submit" class="btn-notif-outline-warning">
        <i class="bx bx-alarm-exclamation"></i> Kirim Pengingat Tenggat Waktu
      </button>
    </form>

    @if($unreadCount > 0)
      <form method="POST" action="{{ route('notifikasi.mark-all-read') }}">
        @csrf
        <button type="submit" class="btn-notif-action">
          <i class="bx bx-check-double"></i> Tandai Semua Sudah Dibaca ({{ $unreadCount }})
        </button>
      </form>
    @endif
  </div>
</div>

<!-- Filter & Quick Selector Card -->
<div class="notif-filter-card">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <!-- Segmented Status Pills -->
    <div class="notif-segmented-nav">
      <a href="{{ route('notifikasi.index', ['jenis' => $jenis]) }}"
         class="nav-link {{ $statusBaca === '' ? 'active' : '' }}">
        Semua
      </a>
      <a href="{{ route('notifikasi.index', ['status' => 'unread', 'jenis' => $jenis]) }}"
         class="nav-link {{ $statusBaca === 'unread' ? 'active' : '' }}">
        Belum Dibaca @if($unreadCount > 0) ({{ $unreadCount }}) @endif
      </a>
      <a href="{{ route('notifikasi.index', ['status' => 'read', 'jenis' => $jenis]) }}"
         class="nav-link {{ $statusBaca === 'read' ? 'active' : '' }}">
        Sudah Dibaca
      </a>
    </div>

    <!-- Category Dropdown Filter Form -->
    <form method="GET" action="{{ route('notifikasi.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
      <input type="hidden" name="status" value="{{ $statusBaca }}">

      <div style="min-width: 220px;">
        <select name="jenis" class="form-select notif-form-select" onchange="this.form.submit()">
          <option value="">Semua Jenis Notifikasi</option>
          @foreach($jenisMeta as $key => $meta)
            <option value="{{ $key }}" {{ $jenis === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
          @endforeach
        </select>
      </div>

      @if($jenis !== '' || $statusBaca !== '')
        <a href="{{ route('notifikasi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
          Reset Filter
        </a>
      @endif
    </form>
  </div>
</div>

<!-- Notification Items Container Card -->
<div class="notif-main-card">
  @forelse($notifikasis as $notif)
    @php
      $meta = $jenisMeta[$notif->jenis_notifikasi] ?? [
          'label' => ucwords(str_replace('_', ' ', $notif->jenis_notifikasi)),
          'class' => 'badge-soft-secondary',
          'icon' => 'bx-bell',
          'color' => 'icon-secondary',
      ];
    @endphp

    <div class="notif-card-item {{ ! $notif->is_read ? 'is-unread' : '' }}">
      <!-- Left Themed Squircle Icon -->
      <div class="notif-squircle-icon {{ $meta['color'] }}">
        <i class="bx {{ $meta['icon'] }}"></i>
      </div>

      <!-- Middle Info Section -->
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
          <!-- Notification Type Badge -->
          <span class="badge {{ $meta['class'] }} rounded-pill px-2 py-1 fw-bold" style="font-size: 0.72rem;">
            {{ strtoupper($meta['label']) }}
          </span>

          <!-- Read/Unread Status Chip -->
          @if(! $notif->is_read)
            <span class="badge rounded-pill px-2 py-1" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; font-size: 0.72rem; font-weight: 700;">
              <span class="notif-unread-dot"></span> Belum Dibaca
            </span>
          @else
            <span class="badge rounded-pill px-2 py-1 text-muted" style="background: #f1f5f9; font-size: 0.72rem;">
              Sudah Dibaca
            </span>
          @endif

          <!-- Timestamp -->
          <small class="text-muted ms-auto d-none d-md-inline" style="font-size: 0.76rem;">
            <i class="bx bx-time-five me-1"></i> {{ $notif->created_at->translatedFormat('d M Y H:i') }} ({{ $notif->created_at->diffForHumans() }})
          </small>
        </div>

        <!-- Title -->
        <h6 class="notif-title-text">{{ $notif->judul }}</h6>

        <!-- Description / Message -->
        <p class="notif-desc-text">{{ $notif->pesan }}</p>

        <!-- Mobile timestamp -->
        <div class="d-md-none mt-2">
          <small class="text-muted" style="font-size: 0.72rem;">
            <i class="bx bx-time-five me-1"></i> {{ $notif->created_at->translatedFormat('d M Y H:i') }}
          </small>
        </div>
      </div>

      <!-- Right Action Button -->
      <div class="align-self-center flex-shrink-0">
        <a href="{{ route('notifikasi.buka', $notif) }}" class="btn-open-detail">
          Buka Detail <i class="bx bx-right-arrow-alt"></i>
        </a>
      </div>
    </div>
  @empty
    <!-- Empty State -->
    <div class="text-center py-5">
      <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
           style="width: 72px; height: 72px; background: #f1f5f9; color: #94a3b8; font-size: 2rem;">
        <i class="bx bx-bell-off"></i>
      </div>
      <h6 class="fw-bold text-dark mb-1">Tidak Ada Notifikasi</h6>
      <p class="text-muted small mb-3">Tidak ada notifikasi yang sesuai dengan filter yang Anda pilih.</p>
      @if($jenis !== '' || $statusBaca !== '')
        <a href="{{ route('notifikasi.index') }}" class="btn btn-sm btn-primary rounded-pill px-4">
          Lihat Semua Notifikasi
        </a>
      @endif
    </div>
  @endforelse
</div>
@endsection
