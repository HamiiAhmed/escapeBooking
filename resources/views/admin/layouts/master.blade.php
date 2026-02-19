<!doctype html>
<html lang="en">
@php
    $modules = \App\Models\Module::all();
@endphp

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ isset($title) ? $title : 'Escape | Admin' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap' rel='stylesheet' type='text/css'>
    <style>
        body,
        html {
            font-family: 'Montserrat', Arial, sans-serif !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
        integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admincss/adminlte.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
        integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.0/tinymce.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown user-menu">
                        <!-- <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"> -->
                        @if (Auth::user()->profile_pic)
                            <img src="{{ asset('images/users/' . Auth::user()->profile_pic) }}"
                                class="user-image rounded-circle shadow" alt="User Image" />
                        @else
                            <img src="{{ asset('images/users/dummy_profile.webp') }}"
                                class="user-image rounded-circle shadow" alt="User Image" />
                        @endif
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        <!-- </a> -->
                        <!-- <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header">
                                <img
                                    src="{{ asset('images/users/dummy_profile.webp') }}"
                                    class="rounded-circle shadow"
                                    alt="User Image" />
                                <p>
                                    Alexander Pierce - Web Developer
                                    <small>Member since Nov. 2023</small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                                <a href="" class="btn btn-default btn-flat float-end">Sign out</a>
                            </li>
                        </ul> -->
                    </li>
                </ul>
            </div>
        </nav>


        <aside class="app-sidebar bg-maroon shadow" data-bs-theme="dark">
            <div class="sidebar-brand bg-dark" style="border-right: 1px solid #1c1c51;">
                <a href="{{ route('admin.dashboard') }}" class="brand-link">
                    <img src="{{ asset('images/logo.webp') }}" alt="AdminLTE Logo" class="brand-image"
                        style="width: 100px;" />
                    <!-- <span class="brand-text fw-light">AdminLTE 4</span> -->
                </a>
                <!--end::Brand Link-->
            </div>
            <!--end::Sidebar Brand-->
            <!--begin::Sidebar Wrapper-->

            <div class="sidebar-wrapper" data-overlayscrollbars="host">
                <div class="os-size-observer">
                    <div class="os-size-observer-listener"></div>
                </div>
                <div class="" data-overlayscrollbars-viewport="scrollbarHidden overflowXHidden overflowYScroll"
                    tabindex="-1"
                    style="margin-right: -16px; margin-bottom: -16px; margin-left: 0px; top: -8px; right: auto; left: -8px; width: calc(100% + 16px); padding: 8px;">
                    <nav class="mt-2">
                        <!--begin::Sidebar Menu-->
                        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
                            data-accordion="false">

                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}"
                                    class="nav-link {{ request()->is('admin') || request()->is('admin/dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-tachometer-alt"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>

                            <!-- Packages Module -->
                            @can('view', $modules[3])
                                <li class="nav-item">
                                    <a href="{{ route('admin.packages.index') }}"
                                        class="nav-link {{ request()->is('admin/packages*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-box"></i>
                                        <p>Packages</p>
                                    </a>
                                </li>
                            @endcan

                            <!-- Bookings Module -->
                            @can('view', $modules[4])
                                <li class="nav-item">
                                    <a href="{{ route('admin.bookings.index') }}"
                                        class="nav-link {{ request()->is('admin/bookings*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-ticket-alt"></i>
                                        <p>Bookings</p>
                                    </a>
                                </li>
                            @endcan
                            
                            @can('view', $modules[6])
                                <li class="nav-item">
                                    <a href="{{ route('admin.working-hours.index') }}"
                                        class="nav-link {{ request()->is('admin/working-hours*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-clock"></i>
                                        <p>Working Hours</p>
                                    </a>
                                </li>
                            @endcan
                            
                            @can('view', $modules[7])
                                <li class="nav-item">
                                    <a href="{{ route('admin.coupons.index') }}"
                                        class="nav-link {{ request()->is('admin/coupons') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-clock"></i>
                                        <p>Coupons</p>
                                    </a>
                                </li>
                            @endcan

                            <!-- Payments Module -->
                            @can('view', $modules[5])
                                <li class="nav-item">
                                    <a href="{{ route('admin.payments.index') }}"
                                        class="nav-link {{ request()->is('admin/payments*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-credit-card"></i>
                                        <p>Payments</p>
                                    </a>
                                </li>
                            @endcan

                            <!-- Divider -->
                            <li class="nav-header">USER MANAGEMENT</li>

                            <!-- Users Module -->
                            @can('view', $modules[2])
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}"
                                        class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-user"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                            @endcan

                            <!-- Roles & Permissions Module -->
                            @can('view', $modules[1])
                                <li class="nav-item">
                                    <a href="{{ route('admin.roles.index') }}"
                                        class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-user-lock"></i>
                                        <p>Roles & Permissions</p>
                                    </a>
                                </li>
                            @endcan

                            <!-- Divider -->
                            {{-- <li class="nav-header">REPORTS</li>

                            <!-- Reports Module -->
                            @can('view', $modules[0])
                                <li class="nav-item">
                                    <a href="{{ route('admin.reports.index') }}"
                                        class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-chart-bar"></i>
                                        <p>Reports</p>
                                    </a>
                                </li>
                            @endcan --}}

                            <!-- Divider -->
                            <li class="nav-header">ACCOUNT</li>

                            <!-- Profile -->
                            <li class="nav-item">
                                <a href="{{ route('admin.profile') }}"
                                    class="nav-link {{ request()->is('admin/profile') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-user-circle"></i>
                                    <p>Profile</p>
                                </a>
                            </li>

                            <!-- Logout -->
                            <li class="nav-item">
                                <a href="{{ route('admin.logout') }}" class="nav-link">
                                    <i class="nav-icon fa-solid fa-right-from-bracket"></i>
                                    <p>Logout</p>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="os-scrollbar os-scrollbar-horizontal os-theme-light os-scrollbar-auto-hide os-scrollbar-handle-interactive os-scrollbar-track-interactive os-scrollbar-cornerless os-scrollbar-unusable os-scrollbar-auto-hide-hidden"
                    style="--os-viewport-percent: 1; --os-scroll-direction: 0;">
                    <div class="os-scrollbar-track">
                        <div class="os-scrollbar-handle"></div>
                    </div>
                </div>
                <div class="os-scrollbar os-scrollbar-vertical os-theme-light os-scrollbar-auto-hide os-scrollbar-handle-interactive os-scrollbar-track-interactive os-scrollbar-visible os-scrollbar-cornerless os-scrollbar-auto-hide-hidden"
                    style="--os-viewport-percent: 0.2964; --os-scroll-direction: 0;">
                    <div class="os-scrollbar-track">
                        <div class="os-scrollbar-handle"></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="app-main">
            <div class="container mt-2">
                <div class="row">
                    <div class="col-md-12">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        {{-- Show validation errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>
        <footer class="app-footer text-center">
            <strong>
                <!-- Copyright &copy; 2014-2024&nbsp;
                <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>. -->
                Copyright &copy; <?= date('Y') ?> Escape.
            </strong>
            All rights reserved.
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
        integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('js/adminjs/adminlte.js') }}"></script>

    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
        integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
        integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
        integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
        integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY=" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 3000);
        });
    </script>
</body>
<!--end::Body-->

</html>
