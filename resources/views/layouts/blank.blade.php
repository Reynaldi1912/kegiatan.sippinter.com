<!doctype html>

<html lang="en" class="layout-compact" data-assets-path="{{ asset('sneats/assets/') }}/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title', 'Dashboard') | Sneat Admin</title>

  <meta name="description" content="" />

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

  <!-- Custom Styles -->
  <style>
    /* Style Select2 */
    .select2-container--bootstrap-5 .select2-selection {
      border-color: #d9dee3;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5 .select2-selection:focus {
      border-color: #696cff;
      box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }

    /* [TAMBAHAN] Style Notifikasi Pojok Kanan Bawah */
    .toast-notification-container {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 9999;
      /* Pastikan di atas elemen lain */
      min-width: 300px;
      max-width: 400px;
    }

    .toast-alert {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      /* Shadow agar terlihat mengambang */
      border: none;
    }
  </style>

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="{{ asset('sneats/assets/vendor/js/helpers.js') }}"></script>
  <script src="{{ asset('sneats/assets/js/config.js') }}"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-without-menu">
    <div class="layout-container">
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
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

  <!-- Modal Pilih Display Kasir (Redesigned ASS1) -->
  @include('layouts.partials.modal_select_display_kasir')

  <!-- Modal Pilih Display Apotik (Redesigned ASS1) -->
  @include('layouts.partials.modal_select_display_apotik')

  <!-- Modal Pilih Display Poli (New) -->
  @include('layouts.partials.modal_select_display_poli')
@include('layouts.partials.modal_select_admin_poli')

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
    });
  </script>

  <!-- Place this tag before closing body tag for github widget button. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>

  <!-- Global Socket.io for Sidebar Badge Updates -->
  <script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
  <script>
    (function () {
      // Socket.io connection for sidebar badge updates
      const envSocketUrl = "{{ env('SOCKET_SERVER_URL', 'http://localhost:3000') }}";
      let socketUrl = envSocketUrl;

      // Auto-adapt to LAN if env is localhost
      if (envSocketUrl.includes('localhost') || envSocketUrl.includes('127.0.0.1')) {
        const portMatch = envSocketUrl.match(/:(\d+)/);
        const port = portMatch ? portMatch[1] : 3000;
        socketUrl = window.location.protocol + '//' + window.location.hostname + ':' + port;
      }

      const globalSocket = io(socketUrl, { transports: ['websocket', 'polling'] });

      globalSocket.on('connect', () => console.log('Global Socket connected for sidebar'));

      // Listen for count updates - update badge without refresh
      globalSocket.on('CountsUpdated', (data) => {
        console.log('Counts updated:', data.counts);
        updateSidebarBadges(data.counts);
      });

      globalSocket.on('TicketCreated', (data) => {
        console.log('Ticket created, fetching new counts...');
        fetchAndUpdateCounts();
      });

      globalSocket.on('TicketUpdated', (data) => {
        console.log('Ticket updated, fetching new counts...');
        fetchAndUpdateCounts();
      });

      globalSocket.on('RegistrationUpdated', (data) => {
        console.log('Registration updated, fetching new counts...');
        fetchAndUpdateCounts();
      });

      globalSocket.on('PharmacyUpdated', (data) => {
        console.log('Pharmacy updated, fetching new counts...');
        fetchAndUpdateCounts();
      });

      globalSocket.on('CashierUpdated', (data) => {
        console.log('Cashier updated, fetching new counts...');
        fetchAndUpdateCounts();
      });

      // Function to update sidebar badges
      function updateSidebarBadges(counts) {
        // Antrian count
        const antrianBadge = document.getElementById('sidebar-antrian-count');
        if (antrianBadge && counts.antrian_count !== undefined) {
          if (counts.antrian_count > 0) {
            antrianBadge.textContent = counts.antrian_count;
            antrianBadge.classList.remove('d-none');
          } else {
            antrianBadge.classList.add('d-none');
          }
        }

        // Pharmacy count
        const pharmacyBadge = document.getElementById('sidebar-pharmacy-count');
        if (pharmacyBadge && counts.pharmacy_pending !== undefined) {
          if (counts.pharmacy_pending > 0) {
            pharmacyBadge.textContent = counts.pharmacy_pending;
            pharmacyBadge.classList.remove('d-none');
          } else {
            pharmacyBadge.classList.add('d-none');
          }
        }

        // Cashier count
        const cashierBadge = document.getElementById('sidebar-cashier-count');
        if (cashierBadge && counts.cashier_unpaid !== undefined) {
          if (counts.cashier_unpaid > 0) {
            cashierBadge.textContent = counts.cashier_unpaid;
            cashierBadge.classList.remove('d-none');
          } else {
            cashierBadge.classList.add('d-none');
          }
        }

        // Register count
        const registerBadge = document.getElementById('sidebar-register-count');
        if (registerBadge && counts.register_count !== undefined) {
          if (counts.register_count > 0) {
            registerBadge.textContent = counts.register_count;
            registerBadge.classList.remove('d-none');
          } else {
            registerBadge.classList.add('d-none');
          }
        }

        // Rawat Jalan count
        const rawatJalanBadge = document.getElementById('sidebar-rawatjalan-count');
        if (rawatJalanBadge && counts.rawat_jalan_count !== undefined) {
          rawatJalanBadge.textContent = counts.rawat_jalan_count;
          if (counts.rawat_jalan_count > 0) {
            rawatJalanBadge.classList.remove('d-none');
          } else {
            rawatJalanBadge.classList.add('d-none');
          }
        }

        // Rawat Inap count
        const rawatInapBadge = document.getElementById('sidebar-rawatinap-count') || document.getElementById('sidebar-rawarinap-count');
        if (rawatInapBadge && counts.rawat_inap_count !== undefined) {
          rawatInapBadge.textContent = counts.rawat_inap_count;
          if (counts.rawat_inap_count > 0) {
            rawatInapBadge.classList.remove('d-none');
          } else {
            rawatInapBadge.classList.add('d-none');
          }
        }
      }

      // Function to fetch counts via API
      function fetchAndUpdateCounts() {
        fetch('/api/sidebar-counts')
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              updateSidebarBadges(data.counts);
            }
          })
          .catch(err => console.error('Failed to fetch counts:', err));
      }
    })();
  </script>

  <!-- Stack Scripts untuk script tambahan dari View -->
  @stack('scripts')
</body>

</html>