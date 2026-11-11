@extends('layouts.app')

@section('title', 'Detail Riwayat Kegiatan')

@section('content')
<!-- Halaman Detail Riwayat (PRD 3.8: Informasi kegiatan, Penugasan, Hasil pelaksanaan, Dokumentasi, Tupoksi terkait, Status akhir) -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <a href="{{ route('riwayat.index') }}" class="text-decoration-none small fw-semibold text-primary">
      <i class="bx bx-arrow-back me-1"></i> Kembali ke Riwayat Kegiatan
    </a>
    <h4 class="fw-bold mb-0 text-dark mt-1">Detail Riwayat Kegiatan: {{ $pelaksanaan->kode_laporan }}</h4>
  </div>
  <span class="badge {{ $pelaksanaan->status_laporan === 'disetujui' ? 'badge-soft-success' : 'badge-soft-warning' }} px-3 py-2">
    STATUS AKHIR: {{ strtoupper(str_replace('_', ' ', $pelaksanaan->status_laporan)) }}
  </span>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">1. Informasi Kegiatan & Penugasan</h6>
      </div>
      <div class="card-body">
        <h5 class="fw-bold text-dark mb-1">{{ $pelaksanaan->kegiatan->nama_kegiatan ?? '-' }}</h5>
        <p class="text-muted small mb-3">{{ $pelaksanaan->kegiatan->deskripsi ?? '' }}</p>

        <div class="row g-3">
          <div class="col-md-4">
            <small class="text-muted d-block">Nomor Surat Tugas</small>
            <div class="fw-bold text-dark">{{ $pelaksanaan->penugasan->nomor_surat_tugas ?? '-' }}</div>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Lokasi Pelaksanaan</small>
            <div class="fw-bold text-dark">{{ $pelaksanaan->kegiatan->lokasi ?? '-' }}</div>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Tanggal Pelaksanaan</small>
            <div class="fw-bold text-dark">{{ $pelaksanaan->tanggal_mulai->translatedFormat('d M Y') }} - {{ $pelaksanaan->tanggal_selesai->translatedFormat('d M Y') }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">2. Hasil Pelaksanaan & Dokumentasi</h6>
      </div>
      <div class="card-body">
        <div class="p-3 rounded-3 bg-light border mb-3">
          <div class="small text-muted mb-1">Realisasi Capaian: <strong>{{ $pelaksanaan->jumlah_realisasi }} / {{ $pelaksanaan->jumlah_target }} {{ $pelaksanaan->satuan }}</strong></div>
          <div class="text-dark">{{ $pelaksanaan->uraian_hasil }}</div>
        </div>

        <h6 class="fw-bold small text-uppercase text-muted mb-2">Dokumentasi Terlampir ({{ $pelaksanaan->dokumentasis->count() }})</h6>
        <div class="row g-2">
          @forelse($pelaksanaan->dokumentasis as $dok)
            <div class="col-md-6">
              <div class="p-2 rounded-3 border small">
                <div class="fw-bold text-dark">{{ $dok->nama_file }}</div>
                <div class="text-muted">{{ $dok->keterangan }} ({{ $dok->ukuran_kb }} KB)</div>
              </div>
            </div>
          @empty
            <div class="col-12 text-muted small">Tidak ada lampiran dokumentasi.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">3. Tupoksi Terkait & Status Akhir</h6>
      </div>
      <div class="card-body">
        @if($pelaksanaan->tupoksi)
          <span class="badge badge-soft-purple mb-1">{{ $pelaksanaan->tupoksi->kode_tupoksi }}</span>
          <div class="fw-bold text-dark mb-2">{{ $pelaksanaan->tupoksi->nama_tupoksi }}</div>
          <p class="text-muted small">{{ $pelaksanaan->tupoksi->uraian_tugas }}</p>
        @else
          <p class="text-muted small">Tidak terhubung ke Tupoksi spesifik.</p>
        @endif

        <hr>
        <small class="text-muted d-block">Pelapor</small>
        <div class="fw-bold text-dark mb-2">{{ $pelaksanaan->pelapor->name ?? '-' }}</div>

        <small class="text-muted d-block">Verifikator Web Application</small>
        <div class="fw-bold text-dark mb-2">{{ $pelaksanaan->verifikator->name ?? 'Belum Diverifikasi' }}</div>

        @if($pelaksanaan->catatan_verifikasi)
          <div class="p-2 rounded-3 bg-light border small">
            <strong>Catatan Verifikasi:</strong> {{ $pelaksanaan->catatan_verifikasi }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

