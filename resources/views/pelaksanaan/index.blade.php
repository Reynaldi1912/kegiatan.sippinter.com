@extends('layouts.app')

@section('title', 'Pelaksanaan & Verifikasi Laporan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-check-shield text-primary me-1"></i> Pelaksanaan Kegiatan & Verifikasi Laporan
    </h4>
    <p class="text-muted mb-0 small">
      Monitoring laporan hasil pelaksanaan kegiatan dari pegawai dan proses verifikasi ke capaian Tupoksi
    </p>
  </div>
  <a href="{{ route('pelaksanaan.create') }}" class="btn btn-primary">
    <i class="bx bx-plus-circle me-1"></i> Buat Laporan Pelaksanaan
  </a>
</div>

<div class="card mb-4">
  <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="nav-pills-custom flex-wrap">
      <a href="{{ route('pelaksanaan.index') }}"
         class="nav-link text-decoration-none {{ $statusLaporan === '' ? 'active' : '' }}">
        Semua Laporan ({{ $ringkasanLaporan['semua'] }})
      </a>
      <a href="{{ route('pelaksanaan.index', ['status_laporan' => 'dikirim']) }}"
         class="nav-link text-decoration-none {{ $statusLaporan === 'dikirim' ? 'active' : '' }}">
        Menunggu Verifikasi ({{ $ringkasanLaporan['dikirim'] }})
      </a>
      <a href="{{ route('pelaksanaan.index', ['status_laporan' => 'perlu_perbaikan']) }}"
         class="nav-link text-decoration-none {{ $statusLaporan === 'perlu_perbaikan' ? 'active' : '' }}">
        Perlu Perbaikan ({{ $ringkasanLaporan['perlu_perbaikan'] }})
      </a>
      <a href="{{ route('pelaksanaan.index', ['status_laporan' => 'disetujui']) }}"
         class="nav-link text-decoration-none {{ $statusLaporan === 'disetujui' ? 'active' : '' }}">
        Disetujui ({{ $ringkasanLaporan['disetujui'] }})
      </a>
      <a href="{{ route('pelaksanaan.index', ['status_laporan' => 'draft']) }}"
         class="nav-link text-decoration-none {{ $statusLaporan === 'draft' ? 'active' : '' }}">
        Draft ({{ $ringkasanLaporan['draft'] }})
      </a>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Kode Laporan & Kegiatan</th>
          <th>Pelapor & Tanggal</th>
          <th>Realisasi Target</th>
          <th>Tupoksi Terkait</th>
          <th>Dokumentasi & Sumber</th>
          <th>Status Laporan</th>
          <th class="text-center">Tindakan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pelaksanaans as $item)
          <tr>
            <td>
              <span class="badge badge-soft-primary mb-1">{{ $item->kode_laporan }}</span>
              <div class="fw-bold text-dark">{{ $item->penugasan->kegiatan->nama_kegiatan ?? '-' }}</div>
              <small class="text-muted">ST: {{ $item->penugasan->nomor_surat_tugas ?? '-' }}</small>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $item->pelapor->name ?? '-' }}</div>
              <small class="text-muted">{{ $item->tanggal_mulai->translatedFormat('d M Y') }} - {{ $item->tanggal_selesai->translatedFormat('d M Y') }}</small>
            </td>
            <td>
              <div class="fw-bold text-primary">{{ $item->jumlah_realisasi }} / {{ $item->jumlah_target }} {{ $item->satuan }}</div>
              <small class="text-muted">{{ Str::limit($item->uraian_hasil, 60) }}</small>
            </td>
            <td>
              @if($item->tupoksi)
                <span class="badge badge-soft-purple">{{ $item->tupoksi->kode_tupoksi }}</span>
                <small class="d-block text-muted text-truncate" style="max-width: 180px;">{{ $item->tupoksi->nama_tupoksi }}</small>
              @else
                <span class="text-muted small">-</span>
              @endif
            </td>
            <td>
              <span class="badge badge-soft-info">{{ $item->dokumentasis->count() }} Lampiran</span>
              <small class="d-block text-muted mt-1">Via: {{ strtoupper($item->sumber_input) }}</small>
            </td>
            <td>
              <span class="badge {{ $item->status_laporan === 'disetujui' ? 'badge-soft-success' : ($item->status_laporan === 'perlu_perbaikan' ? 'badge-soft-danger' : ($item->status_laporan === 'draft' ? 'badge-soft-secondary' : 'badge-soft-warning')) }}">
                {{ strtoupper(str_replace('_', ' ', $item->status_laporan)) }}
              </span>
            </td>
            <td class="text-center">
              <a href="{{ route('pelaksanaan.show', $item) }}" class="btn btn-sm btn-primary">
                Review & Verifikasi
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">Belum ada laporan pelaksanaan kegiatan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

