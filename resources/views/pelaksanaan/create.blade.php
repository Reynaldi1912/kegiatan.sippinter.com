@extends('layouts.app')

@section('title', 'Form Pelaksanaan Kegiatan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <a href="{{ route('pelaksanaan.index') }}" class="text-decoration-none small fw-semibold text-primary">
      <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Laporan Pelaksanaan
    </a>
    <h4 class="fw-bold mb-0 text-dark mt-1">Form Laporan Pelaksanaan Kegiatan (PRD 3.6)</h4>
  </div>
</div>

<!-- Pilihan Penugasan -->
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('pelaksanaan.create') }}" class="row g-2 align-items-center">
      <div class="col-md-9">
        <label class="form-label small fw-bold text-muted mb-1">Pilih Penugasan yang Dilaporkan</label>
        <select name="penugasan_id" class="form-select" onchange="this.form.submit()">
          @foreach($penugasans as $p)
            <option value="{{ $p->id }}" {{ $selectedPenugasan && $selectedPenugasan->id === $p->id ? 'selected' : '' }}>
              {{ $p->nomor_surat_tugas }} — {{ $p->kegiatan->nama_kegiatan ?? '' }} (PJ: {{ $p->penanggungJawab->name ?? '' }})
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 d-grid pt-md-4">
        <button type="submit" class="btn btn-outline-primary">Muat Data Penugasan</button>
      </div>
    </form>
  </div>
</div>

@if($selectedPenugasan)
  <form method="POST" action="{{ route('pelaksanaan.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="penugasan_id" value="{{ $selectedPenugasan->id }}">
    <input type="hidden" name="client_sync_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

    <div class="row g-4">
      <div class="col-lg-5">
        <!-- A. Informasi Kegiatan (PRD 3.6.A) -->
        <div class="card mb-4">
          <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold text-dark">A. Informasi Kegiatan & Penugasan RPK</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <small class="text-muted d-block">Nama Kegiatan RPK</small>
              <div class="fw-bold text-dark">{{ $selectedPenugasan->kegiatan->nama_kegiatan ?? '-' }}</div>
              <span class="badge badge-soft-primary mt-1">{{ $selectedPenugasan->kegiatan->kode_kegiatan ?? '' }} • {{ $selectedPenugasan->nomor_surat_tugas }}</span>
            </div>
            <div class="mb-3">
              <small class="text-muted d-block">Tanggal Pelaksanaan Terjadwal</small>
              <div class="fw-semibold text-dark">{{ $selectedPenugasan->tanggal_mulai->translatedFormat('d F Y') }}</div>
            </div>
            <div class="mb-3">
              <small class="text-muted d-block">Lokasi Kegiatan</small>
              <div class="fw-semibold text-dark"><i class="bx bx-map text-danger me-1"></i> {{ $selectedPenugasan->kegiatan->lokasi ?? '-' }}</div>
            </div>
            <div class="mb-3">
              <small class="text-muted d-block">Penanggung Jawab</small>
              <div class="fw-semibold text-dark">{{ $selectedPenugasan->penanggungJawab->name ?? '-' }}</div>
            </div>
            @if($selectedPenugasan->tupoksi)
              <div class="p-3 rounded-3 bg-light border">
                <small class="text-muted fw-bold text-uppercase d-block">Terhubung dengan Tupoksi</small>
                <div class="fw-bold text-primary">{{ $selectedPenugasan->tupoksi->kode_tupoksi }} - {{ $selectedPenugasan->tupoksi->nama_tupoksi }}</div>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <!-- B. Hasil Pelaksanaan (PRD 3.6.B) -->
        <div class="card mb-4">
          <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold text-dark">B. Hasil Pelaksanaan Kegiatan</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-semibold">Uraian Hasil Kegiatan <span class="text-danger">*</span></label>
                <textarea name="uraian_hasil" rows="4" class="form-control" required placeholder="Tuliskan hasil pelaksanaan kegiatan secara lengkap dan terukur...">{{ old('uraian_hasil') }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Jumlah Target</label>
                <input type="number" name="jumlah_target" class="form-control" value="{{ old('jumlah_target', $selectedPenugasan->target_pekerjaan) }}" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Jumlah Realisasi <span class="text-danger">*</span></label>
                <input type="number" name="jumlah_realisasi" class="form-control" value="{{ old('jumlah_realisasi', $selectedPenugasan->target_pekerjaan) }}" min="0" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Satuan</label>
                <input type="text" name="satuan" class="form-control" value="{{ old('satuan', $selectedPenugasan->satuan) }}" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Keterangan Capaian</label>
                <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="Contoh: Target tercapai 100%, seluruh sasaran terlayani">
              </div>
            </div>
          </div>
        </div>

        <!-- C. Dokumentasi (PRD 3.6.C) -->
        <div class="card mb-4">
          <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold text-dark">C. Dokumentasi Kegiatan (Foto & Dokumen)</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="bx bx-camera text-primary me-1"></i> Upload Foto Kegiatan</label>
                <input type="file" name="foto_dokumentasi[]" class="form-control" accept="image/*" capture="environment" multiple>
                <small class="text-muted">Mendukung kamera langsung maupun galeri (JPG/PNG/WEBP)</small>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="bx bx-file text-info me-1"></i> Upload Dokumen Pendukung</label>
                <input type="file" name="dokumen_pendukung[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png" multiple>
                <small class="text-muted">Daftar hadir, berita acara, atau notulensi (PDF/Doc/Excel)</small>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Tambah Keterangan Dokumentasi</label>
                <input type="text" name="keterangan_dokumentasi" class="form-control" placeholder="Contoh: Foto pemeriksaan sasaran dan daftar hadir kegiatan">
              </div>
            </div>
          </div>
        </div>

        <!-- D. Informasi Pelaksanaan & Tindakan (PRD 3.6.D) -->
        <div class="card">
          <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold text-dark">D. Informasi Waktu Pelaksanaan & Catatan Pegawai</h6>
          </div>
          <div class="card-body">
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Mulai Pelaksanaan</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ $selectedPenugasan->tanggal_mulai->format('Y-m-d') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Selesai Pelaksanaan</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Catatan Pegawai / Tindak Lanjut Lapangan</label>
                <textarea name="catatan_pegawai" rows="2" class="form-control" placeholder="Tuliskan catatan kendala lapangan atau rekomendasi tindak lanjut..."></textarea>
              </div>
            </div>

            <!-- Tindakan: Simpan Draft, Kirim Laporan, Lihat Status (PRD 3.6) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
              <a href="{{ route('penugasan.show', $selectedPenugasan) }}" class="btn btn-outline-secondary">
                Lihat Status Penugasan
              </a>
              <div class="d-flex gap-2">
                <button type="submit" name="aksi_simpan" value="draft" class="btn btn-outline-primary">
                  <i class="bx bx-save me-1"></i> Simpan Draft
                </button>
                <button type="submit" name="aksi_simpan" value="kirim" class="btn btn-primary">
                  <i class="bx bx-send me-1"></i> Kirim Laporan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
@endif
@endsection

