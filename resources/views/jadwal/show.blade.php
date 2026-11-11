@extends('layouts.app')

@section('title', 'Detail Jadwal Kegiatan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <a href="{{ route('jadwal.index') }}" class="text-decoration-none small fw-semibold text-primary">
      <i class="bx bx-arrow-back me-1"></i> Kembali ke Jadwal Kegiatan
    </a>
    <h4 class="fw-bold mb-0 text-dark mt-1">{{ $kegiatan->nama_kegiatan }}</h4>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUbahJadwal">
      <i class="bx bx-edit me-1"></i> Ubah Jadwal / Lokasi
    </button>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBuatPenugasan">
      <i class="bx bx-user-plus me-1"></i> Buat Penugasan dari Jadwal Ini
    </button>
  </div>
</div>

<!-- Halaman Detail Jadwal (PRD 3.4) -->
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <div>
          <span class="badge badge-soft-primary me-1">{{ $kegiatan->kode_kegiatan }}</span>
          <span class="badge badge-soft-info">{{ $kegiatan->jenis_dokumen }} • {{ $kegiatan->sumber_dana }}</span>
        </div>
        <span class="badge {{ $kegiatan->status === 'selesai' ? 'badge-soft-success' : ($kegiatan->status === 'berjalan' ? 'badge-soft-info' : 'badge-soft-warning') }}">
          STATUS PELAKSANAAN: {{ strtoupper($kegiatan->status) }}
        </span>
      </div>
      <div class="card-body">
        <h6 class="fw-bold text-uppercase text-muted small mb-2">Uraian Kegiatan</h6>
        <p class="text-dark mb-4">{{ $kegiatan->deskripsi ?: 'Belum ada uraian rincian kegiatan.' }}</p>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-light">
              <small class="text-muted d-block">Program & Sumber Kegiatan</small>
              <div class="fw-bold text-dark">{{ $kegiatan->program }} ({{ $kegiatan->sumber_kegiatan }})</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-light">
              <small class="text-muted d-block">Lokasi Pelaksanaan</small>
              <div class="fw-bold text-dark"><i class="bx bx-map text-danger me-1"></i> {{ $kegiatan->lokasi }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-light">
              <small class="text-muted d-block">Jadwal & Waktu</small>
              <div class="fw-bold text-dark">
                {{ $kegiatan->tanggal_mulai->translatedFormat('d F Y') }}
                @if($kegiatan->tanggal_selesai->gt($kegiatan->tanggal_mulai))
                  - {{ $kegiatan->tanggal_selesai->translatedFormat('d F Y') }}
                @endif
                <span class="d-block small text-primary">{{ $kegiatan->waktu_mulai }} - {{ $kegiatan->waktu_selesai }} WIB</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 border bg-light">
              <small class="text-muted d-block">Target Kegiatan & Indikator</small>
              <div class="fw-bold text-dark">{{ $kegiatan->target_kegiatan }} {{ $kegiatan->satuan_target }}</div>
              <small class="text-muted">{{ $kegiatan->indikator_keberhasilan ?: '-' }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Daftar Penugasan Terkait Jadwal Ini -->
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="mb-0 fw-bold text-dark">Daftar Penugasan & Laporan pada Kegiatan Ini</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Nomor Surat Tugas</th>
              <th>Penanggung Jawab</th>
              <th>Anggota Pelaksana</th>
              <th>Status Penugasan</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kegiatan->penugasans as $penugasan)
              <tr>
                <td>
                  <div class="fw-bold text-dark">{{ $penugasan->nomor_surat_tugas }}</div>
                  <small class="text-muted">Tenggat: {{ $penugasan->tenggat_waktu->translatedFormat('d M Y') }}</small>
                </td>
                <td>{{ $penugasan->penanggungJawab->name ?? '-' }}</td>
                <td>
                  @foreach($penugasan->anggotaPelaksana as $agt)
                    <span class="badge badge-soft-secondary mb-1">{{ $agt->name }}</span>
                  @endforeach
                </td>
                <td>
                  <span class="badge badge-soft-primary">{{ strtoupper(str_replace('_', ' ', $penugasan->status)) }}</span>
                </td>
                <td class="text-center">
                  <a href="{{ route('penugasan.show', $penugasan) }}" class="btn btn-sm btn-outline-primary">Detail Penugasan</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">Belum ada surat tugas pada jadwal kegiatan ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Penanggung Jawab & Anggota Pelaksana (PRD 3.4) -->
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">Penanggung Jawab & Anggota Pelaksana</h6>
      </div>
      <div class="card-body">
        <div class="mb-3 pb-3 border-bottom">
          <small class="text-muted d-block mb-1">Penanggung Jawab Kegiatan</small>
          <div class="d-flex align-items-center gap-2">
            <div class="badge badge-soft-primary p-2 rounded-3"><i class="bx bx-user-check fs-5"></i></div>
            <div>
              <div class="fw-bold text-dark">{{ $kegiatan->penanggungJawab->name ?? '-' }}</div>
              <small class="text-muted">{{ $kegiatan->penanggungJawab->jabatan ?? '' }}</small>
            </div>
          </div>
        </div>

        <small class="text-muted d-block mb-2">Anggota Pelaksana Terdaftar</small>
        @php
          $semuaAnggota = $kegiatan->penugasans->flatMap(fn ($p) => $p->anggotaPelaksana)->unique('id');
        @endphp
        @forelse($semuaAnggota as $anggota)
          <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
            <div>
              <div class="fw-semibold text-dark small">{{ $anggota->name }}</div>
              <small class="text-muted">{{ $anggota->jabatan }}</small>
            </div>
            <span class="badge badge-soft-info">{{ $anggota->pivot->peran ?? 'Pelaksana' }}</span>
          </div>
        @empty
          <p class="text-muted small mb-0">Belum ada anggota pelaksana yang ditugaskan.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Modal Ubah Jadwal & Lokasi (Otomatis Kirim Notifikasi Perubahan Jadwal/Lokasi PRD 3.9) -->
<div class="modal fade" id="modalUbahJadwal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('kegiatan.update', $kegiatan) }}">
        @csrf
        @method('PUT')
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Ubah Jadwal & Informasi Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info small py-2">
            <i class="bx bx-bell me-1"></i> Perubahan tanggal/waktu atau lokasi akan otomatis mengirimkan <strong>Notifikasi Perubahan Jadwal / Lokasi</strong> kepada seluruh pegawai pelaksana.
          </div>
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nama Kegiatan</label>
              <input type="text" name="nama_kegiatan" class="form-control" value="{{ $kegiatan->nama_kegiatan }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Jenis Dokumen</label>
              <select name="jenis_dokumen" class="form-select" required>
                @foreach(['RPK', 'RUK', 'RBA'] as $doc)
                  <option value="{{ $doc }}" {{ $kegiatan->jenis_dokumen === $doc ? 'selected' : '' }}>{{ $doc }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Program</label>
              <input type="text" name="program" class="form-control" value="{{ $kegiatan->program }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Dana</label>
              <input type="text" name="sumber_dana" class="form-control" value="{{ $kegiatan->sumber_dana }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Kegiatan</label>
              <input type="text" name="sumber_kegiatan" class="form-control" value="{{ $kegiatan->sumber_kegiatan }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" value="{{ $kegiatan->tanggal_mulai->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" class="form-control" value="{{ $kegiatan->tanggal_selesai->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Waktu Mulai</label>
              <input type="time" name="waktu_mulai" class="form-control" value="{{ $kegiatan->waktu_mulai }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Waktu Selesai</label>
              <input type="time" name="waktu_selesai" class="form-control" value="{{ $kegiatan->waktu_selesai }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Lokasi Pelaksanaan</label>
              <input type="text" name="lokasi" class="form-control" value="{{ $kegiatan->lokasi }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Penanggung Jawab</label>
              <select name="penanggung_jawab_id" class="form-select" required>
                @foreach($pegawais as $pegawai)
                  <option value="{{ $pegawai->id }}" {{ $kegiatan->penanggung_jawab_id === $pegawai->id ? 'selected' : '' }}>
                    {{ $pegawai->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target Kegiatan</label>
              <input type="number" name="target_kegiatan" class="form-control" value="{{ $kegiatan->target_kegiatan }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Satuan Target</label>
              <input type="text" name="satuan_target" class="form-control" value="{{ $kegiatan->satuan_target }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status Kegiatan</label>
              <select name="status" class="form-select" required>
                @foreach(['terjadwal', 'berjalan', 'selesai', 'dibatalkan'] as $st)
                  <option value="{{ $st }}" {{ $kegiatan->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
              </select>
            </div>
            <input type="hidden" name="status_keuangan" value="{{ $kegiatan->status_keuangan }}">
            <input type="hidden" name="pagu_anggaran" value="{{ $kegiatan->pagu_anggaran }}">
            <input type="hidden" name="realisasi_anggaran" value="{{ $kegiatan->realisasi_anggaran }}">
            <div class="col-12">
              <label class="form-label fw-semibold">Uraian Kegiatan</label>
              <textarea name="deskripsi" rows="2" class="form-control">{{ $kegiatan->deskripsi }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Indikator Keberhasilan</label>
              <input type="text" name="indikator_keberhasilan" class="form-control" value="{{ $kegiatan->indikator_keberhasilan }}">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Buat Penugasan dari Jadwal Ini -->
<div class="modal fade" id="modalBuatPenugasan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('penugasan.store') }}">
        @csrf
        <input type="hidden" name="kegiatan_id" value="{{ $kegiatan->id }}">
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Buat Penugasan Pegawai: {{ $kegiatan->nama_kegiatan }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Penanggung Jawab Pelaksana</label>
              <select name="penanggung_jawab_id" class="form-select" required>
                @foreach($pegawais as $pegawai)
                  <option value="{{ $pegawai->id }}" {{ $kegiatan->penanggung_jawab_id === $pegawai->id ? 'selected' : '' }}>
                    {{ $pegawai->name }} ({{ $pegawai->jabatan }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Hubungkan dengan Tupoksi Pegawai</label>
              <select name="tupoksi_id" class="form-select">
                <option value="">-- Pilih Tupoksi Terkait --</option>
                @foreach($tupoksis as $tupoksi)
                  <option value="{{ $tupoksi->id }}">{{ $tupoksi->kode_tupoksi }} - {{ $tupoksi->nama_tupoksi }} ({{ $tupoksi->user->name ?? '' }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Anggota Pelaksana (Bisa pilih lebih dari satu)</label>
              <select name="anggota_ids[]" class="form-select select2" multiple>
                @foreach($pegawais as $pegawai)
                  <option value="{{ $pegawai->id }}">{{ $pegawai->name }} - {{ $pegawai->jabatan }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Penugasan</label>
              <input type="text" name="sumber_kegiatan" class="form-control" value="{{ $kegiatan->sumber_kegiatan }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Prioritas</label>
              <select name="prioritas" class="form-select" required>
                <option value="sedang">Sedang</option>
                <option value="tinggi">Tinggi</option>
                <option value="mendesak">Mendesak</option>
                <option value="rendah">Rendah</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target & Satuan</label>
              <div class="input-group">
                <input type="number" name="target_pekerjaan" class="form-control" value="{{ $kegiatan->target_kegiatan }}" required>
                <input type="text" name="satuan" class="form-control" value="{{ $kegiatan->satuan_target }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" value="{{ $kegiatan->tanggal_mulai->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tenggat Waktu Pelaporan</label>
              <input type="date" name="tenggat_waktu" class="form-control" value="{{ $kegiatan->tanggal_selesai->format('Y-m-d') }}" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Instruksi Pekerjaan <span class="text-danger">*</span></label>
              <textarea name="instruksi_pekerjaan" rows="3" class="form-control" required placeholder="Tuliskan instruksi pelaksanaan dan dokumen yang wajib dilaporkan..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Indikator Keberhasilan</label>
              <input type="text" name="indikator_keberhasilan" class="form-control" value="{{ $kegiatan->indikator_keberhasilan }}">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Terbitkan Penugasan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

