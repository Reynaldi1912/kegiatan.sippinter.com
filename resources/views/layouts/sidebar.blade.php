<style>
  /* ============================================================
     DASH / SIPPINTER - SIGNATURE CURVED CUTOUT SIDEBAR (SCREENSHOT 2)
     ============================================================ */
  .layout-menu {
    background: #1f398a !important;
    background: linear-gradient(180deg, #1f398a 0%, #1a2f6c 100%) !important;
    border-right: none !important;
    box-shadow: 4px 0 25px rgba(15, 23, 42, 0.08) !important;
    overflow: visible !important;
  }

  .layout-menu .app-brand {
    height: 76px !important;
    padding: 1.25rem 1.5rem !important;
    display: flex !important;
    align-items: center !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    margin-bottom: 0.85rem !important;
  }

  .dash-brand-icon {
    width: 38px !important;
    height: 38px !important;
    border-radius: 11px !important;
    background: rgba(255, 255, 255, 0.14) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #ffffff !important;
    font-size: 1.35rem !important;
    margin-right: 0.75rem !important;
    flex-shrink: 0 !important;
  }

  .dash-brand-title {
    font-size: 1.12rem !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    letter-spacing: 0.05em !important;
    line-height: 1.05 !important;
  }

  .dash-brand-subtitle {
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.12em !important;
    color: rgba(255, 255, 255, 0.75) !important;
    text-transform: uppercase !important;
    line-height: 1.1 !important;
  }

  .layout-menu .menu-header {
    padding: 0.85rem 1.5rem 0.65rem 1.5rem !important;
    margin-top: 0.25rem !important;
  }

  .layout-menu .menu-header::before,
  .layout-menu .menu-header:before {
    display: none !important;
  }

  .layout-menu .menu-header .menu-header-text {
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.1em !important;
    color: rgba(255, 255, 255, 0.45) !important;
    text-transform: uppercase !important;
  }

  .layout-menu,
  .layout-menu .menu-inner {
    box-sizing: border-box !important;
  }

  .layout-menu .menu-inner {
    width: 100% !important;
    padding: 0.25rem 0 1.5rem 0 !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: calc(100vh - 100px) !important;
    overflow-x: visible !important;
  }

  .layout-menu .menu-inner > .menu-item {
    width: 100% !important;
    margin-bottom: 0.35rem !important;
    position: relative !important;
  }

  /* Inactive item link */
  .layout-menu .menu-inner > .menu-item:not(.active) > .menu-link {
    margin: 0 1rem !important;
    border-radius: 25px !important;
    padding: 0.72rem 1.25rem !important;
    font-size: 0.92rem !important;
    font-weight: 500 !important;
    color: rgba(255, 255, 255, 0.75) !important;
    min-height: 48px !important;
    display: flex !important;
    align-items: center !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
  }

  .layout-menu .menu-inner > .menu-item:not(.active) > .menu-link i.menu-icon {
    font-size: 1.35rem !important;
    margin-right: 0.95rem !important;
    color: rgba(255, 255, 255, 0.75) !important;
    flex-shrink: 0 !important;
    transition: all 0.2s ease !important;
  }

  .layout-menu .menu-inner > .menu-item:not(.active) > .menu-link:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: #ffffff !important;
    transform: translateX(3px) !important;
  }

  .layout-menu .menu-inner > .menu-item:not(.active) > .menu-link:hover i.menu-icon {
    color: #ffffff !important;
  }

  /* Active Cut-Out Tab (Signature Screenshot 2 style) */
  .layout-menu .menu-inner > .menu-item.active {
    position: relative !important;
    background: transparent !important;
  }

  .layout-menu .menu-inner > .menu-item.active > .menu-link {
    background: #ffffff !important;
    color: #1f398a !important;
    font-weight: 700 !important;
    font-size: 0.92rem !important;
    margin-left: 1.25rem !important;
    margin-right: 0 !important;
    padding: 0.75rem 1.35rem !important;
    border-radius: 30px 0 0 30px !important;
    box-shadow: none !important;
    min-height: 50px !important;
    display: flex !important;
    align-items: center !important;
    position: relative !important;
    z-index: 5 !important;
    text-decoration: none !important;
  }

  .layout-menu .menu-inner > .menu-item.active > .menu-link i.menu-icon {
    color: #1f398a !important;
    font-size: 1.35rem !important;
    margin-right: 0.95rem !important;
    flex-shrink: 0 !important;
  }

  .layout-menu .menu-inner > .menu-item.active > .menu-link > div,
  .layout-menu .menu-inner > .menu-item.active > .menu-link span {
    color: #1f398a !important;
    font-weight: 700 !important;
  }

  /* Top Inverted Corner Cutout (Clean Radial Curve - Zero Artifacts) */
  .layout-menu .menu-inner > .menu-item.active::before {
    content: '' !important;
    display: block !important;
    position: absolute !important;
    top: -20px !important;
    right: 0 !important;
    width: 20px !important;
    height: 20px !important;
    background: radial-gradient(circle at 0 0, transparent 20px, #ffffff 20.5px) !important;
    border: none !important;
    box-shadow: none !important;
    z-index: 4 !important;
    pointer-events: none !important;
  }

  /* Bottom Inverted Corner Cutout (Clean Radial Curve - Zero Artifacts) */
  .layout-menu .menu-inner > .menu-item.active::after {
    content: '' !important;
    display: block !important;
    position: absolute !important;
    bottom: -20px !important;
    right: 0 !important;
    width: 20px !important;
    height: 20px !important;
    background: radial-gradient(circle at 0 100%, transparent 20px, #ffffff 20.5px) !important;
    border: none !important;
    box-shadow: none !important;
    z-index: 4 !important;
    pointer-events: none !important;
  }

  /* Bottom Logout Area */
  .sidebar-bottom-action {
    margin-top: auto !important;
    padding-top: 1.5rem !important;
    padding-bottom: 0.75rem !important;
    padding-left: 1.25rem !important;
    padding-right: 1.25rem !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
  }

  .sidebar-bottom-action a {
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    color: rgba(255, 255, 255, 0.7) !important;
    text-decoration: none !important;
    font-size: 0.92rem !important;
    font-weight: 600 !important;
    padding: 0.65rem 1rem !important;
    border-radius: 20px !important;
    transition: all 0.2s ease !important;
  }

  .sidebar-bottom-action a:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.1) !important;
  }
</style>

<aside id="layout-menu" class="layout-menu menu-vertical menu">
  <!-- Brand / Logo (Kegiatan SIPPINTER) -->
  <div class="app-brand demo">
    <a href="{{ route('dashboard') }}" class="app-brand-link text-decoration-none d-flex align-items-center">
      <div class="dash-brand-icon">
        <i class="bx bx-plus-medical"></i>
      </div>
      <div class="d-flex flex-column">
        <span class="dash-brand-title">KEGIATAN</span>
        <span class="dash-brand-subtitle">SIPPINTER</span>
      </div>
    </a>

    <!-- Mobile-only close button -->
    <a href="javascript:void(0);" class="btn-sidebar-close-custom d-xl-none" id="btnSidebarClose" title="Tutup Sidebar">
      <i class="bx bx-chevron-left"></i>
    </a>
  </div>

  <ul class="menu-inner py-1">
    <!-- MAIN MENU SECTION -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Main Menu</span>
    </li>

    <!-- 1. Dashboard -->
    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-alt-2"></i>
        <div>
          <span>Dashboard</span>
        </div>
      </a>
    </li>

    <!-- 2. Jadwal Kegiatan -->
    <li class="menu-item {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
      <a href="{{ route('jadwal.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-calendar"></i>
        <div>
          <span>Jadwal Kegiatan</span>
        </div>
      </a>
    </li>

    <!-- 3. Penugasan -->
    <li class="menu-item {{ request()->routeIs('penugasan.*') ? 'active' : '' }}">
      <a href="{{ route('penugasan.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-user-check"></i>
        <div>
          <span>Penugasan</span>
        </div>
      </a>
    </li>

    <!-- 4. Pelaksanaan & Laporan -->
    <li class="menu-item {{ request()->routeIs('pelaksanaan.*') ? 'active' : '' }}">
      <a href="{{ route('pelaksanaan.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-check-square"></i>
        <div>
          <span>Pelaksanaan</span>
        </div>
      </a>
    </li>

    <!-- 5. RUK / RPK / RBA -->
    <li class="menu-item {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}">
      <a href="{{ route('kegiatan.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
        <div>
          <span>RUK / RPK</span>
        </div>
      </a>
    </li>

    <!-- 6. Approval Line & Dana -->
    <li class="menu-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
      <a href="{{ route('approval.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-check-shield"></i>
        <div>
          <span>Approval Line</span>
        </div>
      </a>
    </li>

    <!-- 6. Capaian Tupoksi -->
    <li class="menu-item {{ request()->routeIs('tupoksi.*') ? 'active' : '' }}">
      <a href="{{ route('tupoksi.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-target-lock"></i>
        <div>
          <span>Capaian Tupoksi</span>
        </div>
      </a>
    </li>

    <!-- 7. Riwayat Laporan -->
    <li class="menu-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
      <a href="{{ route('riwayat.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file-blank"></i>
        <div>
          <span>Riwayat Laporan</span>
        </div>
      </a>
    </li>

    <!-- 8. Data Pegawai -->
    <li class="menu-item {{ request()->routeIs('pegawai.*') || request()->routeIs('profile.*') || request()->routeIs('sinkronisasi.*') ? 'active' : '' }}">
      <a href="{{ route('pegawai.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-group"></i>
        <div>
          <span>Data Pegawai</span>
        </div>
      </a>
    </li>

    <!-- 9. Notifikasi -->
    <li class="menu-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
      <a href="{{ route('notifikasi.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-bell"></i>
        <div>
          <span>Notifikasi</span>
        </div>
      </a>
    </li>

    <!-- BOTTOM LOGOUT ACTION -->
    <li class="sidebar-bottom-action">
      <form action="{{ route('logout') }}" method="POST" id="sidebarLogoutForm">
        @csrf
        <a href="javascript:void(0);" onclick="document.getElementById('sidebarLogoutForm').submit();">
          <i class="bx bx-log-out fs-5"></i>
          <span>Logout</span>
        </a>
      </form>
    </li>
  </ul>
</aside>
