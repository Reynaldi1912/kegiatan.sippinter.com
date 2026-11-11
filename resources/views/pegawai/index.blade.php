@extends('layouts.app')

@section('title', 'Manajemen Data Pegawai')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">
      <i class="bx bx-group text-primary me-1"></i> Manajemen Akun & Data Pegawai Puskesmas
    </h4>
    <p class="text-muted mb-0 small">
      Pengelolaan akun login pegawai, NIP, jabatan, dan unit program untuk Web & Mobile Application (PRD 3.2 & 3.10)
    </p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPegawai">
    <i class="bx bx-user-plus me-1"></i> Tambah Pegawai Baru
  </button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Nama Pegawai & NIP</th>
          <th>Username & Email</th>
          <th>Jabatan & Unit Program</th>
          <th>Role Akses</th>
          <th>Penugasan & Tupoksi</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pegawais as $p)
          <tr>
            <td>
              <div class="fw-bold text-dark">{{ $p->name }}</div>
              <small class="text-muted">NIP: {{ $p->nip ?: '-' }}</small>
            </td>
            <td>
              <div class="fw-semibold">{{ $p->username }}</div>
              <small class="text-muted">{{ $p->email }}</small>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $p->jabatan }}</div>
              <span class="badge badge-soft-info">{{ $p->unit_program }}</span>
            </td>
            <td>
              <span class="badge badge-soft-primary">{{ strtoupper(str_replace('_', ' ', $p->role)) }}</span>
            </td>
            <td>
              <span class="badge badge-soft-purple">{{ $p->tupoksis_count }} Tupoksi</span>
              <span class="badge badge-soft-info">{{ $p->penugasans_count }} Tugas</span>
            </td>
            <td>
              <span class="badge {{ $p->is_active ? 'badge-soft-success' : 'badge-soft-danger' }}">
                {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Pegawai -->
<div class="modal fade" id="modalTambahPegawai" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <form method="POST" action="{{ route('pegawai.store') }}">
        @csrf
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Daftarkan Akun Pegawai Puskesmas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">NIP / Identitas</label>
              <input type="text" name="nip" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
              <input type="text" name="jabatan" class="form-control" required placeholder="Contoh: Bidan Pelaksana">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Unit / Program <span class="text-danger">*</span></label>
              <input type="text" name="unit_program" class="form-control" required placeholder="Contoh: KIA & KB">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nomor Telepon</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Hak Akses</label>
              <select name="role" class="form-select" required>
                <option value="pegawai">Pegawai Pelaksana</option>
                <option value="pj_program">Penanggung Jawab Program</option>
                <option value="kepala_tata_usaha">Kepala Tata Usaha</option>
                <option value="kepala_puskesmas">Kepala Puskesmas</option>
                <option value="super_admin">Super Admin</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Password Awal <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" value="password" required>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Akun Pegawai</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

