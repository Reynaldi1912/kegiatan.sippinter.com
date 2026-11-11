@extends('layouts.app')

@section('title', 'Penugasan Pegawai')

@section('content')
@php
  $statusBadgeMap = [
      'belum_dikerjakan' => ['label' => 'Belum Dikerjakan', 'class' => 'badge-soft-secondary'],
      'sedang_berjalan' => ['label' => 'Sedang Berjalan', 'class' => 'badge-soft-info'],
      'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-soft-warning'],
      'perlu_perbaikan' => ['label' => 'Perlu Perbaikan', 'class' => 'badge-soft-danger'],
      'selesai' => ['label' => 'Selesai', 'class' => 'badge-soft-success'],
  ];

  $prioritasBadgeMap = [
      'rendah' => ['label' => 'Rendah', 'class' => 'badge-soft-secondary'],
      'sedang' => ['label' => 'Sedang', 'class' => 'badge-soft-primary'],
      'tinggi' => ['label' => 'Tinggi', 'class' => 'badge-soft-warning'],
      'mendesak' => ['label' => 'Mendesak', 'class' => 'badge-soft-danger'],
  ];
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-task text-primary me-1"></i> Manajemen Penugasan & Disposisi Kegiatan
    </h4>
    <p class="text-muted mb-0 small">
      Menampilkan seluruh pekerjaan dan surat tugas yang diberikan kepada pegawai Puskesmas
    </p>
  </div>

  <div class="d-flex gap-2">
    <a href="{{ route('pelaksanaan.create') }}" class="btn btn-outline-primary">
      <i class="bx bx-file-blank me-1"></i> Laporkan Hasil
    </a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPenugasan">
      <i class="bx bx-plus me-1"></i> Buat Penugasan Baru
    </button>
  </div>
</div>

<!-- Kategori Tab Penugasan (PRD 3.5: Semua, Belum Dikerjakan, Sedang Berjalan, Menunggu Verifikasi, Selesai) -->
<div class="card mb-4">
  <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="nav-pills-custom flex-wrap">
      <a href="{{ route('penugasan.index', ['kategori' => 'semua']) }}"
         class="nav-link text-decoration-none {{ $kategori === 'semua' ? 'active' : '' }}">
        Semua ({{ $counts['semua'] }})
      </a>
      <a href="{{ route('penugasan.index', ['kategori' => 'belum_dikerjakan']) }}"
         class="nav-link text-decoration-none {{ $kategori === 'belum_dikerjakan' ? 'active' : '' }}">
        Belum Dikerjakan ({{ $counts['belum_dikerjakan'] }})
      </a>
      <a href="{{ route('penugasan.index', ['kategori' => 'sedang_berjalan']) }}"
         class="nav-link text-decoration-none {{ $kategori === 'sedang_berjalan' ? 'active' : '' }}">
        Sedang Berjalan ({{ $counts['sedang_berjalan'] }})
      </a>
      <a href="{{ route('penugasan.index', ['kategori' => 'menunggu_verifikasi']) }}"
         class="nav-link text-decoration-none {{ $kategori === 'menunggu_verifikasi' ? 'active' : '' }}">
        Menunggu Verifikasi ({{ $counts['menunggu_verifikasi'] }})
      </a>
      @if($counts['perlu_perbaikan'] > 0)
        <a href="{{ route('penugasan.index', ['kategori' => 'perlu_perbaikan']) }}"
           class="nav-link text-decoration-none {{ $kategori === 'perlu_perbaikan' ? 'active' : '' }}">
          Perlu Perbaikan ({{ $counts['perlu_perbaikan'] }})
        </a>
      @endif
      <a href="{{ route('penugasan.index', ['kategori' => 'selesai']) }}"
         class="nav-link text-decoration-none {{ $kategori === 'selesai' ? 'active' : '' }}">
        Selesai ({{ $counts['selesai'] }})
      </a>
    </div>

    <form method="GET" action="{{ route('penugasan.index') }}" class="d-flex gap-2 align-items-center">
      <input type="hidden" name="kategori" value="{{ $kategori }}">
      <select name="pegawai_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">-- Filter Pegawai --</option>
        @foreach($pegawais as $p)
          <option value="{{ $p->id }}" {{ (string) $pegawaiId === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <select name="prioritas" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">-- Prioritas --</option>
        <option value="mendesak" {{ $prioritas === 'mendesak' ? 'selected' : '' }}>Mendesak</option>
        <option value="tinggi" {{ $prioritas === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
        <option value="sedang" {{ $prioritas === 'sedang' ? 'selected' : '' }}>Sedang</option>
        <option value="rendah" {{ $prioritas === 'rendah' ? 'selected' : '' }}>Rendah</option>
      </select>
    </form>
  </div>
</div>

<!-- Daftar Card & Tabel Penugasan (PRD 3.5: Informasi Setiap Penugasan) -->
<div class="row g-3">
  @forelse($penugasans as $item)
    @php
      $stBadge = $statusBadgeMap[$item->status] ?? ['label' => $item->status, 'class' => 'badge-soft-secondary'];
      $prioBadge = $prioritasBadgeMap[$item->prioritas] ?? ['label' => $item->prioritas, 'class' => 'badge-soft-secondary'];
    @endphp
    <div class="col-md-6 col-xl-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge {{ $prioBadge['class'] }}">PRIORITAS {{ strtoupper($prioBadge['label']) }}</span>
              <span class="badge {{ $stBadge['class'] }}">{{ $stBadge['label'] }}</span>
            </div>

            <div class="small text-primary fw-bold mb-1">{{ $item->nomor_surat_tugas }} • {{ $item->sumber_kegiatan }}</div>
            <h5 class="fw-bold text-dark mb-2">
              <a href="{{ route('penugasan.show', $item) }}" class="text-dark text-decoration-none">
                {{ $item->kegiatan->nama_kegiatan ?? '-' }}
              </a>
            </h5>

            <div class="small text-muted mb-3">
              <div class="mb-1">
                <i class="bx bx-calendar text-primary me-1"></i>
                <strong>Tanggal Pelaksanaan:</strong> {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                (Tenggat: {{ $item->tenggat_waktu->translatedFormat('d M Y') }})
              </div>
              <div class="mb-1">
                <i class="bx bx-map text-danger me-1"></i>
                <strong>Lokasi:</strong> {{ $item->kegiatan->lokasi ?? '-' }}
              </div>
              <div class="mb-1">
                <i class="bx bx-user-check text-success me-1"></i>
                <strong>Penanggung Jawab:</strong> {{ $item->penanggungJawab->name ?? '-' }}
              </div>
              <div>
                <i class="bx bx-bullseye text-info me-1"></i>
                <strong>Target:</strong> {{ $item->target_pekerjaan }} {{ $item->satuan }}
              </div>
            </div>
          </div>

          <!-- Tindakan: Mulai Pelaksanaan, Lihat Detail, Laporkan Hasil (PRD 3.5) -->
          <div class="pt-3 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <a href="{{ route('penugasan.show', $item) }}" class="btn btn-sm btn-outline-primary">
              Lihat Detail
            </a>

            <div class="d-flex gap-1">
              @if($item->status === 'belum_dikerjakan')
                <form method="POST" action="{{ route('penugasan.mulai', $item) }}">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-info text-white">
                    <i class="bx bx-play"></i> Mulai Pelaksanaan
                  </button>
                </form>
              @endif

              @if($item->status !== 'selesai')
                <a href="{{ route('pelaksanaan.create', ['penugasan_id' => $item->id]) }}" class="btn btn-sm btn-primary">
                  <i class="bx bx-upload me-1"></i> Laporkan Hasil
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="card">
        <div class="card-body text-center py-5 text-muted">
          <i class="bx bx-task fs-1 d-block mb-2"></i>
          Tidak ada penugasan pada kategori ini.
        </div>
      </div>
    </div>
  @endforelse
</div>

<!-- Modal Tambah Penugasan Baru -->
<div class="modal fade" id="modalTambahPenugasan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('penugasan.store') }}">
        @csrf
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Buat Surat Tugas / Penugasan Pegawai Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Kegiatan RPK / Jadwal <span class="text-danger">*</span></label>
              <select name="kegiatan_id" class="form-select" required>
                @foreach($kegiatans as $keg)
                  <option value="{{ $keg->id }}">{{ $keg->kode_kegiatan }} - {{ $keg->nama_kegiatan }} ({{ $keg->lokasi }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nomor Surat Tugas (Opsional)</label>
              <input type="text" name="nomor_surat_tugas" class="form-control" placeholder="Otomatis jika kosong">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Penanggung Jawab <span class="text-danger">*</span></label>
              <select name="penanggung_jawab_id" class="form-select" required>
                @foreach($pegawais as $p)
                  <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->jabatan }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tupoksi Terkait</label>
              <select name="tupoksi_id" class="form-select">
                <option value="">-- Pilih Tupoksi Pegawai --</option>
                @foreach($tupoksis as $tup)
                  <option value="{{ $tup->id }}">{{ $tup->kode_tupoksi }} - {{ $tup->nama_tupoksi }} ({{ $tup->user->name ?? '' }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Anggota Pelaksana</label>
              <select name="anggota_ids[]" class="form-select select2" multiple>
                @foreach($pegawais as $p)
                  <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->jabatan }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sumber Kegiatan</label>
              <select name="sumber_kegiatan" class="form-select" required>
                <option value="RPK Bulanan">RPK Bulanan</option>
                <option value="RPK Tahunan">RPK Tahunan</option>
                <option value="Disposisi Pimpinan">Disposisi Pimpinan</option>
                <option value="Rutin UKM">Rutin UKM</option>
              </select>
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
                <input type="number" name="target_pekerjaan" class="form-control" value="30" min="1" required>
                <input type="text" name="satuan" class="form-control" value="Sasaran" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tenggat Waktu</label>
              <input type="date" name="tenggat_waktu" class="form-control" value="{{ now()->addDays(2)->format('Y-m-d') }}" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Instruksi Pekerjaan <span class="text-danger">*</span></label>
              <textarea name="instruksi_pekerjaan" rows="3" class="form-control" required placeholder="Jelaskan instruksi kerja kepada petugas pelaksana..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Indikator Keberhasilan</label>
              <input type="text" name="indikator_keberhasilan" class="form-control" placeholder="Contoh: Target sasaran tercapai 100% dan laporan terverifikasi">
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

