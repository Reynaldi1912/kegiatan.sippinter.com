@extends('layouts.app')

@section('title', 'Perencanaan RUK / RPK / RBA')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-spreadsheet text-primary me-1"></i> Perencanaan Kegiatan (RUK, RPK, RBA) & Keuangan
    </h4>
    <p class="text-muted mb-0 small">
      Pengelolaan perencanaan anggaran kegiatan Puskesmas, sumber pendanaan BOK/BLUD, penagihan, dan pencairan pada Web Application (PRD Bab 8)
    </p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRpk">
    <i class="bx bx-plus me-1"></i> Tambah Kegiatan RUK / RPK / RBA
  </button>
</div>

<!-- Ringkasan Anggaran & Realisasi -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Total Kegiatan RUK/RPK/RBA</small>
        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $ringkasanRpk['total_kegiatan'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Total Pagu Anggaran</small>
        <h4 class="fw-bold text-primary mb-0 mt-1">Rp {{ number_format($ringkasanRpk['total_pagu'], 0, ',', '.') }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Realisasi Pembayaran</small>
        <h4 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($ringkasanRpk['total_realisasi'], 0, ',', '.') }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body p-3">
        <small class="text-muted fw-semibold">Kegiatan Selesai</small>
        <h3 class="fw-bold text-info mb-0 mt-1">{{ $ringkasanRpk['selesai'] }}</h3>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Kode & Nama Kegiatan</th>
          <th>Dokumen & Program</th>
          <th>Sumber Dana & Rekening</th>
          <th>Pagu & Realisasi</th>
          <th>Status Keuangan</th>
          <th>Jadwal & PJ</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($kegiatans as $keg)
          <tr>
            <td>
              <span class="badge badge-soft-primary mb-1">{{ $keg->kode_kegiatan }}</span>
              <div class="fw-bold text-dark">{{ $keg->nama_kegiatan }}</div>
              <small class="text-muted">Target: {{ $keg->target_kegiatan }} {{ $keg->satuan_target }}</small>
            </td>
            <td>
              <span class="badge badge-soft-purple">{{ $keg->jenis_dokumen }}</span>
              <div class="small fw-semibold text-dark mt-1">{{ $keg->program }}</div>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $keg->sumber_dana }}</div>
              <small class="text-muted">{{ $keg->kode_rekening ?: '-' }}</small>
            </td>
            <td>
              <div class="fw-bold text-dark">Rp {{ number_format((float) $keg->pagu_anggaran, 0, ',', '.') }}</div>
              <small class="text-success">Cair: Rp {{ number_format((float) $keg->realisasi_anggaran, 0, ',', '.') }}</small>
            </td>
            <td>
              <span class="badge {{ $keg->status_keuangan === 'dibayar' ? 'badge-soft-success' : ($keg->status_keuangan === 'proses_penagihan' ? 'badge-soft-warning' : 'badge-soft-secondary') }}">
                {{ strtoupper(str_replace('_', ' ', $keg->status_keuangan)) }}
              </span>
            </td>
            <td>
              <div class="small fw-semibold">{{ $keg->tanggal_mulai->translatedFormat('d M Y') }}</div>
              <small class="text-muted">{{ $keg->penanggungJawab->name ?? '-' }}</small>
            </td>
            <td class="text-center">
              <a href="{{ route('jadwal.show', $keg) }}" class="btn btn-sm btn-outline-primary">
                Detail & Penugasan
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">Belum ada data kegiatan RUK/RPK/RBA.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah RUK/RPK/RBA -->
<div class="modal fade" id="modalTambahRpk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('kegiatan.store') }}">
        @csrf
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Tambah Perencanaan Kegiatan (RUK / RPK / RBA)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
              <input type="text" name="nama_kegiatan" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Jenis Dokumen</label>
              <select name="jenis_dokumen" class="form-select" required>
                <option value="RPK">RPK</option>
                <option value="RUK">RUK</option>
                <option value="RBA">RBA</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Program Puskesmas</label>
              <input type="text" name="program" class="form-control" value="KIA & KB" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Dana</label>
              <select name="sumber_dana" class="form-select" required>
                <option value="BOK Puskesmas">BOK Puskesmas</option>
                <option value="BLUD">BLUD Puskesmas</option>
                <option value="JKN Kapitasi">JKN Kapitasi</option>
                <option value="APBD">APBD</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Kegiatan</label>
              <input type="text" name="sumber_kegiatan" class="form-control" value="RPK Tahunan" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Kode Rekening Belanja (COA)</label>
              <select name="kode_rekening" class="form-select">
                <option value="">-- Pilih Rekening Belanja --</option>
                @foreach($rekenings as $rek)
                  <option value="{{ $rek->kode_akun }}">{{ $rek->kode_akun }} — {{ $rek->nama_akun }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Pagu Anggaran (Rp)</label>
              <input type="number" name="pagu_anggaran" class="form-control" value="2500000" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Status Keuangan</label>
              <select name="status_keuangan" class="form-select">
                <option value="belum_ditagihkan">Belum Ditagihkan</option>
                <option value="proses_penagihan">Proses Penagihan</option>
                <option value="dibayar">Dibayar / Lunas</option>
              </select>
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
              <label class="form-label fw-semibold">Lokasi Pelaksanaan</label>
              <input type="text" name="lokasi" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Penanggung Jawab</label>
              <select name="penanggung_jawab_id" class="form-select" required>
                @foreach($pegawais as $p)
                  <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->jabatan }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target Kegiatan</label>
              <input type="number" name="target_kegiatan" class="form-control" value="30" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Satuan Target</label>
              <input type="text" name="satuan_target" class="form-control" value="Sasaran" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status Pelaksanaan</label>
              <select name="status" class="form-select" required>
                <option value="terjadwal">Terjadwal</option>
                <option value="berjalan">Sedang Berjalan</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Deskripsi Kegiatan</label>
              <textarea name="deskripsi" rows="2" class="form-control"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perencanaan Kegiatan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

