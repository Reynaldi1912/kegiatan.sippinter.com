@extends('layouts.app')

@section('title', 'Detail & Verifikasi Laporan Pelaksanaan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <a href="{{ route('pelaksanaan.index') }}" class="text-decoration-none small fw-semibold text-primary">
      <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Laporan Pelaksanaan
    </a>
    <h4 class="fw-bold mb-0 text-dark mt-1">
      Laporan Pelaksanaan: {{ $pelaksanaan->kode_laporan }}
    </h4>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <span class="badge {{ $pelaksanaan->status_sinkronisasi === 'tersinkron' ? 'badge-soft-success' : 'badge-soft-warning' }} px-3 py-2">
      <i class="bx bx-sync me-1"></i> {{ strtoupper($pelaksanaan->status_sinkronisasi) }} ({{ strtoupper($pelaksanaan->sumber_input) }})
    </span>
    <span class="badge {{ $pelaksanaan->status_laporan === 'disetujui' ? 'badge-soft-success' : ($pelaksanaan->status_laporan === 'perlu_perbaikan' ? 'badge-soft-danger' : 'badge-soft-warning') }} px-3 py-2">
      STATUS: {{ strtoupper(str_replace('_', ' ', $pelaksanaan->status_laporan)) }}
    </span>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <!-- Informasi Kegiatan & Hasil Pelaksanaan (PRD 3.6) -->
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">Hasil Pelaksanaan Kegiatan & Capaian</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <span class="badge badge-soft-primary mb-1">{{ $pelaksanaan->penugasan->nomor_surat_tugas ?? '-' }}</span>
          <h5 class="fw-bold text-dark mb-1">{{ $pelaksanaan->penugasan->kegiatan->nama_kegiatan ?? '-' }}</h5>
          <small class="text-muted">
            <i class="bx bx-map text-danger me-1"></i> {{ $pelaksanaan->penugasan->kegiatan->lokasi ?? '-' }}
            <span class="mx-1">•</span>
            Pelaksanaan: {{ $pelaksanaan->tanggal_mulai->translatedFormat('d M Y') }} s/d {{ $pelaksanaan->tanggal_selesai->translatedFormat('d M Y') }}
          </small>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-sm-4">
            <div class="p-3 rounded-3 border bg-light text-center">
              <small class="text-muted d-block">Target Penugasan</small>
              <h4 class="fw-bold text-dark mb-0">{{ $pelaksanaan->jumlah_target }} {{ $pelaksanaan->satuan }}</h4>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="p-3 rounded-3 border bg-light text-center">
              <small class="text-muted d-block">Realisasi Dilaporkan</small>
              <h4 class="fw-bold text-primary mb-0">{{ $pelaksanaan->jumlah_realisasi }} {{ $pelaksanaan->satuan }}</h4>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="p-3 rounded-3 border bg-light text-center">
              <small class="text-muted d-block">Persentase Realisasi</small>
              <h4 class="fw-bold text-success mb-0">
                {{ $pelaksanaan->jumlah_target > 0 ? round(($pelaksanaan->jumlah_realisasi / $pelaksanaan->jumlah_target) * 100, 1) : 0 }}%
              </h4>
            </div>
          </div>
        </div>

        <h6 class="fw-bold text-uppercase text-muted small mb-1">Uraian Hasil Kegiatan</h6>
        <p class="text-dark mb-3">{{ $pelaksanaan->uraian_hasil }}</p>

        @if($pelaksanaan->keterangan)
          <h6 class="fw-bold text-uppercase text-muted small mb-1">Keterangan Capaian</h6>
          <p class="text-muted mb-3">{{ $pelaksanaan->keterangan }}</p>
        @endif

        @if($pelaksanaan->catatan_pegawai)
          <div class="p-3 rounded-3 bg-light border">
            <small class="text-muted fw-bold text-uppercase d-block mb-1">Catatan Pegawai Pelaksana ({{ $pelaksanaan->pelapor->name ?? '-' }})</small>
            <div class="text-dark">{{ $pelaksanaan->catatan_pegawai }}</div>
          </div>
        @endif
      </div>
    </div>

    <!-- Dokumentasi Kegiatan (PRD 3.6.C) -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h6 class="mb-0 fw-bold text-dark">Dokumentasi Kegiatan ({{ $pelaksanaan->dokumentasis->count() }} File)</h6>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-4">
          @forelse($pelaksanaan->dokumentasis as $dok)
            <div class="col-md-6">
              <div class="p-3 rounded-3 border h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="badge badge-soft-info">{{ strtoupper($dok->jenis_dokumen) }}</span>
                  <small class="text-muted">{{ $dok->ukuran_kb }} KB</small>
                </div>
                <div class="fw-bold text-dark small mb-1">{{ $dok->nama_file }}</div>
                <p class="text-muted small mb-0">{{ $dok->keterangan }}</p>
              </div>
            </div>
          @empty
            <div class="col-12 text-muted small">Belum ada file dokumentasi terlampir.</div>
          @endforelse
        </div>

        <!-- Form Tambah Dokumentasi -->
        <form method="POST" action="{{ route('pelaksanaan.dokumentasi', $pelaksanaan) }}" enctype="multipart/form-data" class="border-top pt-3">
          @csrf
          <h6 class="fw-bold small text-uppercase text-muted mb-2">Tambah Lampiran Dokumentasi</h6>
          <div class="row g-2">
            <div class="col-md-3">
              <select name="jenis_dokumen" class="form-select form-select-sm" required>
                <option value="foto">Foto Kegiatan</option>
                <option value="dokumen">Dokumen Resmi</option>
                <option value="lampiran_pendukung">Lampiran Pendukung</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="file" name="file_dokumentasi" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
              <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Keterangan dokumentasi">
            </div>
            <div class="col-md-2 d-grid">
              <button type="submit" class="btn btn-sm btn-outline-primary">Upload</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Panel Verifikasi Web Application (PRD 3.6 & 3.7) -->
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
          <i class="bx bx-check-double text-primary me-1"></i> Panel Verifikasi Laporan (Web App)
        </h6>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">
          Verifikasi laporan pelaksanaan pegawai. Laporan yang <strong>Disetujui</strong> otomatis mencatatkan realisasi ke <strong>Capaian Tupoksi</strong> pegawai terkait.
        </p>

        @if($pelaksanaan->verified_at)
          <div class="alert alert-secondary small">
            <strong>Terakhir Diverifikasi:</strong> {{ $pelaksanaan->verified_at->translatedFormat('d M Y H:i') }}<br>
            <strong>Oleh:</strong> {{ $pelaksanaan->verifikator->name ?? '-' }}<br>
            @if($pelaksanaan->catatan_verifikasi)
              <strong>Catatan:</strong> {{ $pelaksanaan->catatan_verifikasi }}
            @endif
          </div>
        @endif

        <form method="POST" action="{{ route('pelaksanaan.verifikasi', $pelaksanaan) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label fw-semibold">Keputusan Verifikasi</label>
            <select name="status_laporan" class="form-select" required>
              <option value="disetujui" {{ $pelaksanaan->status_laporan === 'disetujui' ? 'selected' : '' }}>
                Setujui Laporan (Update Capaian Tupoksi)
              </option>
              <option value="diterima" {{ $pelaksanaan->status_laporan === 'diterima' ? 'selected' : '' }}>
                Laporan Diterima (Sedang Direview)
              </option>
              <option value="perlu_perbaikan" {{ $pelaksanaan->status_laporan === 'perlu_perbaikan' ? 'selected' : '' }}>
                Perlu Diperbaiki (Kembalikan ke Pegawai)
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Verifikator</label>
            <textarea name="catatan_verifikasi" rows="3" class="form-control" placeholder="Tuliskan catatan pengesahan atau instruksi perbaikan...">{{ $pelaksanaan->catatan_verifikasi }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="bx bx-check-circle me-1"></i> Simpan Hasil Verifikasi
          </button>
        </form>
      </div>
    </div>

    @if($pelaksanaan->tupoksi)
      <div class="card">
        <div class="card-header border-bottom">
          <h6 class="mb-0 fw-bold text-dark">Dampak pada Capaian Tupoksi</h6>
        </div>
        <div class="card-body">
          <span class="badge badge-soft-purple mb-1">{{ $pelaksanaan->tupoksi->kode_tupoksi }}</span>
          <div class="fw-bold text-dark mb-2">{{ $pelaksanaan->tupoksi->nama_tupoksi }}</div>
          <div class="d-flex justify-content-between small mb-1">
            <span>Realisasi Terverifikasi:</span>
            <strong>{{ $pelaksanaan->tupoksi->realisasi }} / {{ $pelaksanaan->tupoksi->target }} {{ $pelaksanaan->tupoksi->satuan }} ({{ $pelaksanaan->tupoksi->persentase_capaian }}%)</strong>
          </div>
          <div class="progress mb-3" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: {{ $pelaksanaan->tupoksi->persentase_capaian }}%"></div>
          </div>
          <a href="{{ route('tupoksi.show', $pelaksanaan->tupoksi) }}" class="btn btn-sm btn-outline-primary w-100">
            Lihat Halaman Tupoksi
          </a>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection

