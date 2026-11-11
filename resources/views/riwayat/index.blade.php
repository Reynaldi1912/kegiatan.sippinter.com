@extends('layouts.app')

@section('title', 'Riwayat Kegiatan')

@section('content')
@php
  $namaBulan = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
      5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
  ];
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-history text-primary me-1"></i> Riwayat Kegiatan & Pelaksanaan (PRD 3.8)
    </h4>
    <p class="text-muted mb-0 small">
      Menampilkan seluruh riwayat penugasan dan kegiatan yang pernah dilaksanakan pegawai beserta status verifikasinya
    </p>
  </div>
</div>

<!-- Filter Periode & Filter Status (PRD 3.8) -->
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('riwayat.index') }}" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted mb-1">Filter Bulan</label>
        <select name="bulan" class="form-select">
          <option value="">Semua Bulan</option>
          @foreach($namaBulan as $num => $lbl)
            <option value="{{ $num }}" {{ (string) $periodeBulan === (string) $num ? 'selected' : '' }}>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-bold text-muted mb-1">Filter Tahun</label>
        <select name="tahun" class="form-select">
          @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
            <option value="{{ $y }}" {{ (string) $periodeTahun === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted mb-1">Filter Status Verifikasi</label>
        <select name="status_verifikasi" class="form-select">
          <option value="">Semua Status</option>
          <option value="disetujui" {{ $statusVerifikasi === 'disetujui' ? 'selected' : '' }}>Disetujui (Selesai)</option>
          <option value="dikirim" {{ $statusVerifikasi === 'dikirim' ? 'selected' : '' }}>Menunggu Verifikasi</option>
          <option value="perlu_perbaikan" {{ $statusVerifikasi === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
          <option value="draft" {{ $statusVerifikasi === 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-bold text-muted mb-1">Pegawai</label>
        <select name="pelapor_id" class="form-select">
          <option value="">Semua Pegawai</option>
          @foreach($pegawais as $p)
            <option value="{{ $p->id }}" {{ (string) $pelaporId === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
        <a href="{{ route('riwayat.index') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Tabel Riwayat Kegiatan (PRD 3.8) -->
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Nama Kegiatan & Penugasan</th>
          <th>Tanggal Pelaksanaan</th>
          <th>Pelapor</th>
          <th>Hasil Pelaksanaan</th>
          <th>Status Verifikasi</th>
          <th class="text-center">Detail Riwayat</th>
        </tr>
      </thead>
      <tbody>
        @forelse($riwayats as $item)
          <tr>
            <td>
              <span class="badge badge-soft-primary mb-1">{{ $item->kode_laporan }}</span>
              <div class="fw-bold text-dark">{{ $item->kegiatan->nama_kegiatan ?? '-' }}</div>
              <small class="text-muted">ST: {{ $item->penugasan->nomor_surat_tugas ?? '-' }}</small>
            </td>
            <td>
              <div class="fw-semibold">{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</div>
              <small class="text-muted">s/d {{ $item->tanggal_selesai->translatedFormat('d M Y') }}</small>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $item->pelapor->name ?? '-' }}</div>
              <small class="text-muted">{{ $item->pelapor->jabatan ?? '' }}</small>
            </td>
            <td>
              <div class="fw-bold text-primary">{{ $item->jumlah_realisasi }} / {{ $item->jumlah_target }} {{ $item->satuan }}</div>
              <small class="text-muted">{{ Str::limit($item->uraian_hasil, 70) }}</small>
            </td>
            <td>
              <span class="badge {{ $item->status_laporan === 'disetujui' ? 'badge-soft-success' : ($item->status_laporan === 'perlu_perbaikan' ? 'badge-soft-danger' : 'badge-soft-warning') }}">
                {{ strtoupper(str_replace('_', ' ', $item->status_laporan)) }}
              </span>
            </td>
            <td class="text-center">
              <a href="{{ route('riwayat.show', $item) }}" class="btn btn-sm btn-outline-primary">
                Detail Riwayat
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">Tidak ditemukan riwayat kegiatan pada filter ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

