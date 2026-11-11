@extends('layouts.app')

@section('title', 'Sinkronisasi Data Web & Mobile')

@section('content')
<!-- Halaman Kebutuhan Sinkronisasi Data Web <-> Mobile (PRD Bab 4) -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-sync text-primary me-1"></i> Pusat Sinkronisasi Data Web & Mobile Application (PRD Bab 4)
    </h4>
    <p class="text-muted mb-0 small">
      Monitoring pertukaran data dua arah antara Web Application dengan Mobile Application beserta proteksi anti-duplikasi laporan
    </p>
  </div>
  <a href="{{ route('api.mobile.bootstrap') }}" target="_blank" class="btn btn-outline-primary">
    <i class="bx bx-code-curly me-1"></i> Uji Payload JSON Web -> Mobile
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Akun Pegawai Tersinkron</small>
        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $syncStats['total_akun_aktif'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Penugasan Dikirim ke Mobile</small>
        <h3 class="fw-bold text-primary mb-0 mt-1">{{ $syncStats['total_penugasan_aktif'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Laporan Tersinkronisasi</small>
        <h3 class="fw-bold text-success mb-0 mt-1">{{ $syncStats['total_laporan_tersinkron'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Laporan Masuk via Mobile Sync</small>
        <h3 class="fw-bold text-info mb-0 mt-1">{{ $syncStats['laporan_mobile'] }}</h3>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- 4.1 Data dari Web ke Mobile -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">4.1 Aliran Data dari Web ke Mobile (`GET /api/mobile/bootstrap`)</h6>
      </div>
      <div class="card-body small">
        <ul class="mb-0">
          <li><strong>Data Akun & Profil Pegawai:</strong> NIP, jabatan, unit/program, token autentikasi.</li>
          <li><strong>Data Penugasan & Instruksi Pekerjaan:</strong> Nomor ST, prioritas, tenggat waktu, anggota pelaksana.</li>
          <li><strong>Data Jadwal & Kegiatan RPK:</strong> Lokasi, tanggal, waktu, target kegiatan.</li>
          <li><strong>Data Tupoksi:</strong> Target dan progres realisasi Tupoksi pegawai.</li>
          <li><strong>Notifikasi Real-time:</strong> Penugasan baru, perubahan jadwal/lokasi/instruksi, status verifikasi.</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- 4.2 & 4.3 Data dari Mobile ke Web -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">4.2 & 4.3 Aliran Data dari Mobile ke Web (`POST /api/mobile/pelaksanaan/sync`)</h6>
      </div>
      <div class="card-body small">
        <ul class="mb-0">
          <li><strong>Laporan Pelaksanaan:</strong> Status pelaksanaan, tanggal mulai & selesai, uraian hasil kegiatan.</li>
          <li><strong>Realisasi Target & Dokumentasi:</strong> Jumlah realisasi, lampiran foto/dokumen, catatan pegawai.</li>
          <li><strong>Anti-Duplikasi (`client_sync_id`):</strong> Mencegah laporan ganda saat koneksi internet tidak stabil dan pegawai mengirim ulang laporan.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header border-bottom">
    <h6 class="mb-0 fw-bold text-dark">Log Sinkronisasi Laporan Pelaksanaan</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Kode Laporan & Client Sync ID</th>
          <th>Kegiatan & Penugasan</th>
          <th>Pelapor</th>
          <th>Sumber Input</th>
          <th>Waktu Sinkron</th>
          <th>Status Sinkronisasi</th>
          <th class="text-center">Tindakan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pelaksanaanSyncs as $sync)
          <tr>
            <td>
              <div class="fw-bold text-dark">{{ $sync->kode_laporan }}</div>
              <code class="small text-muted">{{ $sync->client_sync_id }}</code>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $sync->penugasan->kegiatan->nama_kegiatan ?? '-' }}</div>
              <small class="text-muted">{{ $sync->penugasan->nomor_surat_tugas ?? '-' }}</small>
            </td>
            <td>{{ $sync->pelapor->name ?? '-' }}</td>
            <td>
              <span class="badge {{ $sync->sumber_input === 'mobile_sync' ? 'badge-soft-info' : 'badge-soft-primary' }}">
                {{ strtoupper($sync->sumber_input) }}
              </span>
            </td>
            <td>{{ $sync->synced_at ? $sync->synced_at->translatedFormat('d M Y H:i') : '-' }}</td>
            <td>
              <span class="badge {{ $sync->status_sinkronisasi === 'tersinkron' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                {{ strtoupper($sync->status_sinkronisasi) }}
              </span>
            </td>
            <td class="text-center">
              <form method="POST" action="{{ route('sinkronisasi.retry', $sync) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                  <i class="bx bx-refresh"></i> Sinkron Ulang
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

