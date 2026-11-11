@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
  /* ============================================================
     DASHBOARD SCREENSHOT 2 ACCURATE STYLING
     ============================================================ */
  .dash-stat-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 20px;
    padding: 1.35rem 1.45rem;
    box-shadow: 0 4px 20px rgba(20, 40, 90, 0.03);
    transition: all 0.2s ease;
    height: 100%;
  }
  .dash-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(20, 40, 90, 0.06);
  }

  .dash-stat-number {
    font-size: 1.85rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.1;
    margin-bottom: 0.25rem;
    letter-spacing: -0.02em;
  }

  .dash-stat-label {
    font-size: 0.86rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.35rem;
  }

  .dash-stat-delta {
    font-size: 0.76rem;
    font-weight: 600;
    color: #10b981;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
  }

  .dash-stat-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
  }

  /* Card Headings */
  .dash-section-title {
    font-size: 1.08rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0;
    letter-spacing: -0.01em;
  }

  .dash-dots-btn {
    color: #94a3b8;
    background: transparent;
    border: none;
    font-size: 1.25rem;
    line-height: 1;
    padding: 0.25rem;
    cursor: pointer;
    transition: color 0.15s ease;
  }
  .dash-dots-btn:hover {
    color: #334155;
  }

  /* Legend Indicators */
  .dash-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #94a3b8;
    text-transform: uppercase;
  }
  .dash-legend-dot-orange {
    width: 10px;
    height: 10px;
    border-radius: 2px;
    background-color: #f59e0b;
  }
  .dash-legend-dot-blue {
    width: 10px;
    height: 10px;
    border-radius: 2px;
    background-color: #3b82f6;
  }

  /* Task Checklist Styling */
  .dash-task-item {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 0.85rem 0;
    border-bottom: 1px solid #f8fafc;
  }
  .dash-task-item:last-child {
    border-bottom: none;
  }

  .dash-task-checkbox {
    width: 20px;
    height: 20px;
    border-radius: 5px;
    border: 1.5px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
    cursor: pointer;
    flex-shrink: 0;
    background: #ffffff;
    transition: all 0.15s ease;
  }

  .dash-task-checkbox.checked {
    background-color: #14b8a6;
    border-color: #14b8a6;
    color: #ffffff;
    font-size: 0.75rem;
  }

  .dash-task-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.15rem;
    line-height: 1.3;
  }

  .dash-task-date {
    font-size: 0.76rem;
    color: #94a3b8;
  }

  .dash-add-task-btn {
    background: #1f398a;
    color: #ffffff;
    border: none;
    border-radius: 25px;
    padding: 0.5rem 1.25rem;
    font-size: 0.82rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
    text-decoration: none !important;
  }
  .dash-add-task-btn:hover {
    background: #172b6b;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(31, 57, 138, 0.25);
  }

  /* Patient Table Styling */
  .dash-patient-table {
    width: 100%;
    margin-bottom: 0;
  }
  .dash-patient-table th {
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    color: #64748b !important;
    text-transform: none !important;
    letter-spacing: normal !important;
    padding: 0.9rem 1.25rem !important;
    border-bottom: 1px solid #f1f5f9 !important;
    background: #ffffff !important;
  }
  .dash-patient-table td {
    font-size: 0.86rem !important;
    color: #334155 !important;
    padding: 1.1rem 1.25rem !important;
    border-bottom: 1px solid #f8fafc !important;
    vertical-align: middle !important;
  }
  .dash-patient-table tbody tr:hover {
    background-color: #fbfcfe !important;
  }

  /* Quick Bar for Scope toggle */
  .dash-quick-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 0.75rem;
  }
</style>

<!-- Context Bar / Scope Selector -->
<div class="dash-quick-bar">
  <div class="d-flex align-items-center gap-2">
    <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: rgba(31, 57, 138, 0.1); color: #1f398a; font-size: 0.78rem;">
      <i class="bx bx-check-circle me-1"></i> Mode: {{ $filterScope === 'puskesmas' ? 'Seluruh Puskesmas' : 'Penugasan Saya' }}
    </span>
    <span class="text-muted small d-none d-md-inline">Halo, <strong>{{ $user->name }}</strong> ({{ $user->jabatan ?? 'Pegawai' }})</span>
  </div>

  <div class="d-flex align-items-center gap-2">
    <div class="btn-group p-1 rounded-pill" style="background: #e2e8f0;">
      <a href="{{ route('dashboard', ['scope' => 'puskesmas']) }}"
         class="btn btn-sm {{ $filterScope === 'puskesmas' ? 'btn-white bg-white text-dark fw-bold shadow-sm' : 'text-secondary' }} rounded-pill border-0 px-3 py-1">
        Seluruh Puskesmas
      </a>
      <a href="{{ route('dashboard', ['scope' => 'saya']) }}"
         class="btn btn-sm {{ $filterScope === 'saya' ? 'btn-white bg-white text-dark fw-bold shadow-sm' : 'text-secondary' }} rounded-pill border-0 px-3 py-1">
        Penugasan Saya
      </a>
    </div>

    <a href="{{ route('pelaksanaan.create') }}" class="dash-add-task-btn">
      <i class="bx bx-plus"></i> Laporkan Hasil
    </a>
  </div>
</div>

<!-- ============================================================
     ROW 1: 4 STAT CARDS (100% Database-Driven)
     ============================================================ -->
<div class="row g-3 g-xl-4 mb-4">
  <!-- Card 1: Total Penugasan -->
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dash-stat-card">
      <div class="d-flex align-items-start justify-content-between">
        <div>
          <div class="dash-stat-number">{{ number_format($ringkasanPenugasan['total']) }}</div>
          <div class="dash-stat-label">Total Penugasan</div>
          <div class="dash-stat-delta">
            <i class="bx bx-run"></i> {{ $ringkasanPenugasan['sedang_berjalan'] }} Berjalan · {{ $ringkasanPenugasan['selesai'] }} Selesai
          </div>
        </div>
        <div class="dash-stat-icon-box">
          <i class="bx bx-calendar-check"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 2: Total Kegiatan RPK -->
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dash-stat-card">
      <div class="d-flex align-items-start justify-content-between">
        <div>
          <div class="dash-stat-number">{{ number_format($statistikPuskesmas['total_kegiatan_rpk']) }}</div>
          <div class="dash-stat-label">Kegiatan RUK / RPK</div>
          <div class="dash-stat-delta">
            <i class="bx bx-check-shield"></i> {{ $statistikPuskesmas['total_pelaksanaan'] ?? 0 }} Laporan Pelaksanaan
          </div>
        </div>
        <div class="dash-stat-icon-box">
          <i class="bx bx-show"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 3: Capaian Tupoksi / Realisasi RPK -->
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dash-stat-card">
      <div class="d-flex align-items-start justify-content-between">
        <div>
          <div class="dash-stat-number">{{ number_format($statistikPuskesmas['rata_capaian_tupoksi'], 1) }}%</div>
          <div class="dash-stat-label">Rata-rata Capaian Tupoksi</div>
          <div class="dash-stat-delta">
            <i class="bx bx-wallet"></i> Rp {{ number_format($statistikPuskesmas['total_realisasi_rpk'] / 1000000, 1, ',', '.') }} Jt Realisasi
          </div>
        </div>
        <div class="dash-stat-icon-box">
          <i class="bx bx-wallet"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 4: Pegawai Aktif -->
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="dash-stat-card">
      <div class="d-flex align-items-start justify-content-between">
        <div>
          <div class="dash-stat-number">{{ number_format($statistikPuskesmas['total_pegawai']) }}</div>
          <div class="dash-stat-label">Pegawai Aktif</div>
          <div class="dash-stat-delta">
            <i class="bx bx-bell"></i> {{ $unreadNotifikasiCount }} Notifikasi Baru
          </div>
        </div>
        <div class="dash-stat-icon-box">
          <i class="bx bx-user"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     ROW 2: CHARTS & TASK (100% Database-Driven)
     ============================================================ -->
<div class="row g-3 g-xl-4 mb-4">
  <!-- Left: Realisasi & Target Kegiatan Chart -->
  <div class="col-12 col-xl-8">
    <div class="card h-100 p-4">
      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h5 class="dash-section-title">Statistik Target & Realisasi Kegiatan</h5>
        
        <div class="d-flex align-items-center gap-3">
          <div class="dash-legend-item">
            <span class="dash-legend-dot-orange"></span> TARGET KEGIATAN
          </div>
          <div class="dash-legend-item">
            <span class="dash-legend-dot-blue"></span> REALISASI LAPORAN
          </div>
          <a href="{{ route('riwayat.index') }}" class="dash-dots-btn" title="Lihat Riwayat">
            <i class="bx bx-dots-horizontal-rounded"></i>
          </a>
        </div>
      </div>

      <!-- Chart Container -->
      <div id="appointmentsHistoryChart" style="min-height: 290px;"></div>
    </div>
  </div>

  <!-- Right: Task Checklist Card -->
  <div class="col-12 col-xl-4">
    <div class="card h-100 p-4 d-flex flex-column justify-content-between">
      <div>
        <div class="d-flex align-items-center justify-content-between mb-1">
          <h5 class="dash-section-title">Jadwal Kegiatan Hari Ini</h5>
          <a href="{{ route('jadwal.index') }}" class="dash-dots-btn" title="Lihat Semua Jadwal">
            <i class="bx bx-dots-horizontal-rounded"></i>
          </a>
        </div>
        <small class="text-muted d-block mb-3" style="font-size: 0.74rem;">Daftar kegiatan & penugasan aktif di Puskesmas</small>

        <!-- Task Checklist Items from Database -->
        <div class="dash-task-list">
          @php
            $daftarTugasDashboard = $jadwalHariIni->isNotEmpty() ? $jadwalHariIni : $kegiatanMendatang;
          @endphp

          @forelse($daftarTugasDashboard->take(4) as $jadwal)
            <div class="dash-task-item">
              <span class="dash-task-checkbox {{ $jadwal->status === 'selesai' ? 'checked' : '' }}">
                @if($jadwal->status === 'selesai')
                  <i class="bx bx-check"></i>
                @endif
              </span>
              <div class="flex-grow-1">
                <a href="{{ route('jadwal.show', $jadwal) }}" class="text-decoration-none">
                  <div class="dash-task-title {{ $jadwal->status === 'selesai' ? 'text-decoration-line-through text-muted' : '' }}">
                    {{ Str::limit($jadwal->nama_kegiatan, 42) }}
                  </div>
                </a>
                <div class="dash-task-date">
                  {{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->translatedFormat('d M Y') : '-' }}
                  · {{ $jadwal->lokasi ?? 'Puskesmas' }}
                </div>
              </div>
              <a href="{{ route('jadwal.show', $jadwal) }}" class="text-muted" title="Detail Kegiatan">
                <i class="bx bx-chevron-right"></i>
              </a>
            </div>
          @empty
            <div class="text-muted small py-3 text-center">
              Belum ada jadwal kegiatan tercatat di database.
            </div>
          @endforelse
        </div>
      </div>

      <!-- Bottom Add Task Button -->
      <div class="pt-3">
        <a href="{{ route('pelaksanaan.create') }}" class="dash-add-task-btn">
          <i class="bx bx-plus"></i> Input Pelaksanaan
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     ROW 3: PENUGASAN & PELAKSANAAN TABLE (100% Database-Driven)
     ============================================================ -->
<div class="row g-3 g-xl-4 mb-4">
  <div class="col-12">
    <div class="card p-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="dash-section-title">Daftar Penugasan & Pelaksanaan Pegawai</h5>
        <a href="{{ route('penugasan.index') }}" class="dash-dots-btn" title="Lihat Semua Penugasan">
          <i class="bx bx-dots-horizontal-rounded"></i>
        </a>
      </div>

      <div class="table-responsive">
        <table class="dash-patient-table align-middle">
          <thead>
            <tr>
              <th>Pegawai Pelaksana</th>
              <th>Jabatan / Role</th>
              <th>Lokasi Kegiatan</th>
              <th>No. Surat Tugas</th>
              <th>Realisasi / Target</th>
              <th>Kegiatan & Program</th>
            </tr>
          </thead>
          <tbody>
            @forelse($patientRecords as $record)
              <tr>
                <td class="fw-semibold text-dark">
                  <a href="{{ route('penugasan.show', $record['id']) }}" class="text-dark text-decoration-none">
                    {{ $record['name'] }}
                  </a>
                </td>
                <td>{{ $record['jabatan'] }}</td>
                <td>{{ $record['address'] }}</td>
                <td><span class="badge bg-label-primary">{{ $record['nomor_surat'] }}</span></td>
                <td class="fw-semibold">{{ $record['target_text'] }}</td>
                <td>
                  <div class="fw-medium text-dark">{{ Str::limit($record['kegiatan_nama'], 48) }}</div>
                  <small class="text-muted">{{ $record['program'] }} · {{ $record['tanggal'] }}</small>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  Belum ada data penugasan pada cakupan ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var chartMonths = @json($chartMonths);
    var chartDataOne = @json($chartDataOne);
    var chartDataTwo = @json($chartDataTwo);

    var chartOptions = {
      series: [
        {
          name: 'TARGET KEGIATAN',
          data: chartDataOne
        },
        {
          name: 'REALISASI LAPORAN',
          data: chartDataTwo
        }
      ],
      chart: {
        type: 'area',
        height: 290,
        toolbar: { show: false },
        fontFamily: 'Public Sans, sans-serif'
      },
      colors: ['#f59e0b', '#3b82f6'],
      dataLabels: { enabled: false },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.25,
          opacityTo: 0.03,
          stops: [0, 90, 100]
        }
      },
      legend: { show: false },
      grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 0,
        xaxis: {
          lines: { show: false }
        },
        yaxis: {
          lines: { show: true }
        },
        padding: {
          top: 10,
          right: 15,
          bottom: 0,
          left: 10
        }
      },
      xaxis: {
        categories: chartMonths,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          style: {
            colors: '#94a3b8',
            fontSize: '11px',
            fontWeight: 500
          }
        }
      },
      yaxis: {
        min: 0,
        labels: {
          style: {
            colors: '#94a3b8',
            fontSize: '11px',
            fontWeight: 500
          }
        }
      },
      tooltip: {
        theme: 'light',
        x: { show: true }
      }
    };

    var chartContainer = document.querySelector("#appointmentsHistoryChart");
    if (chartContainer) {
      var chart = new ApexCharts(chartContainer, chartOptions);
      chart.render();
    }
  });
</script>
@endpush
