@extends('layouts.app')

@section('title', 'Profil Pegawai')

@section('content')
<!-- Halaman Profil Pegawai (PRD 3.10) -->
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-body text-center p-4">
        <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center fw-bold text-white shadow"
             style="width: 84px; height: 84px; font-size: 1.75rem; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
          {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
        <p class="text-primary fw-semibold small mb-1">{{ $user->jabatan ?? 'Pegawai Puskesmas' }}</p>
        <span class="badge badge-soft-info mb-3">{{ $user->unit_program ?? 'Puskesmas SIPPINTER' }}</span>

        <div class="text-start border-top pt-3 small">
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">NIP / Identitas:</span>
            <strong class="text-dark">{{ $user->nip ?: '-' }}</strong>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Username Login:</span>
            <strong class="text-dark">{{ $user->username }}</strong>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Email:</span>
            <strong class="text-dark">{{ $user->email }}</strong>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Nomor Telepon:</span>
            <strong class="text-dark">{{ $user->phone ?: '-' }}</strong>
          </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-3 pt-3 border-top">
          @csrf
          <button type="submit" class="btn btn-outline-danger w-100">
            <i class="bx bx-power-off me-1"></i> Logout dari Sistem
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Menu Profil (PRD 3.10: Profil Saya, Data Kepegawaian, Daftar Tupoksi, Pengaturan Akun, Ubah Password) -->
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <ul class="nav nav-pills nav-pills-custom" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabProfilSaya" type="button">
              1. Profil Saya & Pengaturan Akun
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabKepegawaian" type="button">
              2. Data Kepegawaian
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDaftarTupoksi" type="button">
              3. Daftar Tupoksi ({{ $user->tupoksis->count() }})
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabUbahPassword" type="button">
              4. Ubah Password
            </button>
          </li>
        </ul>
      </div>

      <div class="card-body">
        <div class="tab-content p-0">
          <!-- Tab 1: Profil Saya & Pengaturan Akun -->
          <div class="tab-pane fade show active" id="tabProfilSaya">
            <div class="alert alert-info small">
              <i class="bx bx-info-circle me-1"></i> Sesuai ketentuan PRD 3.10, informasi utama kepegawaian dikelola melalui Administrator Web Application. Anda dapat memperbarui alamat email, nomor telepon, dan foto profil di sini.
            </div>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nama Lengkap Pegawai</label>
                  <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">NIP / Identitas</label>
                  <input type="text" class="form-control bg-light" value="{{ $user->nip }}" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email Aktif</label>
                  <input type="email" name="email" class="form-control" value="{{ $user->email }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nomor Telepon / WhatsApp</label>
                  <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan Profil</button>
                </div>
              </div>
            </form>
          </div>

          <!-- Tab 2: Data Kepegawaian -->
          <div class="tab-pane fade" id="tabKepegawaian">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-light">
                  <small class="text-muted d-block">Jabatan Fungsional / Struktural</small>
                  <div class="fw-bold text-dark">{{ $user->jabatan }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-light">
                  <small class="text-muted d-block">Unit / Program Puskesmas</small>
                  <div class="fw-bold text-dark">{{ $user->unit_program }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-light">
                  <small class="text-muted d-block">Peran Hak Akses</small>
                  <div class="fw-bold text-primary">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-light">
                  <small class="text-muted d-block">Token Sinkronisasi Mobile App</small>
                  <code class="small">{{ $user->api_token }}</code>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 3: Daftar Tupoksi -->
          <div class="tab-pane fade" id="tabDaftarTupoksi">
            @forelse($user->tupoksis as $tup)
              <div class="p-3 rounded-3 border mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="badge badge-soft-purple">{{ $tup->kode_tupoksi }}</span>
                  <span class="fw-bold text-primary">{{ $tup->persentase_capaian }}% ({{ $tup->realisasi }}/{{ $tup->target }} {{ $tup->satuan }})</span>
                </div>
                <h6 class="fw-bold text-dark mb-1">{{ $tup->nama_tupoksi }}</h6>
                <p class="text-muted small mb-2">{{ $tup->uraian_tugas }}</p>
                <a href="{{ route('tupoksi.show', $tup) }}" class="btn btn-sm btn-outline-primary">Lihat Detail Tupoksi</a>
              </div>
            @empty
              <p class="text-muted small mb-0">Belum ada Tupoksi terdaftar pada akun Anda.</p>
            @endforelse
          </div>

          <!-- Tab 4: Ubah Password -->
          <div class="tab-pane fade" id="tabUbahPassword">
            <form method="POST" action="{{ route('profile.password') }}">
              @csrf
              @method('PUT')
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Password Saat Ini</label>
                  <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Password Baru</label>
                  <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                  <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Perbarui Password</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

