@extends('layouts.app')

@section('title', 'Capaian Tupoksi Pegawai')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-bullseye text-primary me-1"></i> Capaian & Master Tupoksi Pegawai
    </h4>
    <p class="text-muted mb-0 small">
      Menampilkan pencapaian pekerjaan pegawai berdasarkan Tupoksi dari kegiatan yang telah diverifikasi
    </p>
  </div>

  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTupoksi">
    <i class="bx bx-plus me-1"></i> Tambah Tupoksi Pegawai
  </button>
</div>

<!-- Ringkasan Statistik Tupoksi -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Total Item Tupoksi</small>
        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $ringkasanTupoksi['total_tupoksi'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Akumulasi Target</small>
        <h3 class="fw-bold text-primary mb-0 mt-1">{{ $ringkasanTupoksi['total_target'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Realisasi Terverifikasi</small>
        <h3 class="fw-bold text-success mb-0 mt-1">{{ $ringkasanTupoksi['total_realisasi'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Rata-rata Persentase Capaian</small>
        <h3 class="fw-bold mb-0 mt-1" style="color: #7c3aed;">{{ $ringkasanTupoksi['rata_capaian'] }}%</h3>
      </div>
    </div>
  </div>
</div>

<!-- Filter Pegawai & Periode (PRD 3.7) -->
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('tupoksi.index') }}" class="row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label small fw-bold text-muted mb-1">Filter Pegawai</label>
        <select name="user_id" class="form-select">
          <option value="">Semua Pegawai Puskesmas</option>
          @foreach($pegawais as $p)
            <option value="{{ $p->id }}" {{ (string) $userId === (string) $p->id ? 'selected' : '' }}>
              {{ $p->name }} — {{ $p->jabatan }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-bold text-muted mb-1">Periode Penilaian</label>
        <select name="periode" class="form-select">
          <option value="">Semua Periode</option>
          <option value="Tahun 2026" {{ $periode === 'Tahun 2026' ? 'selected' : '' }}>Tahun 2026</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('tupoksi.index') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Daftar Tupoksi (PRD 3.7: Daftar Tupoksi, Target, Realisasi, Persentase Capaian, Periode) -->
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Kode & Nama Tupoksi</th>
          <th>Pegawai & Unit</th>
          <th>Periode</th>
          <th>Target</th>
          <th>Realisasi</th>
          <th>Persentase Capaian</th>
          <th class="text-center">Detail</th>
        </tr>
      </thead>
      <tbody>
        @forelse($tupoksis as $tup)
          <tr>
            <td>
              <span class="badge badge-soft-purple mb-1">{{ $tup->kode_tupoksi }}</span>
              <div class="fw-bold text-dark">{{ $tup->nama_tupoksi }}</div>
              <small class="text-muted">{{ Str::limit($tup->uraian_tugas, 80) }}</small>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $tup->user->name ?? '-' }}</div>
              <small class="text-muted">{{ $tup->user->jabatan ?? '' }}</small>
            </td>
            <td>
              <span class="badge badge-soft-primary">{{ $tup->periode }}</span>
            </td>
            <td>
              <strong>{{ $tup->target }}</strong> {{ $tup->satuan }}
            </td>
            <td>
              <strong class="text-success">{{ $tup->realisasi }}</strong> {{ $tup->satuan }}
            </td>
            <td style="min-width: 180px;">
              <div class="d-flex justify-content-between small mb-1">
                <span class="fw-bold text-primary">{{ $tup->persentase_capaian }}%</span>
                <span class="text-muted">{{ $tup->penugasans->count() }} Penugasan</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar {{ $tup->persentase_capaian >= 80 ? 'bg-success' : ($tup->persentase_capaian >= 50 ? 'bg-primary' : 'bg-warning') }}"
                     style="width: {{ $tup->persentase_capaian }}%"></div>
              </div>
            </td>
            <td class="text-center">
              <a href="{{ route('tupoksi.show', $tup) }}" class="btn btn-sm btn-outline-primary">
                Lihat Detail
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">Belum ada data Tupoksi terdaftar.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Tupoksi -->
<div class="modal fade" id="modalTambahTupoksi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('tupoksi.store') }}">
        @csrf
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Tambah Target & Tupoksi Pegawai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Pegawai Pemilik Tupoksi <span class="text-danger">*</span></label>
              <select name="user_id" class="form-select" required>
                @foreach($pegawais as $p)
                  <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->jabatan }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Periode Penilaian</label>
              <input type="text" name="periode" class="form-control" value="Tahun 2026" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Nama Tupoksi <span class="text-danger">*</span></label>
              <input type="text" name="nama_tupoksi" class="form-control" required placeholder="Contoh: Pelayanan Pemeriksaan Kesehatan Anak Sekolah">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Uraian Tugas <span class="text-danger">*</span></label>
              <textarea name="uraian_tugas" rows="3" class="form-control" required placeholder="Tuliskan uraian tugas pokok dan fungsi..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Target Kuantitatif <span class="text-danger">*</span></label>
              <input type="number" name="target" class="form-control" value="100" min="1" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Satuan Target <span class="text-danger">*</span></label>
              <input type="text" name="satuan" class="form-control" value="Kegiatan / Sasaran" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Indikator Keberhasilan</label>
              <input type="text" name="indikator_keberhasilan" class="form-control" placeholder="Contoh: Cakupan pelayanan mencapai >= 90%">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Tupoksi</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

