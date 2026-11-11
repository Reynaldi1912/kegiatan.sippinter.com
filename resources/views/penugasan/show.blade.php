@extends('layouts.app')

@section('title', 'Detail Penugasan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <a href="{{ route('penugasan.index') }}" class="text-decoration-none small fw-semibold text-primary">
      <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Penugasan
    </a>
    <h4 class="fw-bold mb-0 text-dark mt-1">
      Detail Penugasan: {{ $penugasan->nomor_surat_tugas }}
    </h4>
  </div>

  <!-- Tindakan: Mulai Pelaksanaan, Ubah Instruksi, Laporkan Hasil (PRD 3.5) -->
  <div class="d-flex flex-wrap gap-2">
    @if($penugasan->status === 'belum_dikerjakan')
      <form method="POST" action="{{ route('penugasan.mulai', $penugasan) }}">
        @csrf
        <button type="submit" class="btn btn-info text-white fw-semibold">
          <i class="bx bx-play-circle me-1"></i> Mulai Pelaksanaan
        </button>
      </form>
    @endif

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPenugasan">
      <i class="bx bx-edit me-1"></i> Ubah Instruksi / Penugasan
    </button>

    <a href="{{ route('pelaksanaan.create', ['penugasan_id' => $penugasan->id]) }}" class="btn btn-primary">
      <i class="bx bx-upload me-1"></i> Laporkan Hasil Pelaksanaan
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <!-- A. Informasi Kegiatan (PRD 3.5.A) -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h6 class="mb-0 fw-bold text-dark">A. Informasi Kegiatan</h6>
        <span class="badge badge-soft-primary">{{ $penugasan->kegiatan->kode_kegiatan ?? '-' }}</span>
      </div>
      <div class="card-body">
        <h5 class="fw-bold text-dark mb-2">{{ $penugasan->kegiatan->nama_kegiatan ?? '-' }}</h5>
        <p class="text-muted mb-3">{{ $penugasan->kegiatan->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</p>

        <div class="row g-3">
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block">Program</small>
            <div class="fw-bold text-dark">{{ $penugasan->kegiatan->program ?? '-' }}</div>
          </div>
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block">Sumber Pendanaan</small>
            <div class="fw-bold text-dark">{{ $penugasan->kegiatan->sumber_dana ?? '-' }}</div>
          </div>
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block">Tanggal Kegiatan</small>
            <div class="fw-bold text-dark">{{ $penugasan->tanggal_mulai->translatedFormat('d M Y') }}</div>
          </div>
          <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block">Lokasi</small>
            <div class="fw-bold text-dark">{{ $penugasan->kegiatan->lokasi ?? '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- B. Informasi Penugasan (PRD 3.5.B) -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h6 class="mb-0 fw-bold text-dark">B. Informasi Penugasan & Instruksi Pekerjaan</h6>
        <span class="badge badge-soft-warning">Prioritas: {{ strtoupper($penugasan->prioritas) }}</span>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <small class="text-muted d-block">Pemberi Tugas</small>
            <div class="fw-bold text-dark">{{ $penugasan->pemberiTugas->name ?? '-' }}</div>
            <small class="text-muted">{{ $penugasan->pemberiTugas->jabatan ?? '' }}</small>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Penanggung Jawab Pelaksana</small>
            <div class="fw-bold text-dark">{{ $penugasan->penanggungJawab->name ?? '-' }}</div>
            <small class="text-muted">{{ $penugasan->penanggungJawab->jabatan ?? '' }}</small>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Tenggat Waktu</small>
            <div class="fw-bold text-danger">{{ $penugasan->tenggat_waktu->translatedFormat('d F Y') }}</div>
            <small class="text-muted">Sumber: {{ $penugasan->sumber_kegiatan }}</small>
          </div>
        </div>

        <div class="p-3 rounded-3 border bg-light mb-3">
          <small class="text-muted fw-bold text-uppercase d-block mb-1">Instruksi Pekerjaan</small>
          <div class="text-dark">{{ $penugasan->instruksi_pekerjaan }}</div>
        </div>

        <small class="text-muted fw-bold text-uppercase d-block mb-2">Anggota Pelaksana</small>
        <div class="d-flex flex-wrap gap-2">
          @foreach($penugasan->anggotaPelaksana as $anggota)
            <span class="badge badge-soft-primary px-3 py-2">
              <i class="bx bx-user me-1"></i> {{ $anggota->name }} ({{ $anggota->pivot->peran }})
            </span>
          @endforeach
        </div>
      </div>
    </div>

    <!-- E. Dokumentasi & Laporan Pelaksanaan (PRD 3.5.E) -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h6 class="mb-0 fw-bold text-dark">E. Dokumentasi & Riwayat Laporan Pelaksanaan</h6>
        <a href="{{ route('pelaksanaan.create', ['penugasan_id' => $penugasan->id]) }}" class="btn btn-sm btn-primary">
          + Buat Laporan Pelaksanaan
        </a>
      </div>
      <div class="card-body">
        @forelse($penugasan->pelaksanaans as $lapor)
          <div class="p-3 rounded-3 border mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <div>
                <span class="badge badge-soft-primary me-1">{{ $lapor->kode_laporan }}</span>
                <span class="badge badge-soft-info">{{ strtoupper($lapor->sumber_input) }}</span>
              </div>
              <span class="badge {{ $lapor->status_laporan === 'disetujui' ? 'badge-soft-success' : ($lapor->status_laporan === 'perlu_perbaikan' ? 'badge-soft-danger' : 'badge-soft-warning') }}">
                STATUS LAPORAN: {{ strtoupper(str_replace('_', ' ', $lapor->status_laporan)) }}
              </span>
            </div>
            <p class="mb-2 text-dark">{{ $lapor->uraian_hasil }}</p>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted">
              <span>Realisasi: <strong>{{ $lapor->jumlah_realisasi }} / {{ $lapor->jumlah_target }} {{ $lapor->satuan }}</strong> • Pelapor: {{ $lapor->pelapor->name ?? '-' }}</span>
              <a href="{{ route('pelaksanaan.show', $lapor) }}" class="btn btn-sm btn-outline-primary">
                Lihat Detail & Verifikasi Laporan
              </a>
            </div>
          </div>
        @empty
          <p class="text-muted mb-0 small">Belum ada laporan pelaksanaan atau dokumentasi yang diunggah untuk penugasan ini.</p>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- C. Target Pekerjaan (PRD 3.5.C) -->
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">C. Target Pekerjaan</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted d-block">Target Kegiatan & Satuan</small>
          <h4 class="fw-bold text-primary mb-0">{{ $penugasan->target_pekerjaan }} {{ $penugasan->satuan }}</h4>
        </div>
        <div>
          <small class="text-muted d-block">Indikator Keberhasilan</small>
          <div class="fw-semibold text-dark">{{ $penugasan->indikator_keberhasilan ?: 'Target kegiatan tercapai tepat waktu dan sesuai standar pelayanan.' }}</div>
        </div>
      </div>
    </div>

    <!-- D. Tupoksi Terkait (PRD 3.5.D) -->
    <div class="card">
      <div class="card-header border-bottom">
        <h6 class="mb-0 fw-bold text-dark">D. Tupoksi Terkait</h6>
      </div>
      <div class="card-body">
        @if($penugasan->tupoksi)
          <span class="badge badge-soft-purple mb-2">{{ $penugasan->tupoksi->kode_tupoksi }}</span>
          <h6 class="fw-bold text-dark mb-2">{{ $penugasan->tupoksi->nama_tupoksi }}</h6>
          <p class="text-muted small mb-3">{{ $penugasan->tupoksi->uraian_tugas }}</p>
          <div class="p-3 rounded-3 bg-light border mb-3">
            <div class="d-flex justify-content-between small mb-1">
              <span>Target Tupoksi: <strong>{{ $penugasan->tupoksi->target }} {{ $penugasan->tupoksi->satuan }}</strong></span>
              <span class="fw-bold text-primary">{{ $penugasan->tupoksi->persentase_capaian }}%</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-primary" style="width: {{ $penugasan->tupoksi->persentase_capaian }}%"></div>
            </div>
            <small class="text-muted d-block mt-1">Realisasi Saat Ini: {{ $penugasan->tupoksi->realisasi }} {{ $penugasan->tupoksi->satuan }}</small>
          </div>
          <a href="{{ route('tupoksi.show', $penugasan->tupoksi) }}" class="btn btn-sm btn-outline-primary w-100">
            Lihat Detail Capaian Tupoksi
          </a>
        @else
          <p class="text-muted small mb-0">Penugasan ini belum dihubungkan dengan item Tupoksi spesifik.</p>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Penugasan / Instruksi (Otomatis Kirim Notifikasi Perubahan Instruksi PRD 3.9) -->
<div class="modal fade" id="modalEditPenugasan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('penugasan.update', $penugasan) }}">
        @csrf
        @method('PUT')
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Ubah Instruksi & Detail Penugasan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Penanggung Jawab</label>
              <select name="penanggung_jawab_id" class="form-select" required>
                @foreach($pegawais as $p)
                  <option value="{{ $p->id }}" {{ $penugasan->penanggung_jawab_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tupoksi Terkait</label>
              <select name="tupoksi_id" class="form-select">
                <option value="">-- Tidak Ada --</option>
                @foreach($tupoksis as $tup)
                  <option value="{{ $tup->id }}" {{ $penugasan->tupoksi_id === $tup->id ? 'selected' : '' }}>
                    {{ $tup->kode_tupoksi }} - {{ $tup->nama_tupoksi }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Anggota Pelaksana</label>
              @php $selectedAnggota = $penugasan->anggotaPelaksana->pluck('id')->all(); @endphp
              <select name="anggota_ids[]" class="form-select select2" multiple>
                @foreach($pegawais as $p)
                  <option value="{{ $p->id }}" {{ in_array($p->id, $selectedAnggota) ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Kegiatan</label>
              <input type="text" name="sumber_kegiatan" class="form-control" value="{{ $penugasan->sumber_kegiatan }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Prioritas</label>
              <select name="prioritas" class="form-select" required>
                @foreach(['rendah', 'sedang', 'tinggi', 'mendesak'] as $pr)
                  <option value="{{ $pr }}" {{ $penugasan->prioritas === $pr ? 'selected' : '' }}>{{ ucfirst($pr) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status Penugasan</label>
              <select name="status" class="form-select" required>
                @foreach(['belum_dikerjakan', 'sedang_berjalan', 'menunggu_verifikasi', 'perlu_perbaikan', 'selesai'] as $st)
                  <option value="{{ $st }}" {{ $penugasan->status === $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target Pekerjaan</label>
              <input type="number" name="target_pekerjaan" class="form-control" value="{{ $penugasan->target_pekerjaan }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Satuan</label>
              <input type="text" name="satuan" class="form-control" value="{{ $penugasan->satuan }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tenggat Waktu</label>
              <input type="date" name="tenggat_waktu" class="form-control" value="{{ $penugasan->tenggat_waktu->format('Y-m-d') }}" required>
              <input type="hidden" name="tanggal_mulai" value="{{ $penugasan->tanggal_mulai->format('Y-m-d') }}">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Instruksi Pekerjaan</label>
              <textarea name="instruksi_pekerjaan" rows="3" class="form-control" required>{{ $penugasan->instruksi_pekerjaan }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Indikator Keberhasilan</label>
              <input type="text" name="indikator_keberhasilan" class="form-control" value="{{ $penugasan->indikator_keberhasilan }}">
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
@endsection

