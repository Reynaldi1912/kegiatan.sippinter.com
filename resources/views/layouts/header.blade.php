<style>
    /* ============================================================
       DASH / SIPPINTER - DEEP NAVY TOPBAR (SCREENSHOT 2 THEME)
       ============================================================ */
    .custom-header-navbar {
        background: #1f398a !important;
        background: linear-gradient(90deg, #1f398a 0%, #1e3578 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06) !important;
        border-radius: 16px !important;
        margin-top: 0.75rem !important;
        margin-bottom: 1.25rem !important;
        padding: 0.6rem 1.25rem !important;
        min-height: 56px !important;
    }

    /* Mobile toggle */
    .btn-nav-sidebar-toggle {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        padding: 0 !important;
    }
    .btn-nav-sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.22) !important;
    }

    /* Search Input (Screenshot 2 style) */
    .dash-search-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 0.35rem 0.95rem;
        width: 260px;
        transition: all 0.2s ease;
    }
    .dash-search-container:focus-within {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.35);
        width: 320px;
    }
    .dash-search-container i {
        color: rgba(255, 255, 255, 0.6);
        font-size: 1rem;
    }
    .dash-search-input {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #ffffff !important;
        font-size: 0.85rem !important;
        width: 100% !important;
    }
    .dash-search-input::placeholder {
        color: rgba(255, 255, 255, 0.55) !important;
    }

    /* Topbar action icons (Bell & 3-dots) */
    .dash-icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        transition: all 0.2s ease;
        text-decoration: none !important;
        position: relative;
    }
    .dash-icon-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    .dash-notification-dot {
        position: absolute;
        top: 7px;
        right: 8px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #ef4444;
        box-shadow: 0 0 0 2px #1f398a;
    }

    /* User Avatar Pill */
    .dash-avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.8);
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        cursor: pointer;
    }

    /* Dropdown menu styling */
    .dash-header-dropdown {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12) !important;
        padding: 0.65rem !important;
        min-width: 240px;
        margin-top: 0.75rem !important;
    }

    .dash-header-dropdown .dropdown-item {
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.55rem 0.85rem;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        transition: all 0.15s ease;
    }

    .dash-header-dropdown .dropdown-item:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .dash-header-dropdown .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }
</style>

@php
    $user = Auth::user();
    $initials = $user ? strtoupper(substr($user->fullname ?? $user->username ?? $user->name, 0, 2)) : 'DR';
    $unreadNotifCount = $user ? \App\Models\NotifikasiPegawai::query()->where('user_id', $user->id)->where('is_read', false)->count() : 0;
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center custom-header-navbar" id="layout-navbar">
    
    <!-- Sidebar Toggle (Mobile & Desktop) -->
    <div class="navbar-nav align-items-center me-3">
        <button type="button" class="btn-nav-sidebar-toggle" id="btnToggleSidebar" title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <i class="bx bx-menu fs-5"></i>
        </button>
    </div>

    <!-- Search Bar (Screenshot 2 style) -->
    <div class="navbar-nav align-items-center me-auto">
        <div class="dash-search-container">
            <i class="bx bx-search"></i>
            <input type="text" class="dash-search-input" placeholder="Search" aria-label="Search">
        </div>
    </div>

    <!-- Right Side: Notification, User Avatar, 3-dots Menu -->
    <div class="d-flex align-items-center gap-3 ms-auto">
        <!-- Notification Bell -->
        <a href="{{ route('notifikasi.index') }}" class="dash-icon-btn" title="Notifikasi">
            <i class="bx bx-bell fs-5"></i>
            @if($unreadNotifCount > 0)
                <span class="dash-notification-dot"></span>
            @endif
        </a>

        <!-- User Profile Avatar Pill -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('profile.edit') }}" title="{{ $user->name ?? 'User Profile' }}">
                <div class="dash-avatar-circle">
                    {{ $initials }}
                </div>
            </a>
        </div>

        <!-- 3-Dots Vertical Options Menu (Screenshot 2 style) -->
        <div class="dropdown">
            <button class="dash-icon-btn border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi">
                <i class="bx bx-dots-vertical-rounded fs-5"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dash-header-dropdown">
                <li class="px-3 py-2 border-bottom mb-2">
                    <span class="d-block fw-bold text-dark text-truncate" style="font-size: 0.88rem;">{{ $user->name ?? 'User' }}</span>
                    <span class="d-block text-muted small text-truncate">{{ $user->jabatan ?? $user->email ?? 'Pegawai' }}</span>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bx bx-user text-primary"></i> Profil Saya
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('dashboard', ['scope' => 'puskesmas']) }}">
                        <i class="bx bx-buildings text-info"></i> Seluruh Puskesmas
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('dashboard', ['scope' => 'saya']) }}">
                        <i class="bx bx-check-circle text-success"></i> Penugasan Saya
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start">
                            <i class="bx bx-log-out text-danger"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
