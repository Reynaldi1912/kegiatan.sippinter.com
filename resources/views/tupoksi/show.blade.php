@extends('layouts.app')

@section('title', 'Detail Capaian Tupoksi')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <a href="{{ route('tupoksi.index') }}" class="text-decoration-none small fw-semibold text-primary">
      <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Tupoksi
    </a>
    <h4 class="fw-bold mb-0 text-dark mt-1">{{ $tupoksi->nama_tupoksi }}</h4>
  </div>
  <span class="badge badge-soft-purple px-3 py-2">{{ $tupoksi->kode_tupoksi }} • {{ $tupoksi->periode }}</span>
</div>

<!-- Halaman Detail Tupoksi (PRD 3.7) -->
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">Informasi & Target Tupoksi</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted d-block">Pegawai Pelaksana</small>
          <div class="fw-bold text-dark">{{ $tupoksi->user->name ?? '-' }}</div>
          <small class="text-muted">{{ $tupoksi->user->jabatan ?? '' }} • {{ $tupoksi->user->unit_program ?? '' }}</small>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Uraian Tugas</small>
          <p class="text-dark small mb-0">{{ $tupoksi->uraian_tugas }}</p>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Indikator Keberhasilan</small>
          <div class="fw-semibold text-dark small">{{ $tupoksi->indikator_keberhasilan ?: '-' }}</div>
        </div>

        <div class="p-3 rounded-3 bg-light border">
          <div class="d-flex justify-content-between mb-1">
            <span class="small text-muted">Target Pekerjaan:</span>
            <strong class="text-dark">{{ $tupoksi->target }} {{ $tupoksi->satuan }}</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="small text-muted">Realisasi Terverifikasi:</span>
            <strong class="text-success">{{ $tupoksi->realisasi }} {{ $tupoksi->satuan }}</strong>
          </div>
          <div class="progress mb-2" style="height: 10px;">
            <div class="progress-bar bg-primary" style="width: {{ $tupoksi->persentase_capaian }}%"></div>
          </div>
          <div class="text-center fw-bold text-primary">{{ $tupoksi->persentase_capaian }}% Tercapai</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- Daftar Penugasan & Kegiatan Terkait (PRD 3.7) -->
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">Kegiatan RPK & Daftar Penugasan Terkait</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Nomor ST & Kegiatan Terkait</th>
              <th>Tanggal</th>
              <th>Target Penugasan</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tupoksi->penugasans as $p)
              <tr>
                <td>
                  <span class="badge badge-soft-primary mb-1">{{ $p->nomor_surat_tugas }}</span>
                  <div class="fw-bold text-dark">{{ $p->kegiatan->nama_kegiatan ?? '-' }}</div>
                </td>
                <td>{{ $p->tanggal_mulai->translatedFormat('d M Y') }}</td>
                <td>{{ $p->target_pekerjaan }} {{ $p->satuan }}</td>
                <td>
                  <span class="badge badge-soft-info">{{ strtoupper(str_replace('_', ' ', $p->status)) }}</span>
                </td>
                <td class="text-center">
                  <a href="{{ route('penugasan.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">Belum ada penugasan terkait Tupoksi ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Hasil Pelaksanaan & Riwayat Capaian (PRD 3.7) -->
    <div class="card">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">Hasil Pelaksanaan & Riwayat Capaian</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Kode Laporan</th>
              <th>Tanggal Pelaksanaan</th>
              <th>Uraian Hasil</th>
              <th>Realisasi</th>
              <th>Status Verifikasi</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tupoksi->pelaksanaans as $lapor)
              <tr>
                <td>
                  <span class="badge badge-soft-primary">{{ $lapor->kode_laporan }}</span>
                </td>
                <td>{{ $lapor->tanggal_selesai->translatedFormat('d M Y') }}</td>
                <td>{{ Str::limit($lapor->uraian_hasil, 70) }}</td>
                <td>
                  <strong class="text-success">+{{ $lapor->jumlah_realisasi }} {{ $lapor->satuan }}</strong>
                </td>
                <td>
                  <span class="badge {{ $lapor->status_laporan === 'disetujui' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                    {{ strtoupper(str_replace('_', ' ', $lapor->status_laporan)) }}
                  </span>
                </td>
                <td class="text-center">
                  <a href="{{ route('pelaksanaan.show', $lapor) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat capaian pelaksanaan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

