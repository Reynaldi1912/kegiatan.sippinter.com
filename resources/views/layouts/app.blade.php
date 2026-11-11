<!doctype html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('sneats/assets/') }}/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title', 'Dashboard') | Kegiatan SIPPINTER</title>

  <meta name="description" content="Kegiatan SIPPINTER - Manajemen Kegiatan Puskesmas" />

  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('sneats/assets/img/favicon/favicon.ico') }}" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="{{ asset('sneats/assets/vendor/fonts/iconify-icons.css') }}" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="{{ asset('sneats/assets/vendor/css/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneats/assets/css/demo.css') }}" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="{{ asset('sneats/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
  <link rel="stylesheet" href="{{ asset('sneats/assets/vendor/libs/apex-charts/apex-charts.css') }}" />

  <!-- [TAMBAHAN 1] Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

  <!-- [TAMBAHAN 3] DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

  <!-- Stack CSS / Styles -->
  @stack('css')
  @stack('styles')
  @yield('css')
  @yield('styles')

  <!-- Custom Styles -->
  <style>
    :root {
      --bs-primary: #2563eb;
      --bs-primary-rgb: 37, 99, 235;
    }

    body {
      background-color: #ebf0f8 !important;
      background-image: 
        radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.8) 0, rgba(255, 255, 255, 0) 180px),
        radial-gradient(circle at 88% 12%, rgba(219, 234, 254, 0.5) 0, rgba(219, 234, 254, 0) 240px),
        radial-gradient(circle at 8% 85%, rgba(224, 231, 255, 0.45) 0, rgba(224, 231, 255, 0) 220px),
        radial-gradient(circle at 92% 82%, rgba(255, 255, 255, 0.85) 0, rgba(255, 255, 255, 0) 200px) !important;
      background-attachment: fixed !important;
      font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
      color: #334155;
    }

    /* Card styling - Clean White Rounded (Screenshot 2 style) */
    .layout-page {
      background: #ffffff !important;
    }

    .card {
      border: 1px solid #edf2f7 !important;
      border-radius: 18px !important;
      box-shadow: 0 4px 20px rgba(20, 40, 90, 0.03) !important;
      background: #ffffff !important;
    }

    /* Navbar styling */
    .layout-navbar {
      border-radius: 10px !important;
      box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05) !important;
      background: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      margin-top: 0.75rem !important;
    }

    /* Button styling */
    .btn-primary {
      background: #2563eb !important;
      border: 1px solid #1d4ed8 !important;
      box-shadow: none !important;
      border-radius: 6px !important;
      font-weight: 600 !important;
      padding: 0.55rem 1.25rem !important;
      transition: all 0.15s ease !important;
    }

    .btn-primary:hover {
      background: #1d4ed8 !important;
      box-shadow: none !important;
      transform: none !important;
    }

    /* Soft Badge Styles */
    .badge-soft-danger { background-color: #fee2e2 !important; color: #dc2626 !important; }
    .badge-soft-warning { background-color: #fef3c7 !important; color: #d97706 !important; }
    .badge-soft-success { background-color: #dcfce7 !important; color: #16a34a !important; }
    .badge-soft-primary { background-color: #dbeafe !important; color: #2563eb !important; }
    .badge-soft-info { background-color: #e0f2fe !important; color: #0284c7 !important; }
    .badge-soft-purple { background-color: #f3e8ff !important; color: #9333ea !important; }
    .badge-soft-dark { background-color: #f1f5f9 !important; color: #334155 !important; }
    .badge-soft-secondary { background-color: #f1f5f9 !important; color: #64748b !important; }

    /* Segmented Pill Tabs */
    .nav-pills-custom {
      background-color: #f1f5f9;
      padding: 4px;
      border-radius: 10px;
      border: none !important;
      display: inline-flex;
      gap: 4px;
    }
    .nav-pills-custom .nav-link {
      color: #64748b;
      font-weight: 600;
      font-size: 0.825rem;
      border-radius: 7px;
      padding: 0.5rem 1rem;
      transition: all 0.2s ease;
      border: none !important;
      box-shadow: none !important;
    }
    .nav-pills-custom .nav-link:hover:not(.active) {
      color: #1e293b;
      background-color: #e2e8f0;
    }
    .nav-pills-custom .nav-link.active {
      color: #ffffff !important;
      background-color: #2563eb !important;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
    }

    /* Action Buttons Spacing */
    .table-action-btns {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 10px !important;
    }
    .table-action-btns .btn,
    .table-action-btns form {
      margin-right: 0 !important;
      margin-left: 0 !important;
    }

    /* Table Improvements */
    .table thead th {
      background-color: #f8fafc !important;
      color: #64748b !important;
      font-size: 0.75rem !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.6px !important;
      border-bottom: 2px solid #e2e8f0 !important;
      padding: 1rem 1.25rem !important;
    }

    .table td {
      padding: 1rem 1.25rem !important;
      vertical-align: middle !important;
      color: #334155 !important;
    }

    .table th:first-child,
    .table td:first-child {
      padding-left: 1.75rem !important;
    }

    .table th:last-child,
    .table td:last-child {
      padding-right: 1.75rem !important;
    }

    .table-hover tbody tr:hover {
      background-color: #f8fafc !important;
    }

    /* Style Select2 */
    .select2-container--bootstrap-5 .select2-selection {
      border-color: #d9dee3;
      border-radius: 8px;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5 .select2-selection:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
    }

    /* Style Notifikasi Pojok Kanan Bawah */
    .toast-notification-container {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
      max-width: 400px;
    }

    .toast-alert {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border-radius: 10px;
      border: none;
    }

    /* Backdrop blur effect for modals */
    .modal-backdrop.show {
      backdrop-filter: blur(15px) !important;
      -webkit-backdrop-filter: blur(15px) !important;
      background-color: rgba(15, 23, 42, 0.55) !important;
    }

    /* Sidebar Smooth Collapsed Transitions (Desktop Full-Width Support) */
    .layout-menu,
    .layout-page {
      transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Fixed Desktop Sidebar Alignment Fix */
    @media (min-width: 1200px) {
      .layout-menu-fixed .layout-menu {
        position: fixed !important;
        top: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        height: 100% !important;
        z-index: 1040 !important;
      }

      .layout-menu-fixed:not(.layout-menu-collapsed) .layout-page {
        padding-left: 16.25rem !important;
      }

      .layout-menu-fixed:not(.layout-menu-collapsed) .layout-navbar.navbar-detached {
        width: calc(100% - 16.25rem - 3rem) !important;
      }
    }

    /* Responsive Spacious Container Width - Modern Dashboard Feel */
    @media (min-width: 1200px) {
      .container-xxl,
      .content-wrapper > .container-xxl,
      .layout-navbar.container-xxl,
      .content-footer .container-xxl {
        max-width: 96% !important;
        width: 100% !important;
      }
    }

    @media (min-width: 1600px) {
      .container-xxl,
      .content-wrapper > .container-xxl,
      .layout-navbar.container-xxl,
      .content-footer .container-xxl {
        max-width: 97% !important;
      }
    }

    @media (min-width: 1200px) {
      html.layout-menu-collapsed:not(.layout-menu-hover) .layout-menu {
        width: 0 !important;
        min-width: 0 !important;
        transform: translate3d(-100%, 0, 0) !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
      }
      html.layout-menu-collapsed .layout-page {
        padding-left: 0 !important;
      }
      html.layout-menu-collapsed .layout-navbar.navbar-detached {
        width: 100% !important;
        max-width: 96% !important;
      }
    }
  </style>

  <!-- Page CSS -->
  @stack('styles')

  <!-- Helpers -->
  <script src="{{ asset('sneats/assets/vendor/js/helpers.js') }}"></script>
  <script src="{{ asset('sneats/assets/js/config.js') }}"></script>
  <script>
    (function () {
      try {
        if (window.innerWidth >= 1200 && localStorage.getItem('sippinter_sidebar_collapsed') === 'true') {
          document.documentElement.classList.add('layout-menu-collapsed');
        }
      } catch (e) {}
    })();
  </script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      @include('layouts.sidebar')
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        @include('layouts.header')
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Konten Utama -->
            @yield('content')

          </div>
          <!-- / Content -->

          <!-- Footer -->
          @include('layouts.footer')
          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- [TAMBAHAN GLOBAL] Flash Message Handling (Floating Bottom Right) -->
  <div class="toast-notification-container">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show toast-alert" role="alert">
        <div class="d-flex align-items-center">
          <i class="bx bx-check-circle me-2 fs-4"></i>
          <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show toast-alert" role="alert">
        <div class="d-flex align-items-center">
          <i class="bx bx-error-circle me-2 fs-4"></i>
          <div>{{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
  </div>
  <!-- Akhir Flash Message -->

  <!-- Core JS -->
  <script src="{{ asset('sneats/assets/vendor/libs/jquery/jquery.js') }}"></script>
  <script src="{{ asset('sneats/assets/vendor/libs/popper/popper.js') }}"></script>
  <script src="{{ asset('sneats/assets/vendor/js/bootstrap.js') }}"></script>
  <script src="{{ asset('sneats/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('sneats/assets/vendor/js/menu.js') }}"></script>

  <!-- Vendors JS -->
  <script src="{{ asset('sneats/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

  <!-- Main JS -->
  <script src="{{ asset('sneats/assets/js/main.js') }}"></script>

  <!-- Page JS -->
  <script src="{{ asset('sneats/assets/js/dashboards-analytics.js') }}"></script>

  <!-- [TAMBAHAN 2] Select2 JS & Auto Hide Alert -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- [TAMBAHAN 3] DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
      // Inisialisasi Global untuk elemen dengan class .select2
      $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih Opsi'
      });

      // [LOGIK BARU] Auto hide alert setelah 5 detik
      setTimeout(function () {
        $('.toast-alert').fadeOut('slow', function () {
          $(this).remove();
        });
      }, 5000); // 5000 ms = 5 detik

      // [TOGGLE SIDEBAR] Desktop & Mobile handler
      function toggleSidebar(forceState) {
        var html = document.documentElement;
        if (window.innerWidth < 1200) {
          if (typeof forceState === 'boolean') {
            if (forceState) {
              html.classList.add('layout-menu-expanded');
            } else {
              html.classList.remove('layout-menu-expanded');
            }
          } else {
            html.classList.toggle('layout-menu-expanded');
          }
        } else {
          var willBeCollapsed = (typeof forceState === 'boolean')
            ? forceState
            : !html.classList.contains('layout-menu-collapsed');

          if (willBeCollapsed) {
            html.classList.add('layout-menu-collapsed');
          } else {
            html.classList.remove('layout-menu-collapsed');
          }

          try {
            localStorage.setItem('sippinter_sidebar_collapsed', willBeCollapsed ? 'true' : 'false');
          } catch(e) {}

          if (window.Helpers && typeof window.Helpers._triggerWindowEvent === 'function') {
            window.Helpers._triggerWindowEvent('resize');
          }
        }
      }

      window.toggleSidebar = toggleSidebar;

      $(document).on('click', '#btnToggleSidebar', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
      });

      $(document).on('click', '#btnSidebarClose', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar(true);
      });

      // Mobile overlay click to close
      $(document).on('click', '.layout-overlay', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.innerWidth < 1200) {
          document.documentElement.classList.remove('layout-menu-expanded');
        }
      });
    });
  </script>

  <!-- Stack Scripts untuk script tambahan dari View -->
  @stack('scripts')
</body>

</html>