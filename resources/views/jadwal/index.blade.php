@extends('layouts.app')

@section('title', 'Jadwal Kegiatan')

@section('content')
@php
  $namaBulan = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
      5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
  ];

  $firstDayOfWeek = (int) $startOfMonth->format('N'); // 1 (Mon) to 7 (Sun)
  $daysInMonth = $startOfMonth->daysInMonth;
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-calendar text-primary me-1"></i> Jadwal Kegiatan Puskesmas
    </h4>
    <p class="text-muted mb-0 small">
      Menampilkan seluruh jadwal kegiatan Puskesmas dalam mode Kalender Bulanan dan Daftar Jadwal
    </p>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2">
    <!-- Mode Tampilan (PRD 3.4: 1. Kalender Bulanan, 2. Daftar Jadwal) -->
    <div class="nav-pills-custom">
      <a href="{{ route('jadwal.index', ['mode' => 'kalender', 'bulan' => $bulan, 'tahun' => $tahun, 'status' => $status]) }}"
         class="nav-link text-decoration-none {{ $mode === 'kalender' ? 'active' : '' }}">
        <i class="bx bx-calendar-alt me-1"></i> Kalender Bulanan
      </a>
      <a href="{{ route('jadwal.index', ['mode' => 'daftar', 'bulan' => $bulan, 'tahun' => $tahun, 'status' => $status]) }}"
         class="nav-link text-decoration-none {{ $mode === 'daftar' ? 'active' : '' }}">
        <i class="bx bx-list-ul me-1"></i> Daftar Jadwal
      </a>
    </div>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
      <i class="bx bx-plus me-1"></i> Tambah Jadwal
    </button>
  </div>
</div>

<!-- Filter Bulan, Tahun & Status (PRD 3.4) -->
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('jadwal.index') }}" class="row g-3 align-items-end">
      <input type="hidden" name="mode" value="{{ $mode }}">
      <div class="col-sm-4 col-md-3">
        <label class="form-label small fw-bold text-muted mb-1">Pilihan Bulan</label>
        <select name="bulan" class="form-select">
          @foreach($namaBulan as $num => $label)
            <option value="{{ $num }}" {{ $bulan === $num ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-sm-4 col-md-2">
        <label class="form-label small fw-bold text-muted mb-1">Pilihan Tahun</label>
        <select name="tahun" class="form-select">
          @for($y = now()->year - 1; $y <= now()->year + 2; $y++)
            <option value="{{ $y }}" {{ $tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>

      <div class="col-sm-4 col-md-3">
        <label class="form-label small fw-bold text-muted mb-1">Filter Status Kegiatan</label>
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <option value="terjadwal" {{ $status === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
          <option value="berjalan" {{ $status === 'berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
          <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
          <option value="dibatalkan" {{ $status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
      </div>

      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bx bx-filter-alt me-1"></i> Terapkan Filter
        </button>
        <a href="{{ route('jadwal.index', ['mode' => $mode]) }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

@if($mode === 'kalender')
  <!-- MODE 1: KALENDER BULANAN DENGAN PENANDA TANGGAL KEGIATAN (PRD 3.4) -->
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between border-bottom py-3">
      <h5 class="mb-0 fw-bold text-dark">
        <i class="bx bx-calendar-check text-primary me-1"></i> Kalender Kegiatan: {{ $namaBulan[$bulan] ?? '' }} {{ $tahun }}
      </h5>
      <div class="d-flex gap-2 small">
        <span class="badge badge-soft-warning">Terjadwal</span>
        <span class="badge badge-soft-info">Sedang Berjalan</span>
        <span class="badge badge-soft-success">Selesai</span>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-bordered mb-0" style="table-layout: fixed; min-width: 820px;">
          <thead>
            <tr class="text-center">
              <th>Senin</th>
              <th>Selasa</th>
              <th>Rabu</th>
              <th>Kamis</th>
              <th>Jumat</th>
              <th>Sabtu</th>
              <th>Minggu</th>
            </tr>
          </thead>
          <tbody>
            @php
              $dayCounter = 1;
              $totalCells = ceil(($firstDayOfWeek - 1 + $daysInMonth) / 7) * 7;
            @endphp
            @for($cell = 1; $cell <= $totalCells; $cell++)
              @if($cell % 7 === 1)
                <tr>
              @endif

              @if($cell < $firstDayOfWeek || $dayCounter > $daysInMonth)
                <td class="bg-light" style="height: 125px; vertical-align: top;"></td>
              @else
                @php
                  $dateStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $dayCounter);
                  $eventsOnDay = $jadwalPerTanggal[$dateStr] ?? [];
                  $isToday = $dateStr === now()->format('Y-m-d');
                @endphp
                <td style="height: 125px; vertical-align: top; padding: 0.55rem !important; {{ $isToday ? 'background: #eef2ff;' : '' }}">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold {{ $isToday ? 'badge bg-primary text-white rounded-pill px-2' : 'text-dark small' }}">
                      {{ $dayCounter }}
                    </span>
                    @if(count($eventsOnDay) > 0)
                      <span class="badge badge-soft-primary" style="font-size: 0.65rem;">{{ count($eventsOnDay) }} Kegiatan</span>
                    @endif
                  </div>

                  @foreach($eventsOnDay as $ev)
                    <a href="{{ route('jadwal.show', $ev) }}"
                       class="d-block p-1 mb-1 rounded-2 text-decoration-none border-start border-3 {{ $ev->status === 'selesai' ? 'badge-soft-success border-success' : ($ev->status === 'berjalan' ? 'badge-soft-info border-info' : 'badge-soft-warning border-warning') }}"
                       style="font-size: 0.72rem; line-height: 1.25;">
                      <div class="fw-bold text-truncate">{{ $ev->waktu_mulai }} • {{ $ev->nama_kegiatan }}</div>
                      <div class="text-truncate opacity-75"><i class="bx bx-map"></i> {{ $ev->lokasi }}</div>
                    </a>
                  @endforeach
                </td>
                @php $dayCounter++; @endphp
              @endif

              @if($cell % 7 === 0)
                </tr>
              @endif
            @endfor
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endif

<!-- MODE 2 / DAFTAR JADWAL KEGIATAN (PRD 3.4: Informasi Setiap Jadwal) -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between border-bottom py-3">
    <h5 class="mb-0 fw-bold text-dark">
      <i class="bx bx-list-check text-primary me-1"></i> Daftar Jadwal Kegiatan ({{ $namaBulan[$bulan] ?? '' }} {{ $tahun }})
    </h5>
    <span class="badge badge-soft-primary">{{ $jadwals->count() }} Jadwal Ditemukan</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Nama Kegiatan & Program</th>
          <th>Tanggal</th>
          <th>Waktu</th>
          <th>Lokasi</th>
          <th>Penanggung Jawab</th>
          <th>Status Kegiatan</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($jadwals as $jadwal)
          <tr>
            <td>
              <span class="badge badge-soft-primary mb-1">{{ $jadwal->kode_kegiatan }} • {{ $jadwal->program }}</span>
              <div class="fw-bold text-dark">{{ $jadwal->nama_kegiatan }}</div>
              <small class="text-muted">Target: {{ $jadwal->target_kegiatan }} {{ $jadwal->satuan_target }}</small>
            </td>
            <td>
              <div class="fw-semibold">{{ $jadwal->tanggal_mulai->translatedFormat('d M Y') }}</div>
              @if($jadwal->tanggal_selesai->gt($jadwal->tanggal_mulai))
                <small class="text-muted">s/d {{ $jadwal->tanggal_selesai->translatedFormat('d M Y') }}</small>
              @endif
            </td>
            <td>
              <span class="badge badge-soft-dark">{{ $jadwal->waktu_mulai }} - {{ $jadwal->waktu_selesai }} WIB</span>
            </td>
            <td>
              <i class="bx bx-map text-danger me-1"></i> {{ $jadwal->lokasi }}
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $jadwal->penanggungJawab->name ?? '-' }}</div>
              <small class="text-muted">{{ $jadwal->penanggungJawab->jabatan ?? '' }}</small>
            </td>
            <td>
              <span class="badge {{ $jadwal->status === 'selesai' ? 'badge-soft-success' : ($jadwal->status === 'berjalan' ? 'badge-soft-info' : 'badge-soft-warning') }}">
                {{ strtoupper($jadwal->status) }}
              </span>
            </td>
            <td class="text-center">
              <a href="{{ route('jadwal.show', $jadwal) }}" class="btn btn-sm btn-outline-primary">
                Detail Jadwal
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">
              Tidak ada jadwal kegiatan pada periode yang dipilih.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Jadwal Kegiatan -->
<div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('kegiatan.store') }}">
        @csrf
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Tambah Jadwal & Kegiatan Puskesmas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
              <input type="text" name="nama_kegiatan" class="form-control" required placeholder="Contoh: Pelayanan Posyandu & Kelas Ibu Hamil">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Jenis Dokumen</label>
              <select name="jenis_dokumen" class="form-select" required>
                <option value="RPK">RPK (Rencana Pelaksanaan Kegiatan)</option>
                <option value="RUK">RUK (Rencana Usulan Kegiatan)</option>
                <option value="RBA">RBA (Rencana Bisnis Anggaran)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Program / Unit <span class="text-danger">*</span></label>
              <input type="text" name="program" class="form-control" value="KIA & KB" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Pendanaan</label>
              <select name="sumber_dana" class="form-select" required>
                <option value="BOK Puskesmas">BOK Puskesmas</option>
                <option value="BLUD">BLUD Puskesmas</option>
                <option value="JKN Kapitasi">JKN Kapitasi</option>
                <option value="APBD">APBD</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Kegiatan</label>
              <input type="text" name="sumber_kegiatan" class="form-control" value="RPK Bulanan" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Waktu Mulai</label>
              <input type="time" name="waktu_mulai" class="form-control" value="08:00" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Waktu Selesai</label>
              <input type="time" name="waktu_selesai" class="form-control" value="12:00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Lokasi Pelaksanaan <span class="text-danger">*</span></label>
              <input type="text" name="lokasi" class="form-control" required placeholder="Contoh: Posyandu Melati Desa Sukamaju">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Penanggung Jawab <span class="text-danger">*</span></label>
              <select name="penanggung_jawab_id" class="form-select" required>
                @foreach($pegawais as $pegawai)
                  <option value="{{ $pegawai->id }}">{{ $pegawai->name }} ({{ $pegawai->jabatan }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target Kegiatan</label>
              <input type="number" name="target_kegiatan" class="form-control" value="30" min="1" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Satuan Target</label>
              <input type="text" name="satuan_target" class="form-control" value="Peserta / Sasaran" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status Awal</label>
              <select name="status" class="form-select" required>
                <option value="terjadwal">Terjadwal</option>
                <option value="berjalan">Sedang Berjalan</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Uraian Kegiatan</label>
              <textarea name="deskripsi" rows="2" class="form-control" placeholder="Jelaskan rincian kegiatan yang akan dilaksanakan..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Indikator Keberhasilan</label>
              <input type="text" name="indikator_keberhasilan" class="form-control" placeholder="Contoh: Cakupan pelayanan mencapai minimal 90% sasaran">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Jadwal Kegiatan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

