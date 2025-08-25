<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- jQuery dulu -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Bootstrap & Feather (jika belum ada) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet" />
    <style>
        /* Hover effect pada nav-link utama */
        .navbar-nav .nav-item .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-bottom: 2px solid #0d6efd;
            transition: all 0.2s ease-in-out;
        }

        /* Aktifkan efek di dropdown menu juga */
        .dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
    </style>
</head>

<body class="bg-transparent">

    <!-- Tambahkan Navbar -->
    <x-navbar />

    <!-- Page Content -->
    @yield('content')

</body>

</html>
<script>
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        link.addEventListener('mouseenter', () => {
            link.style.backgroundColor = 'rgba(255,255,255,0.1)';
            link.style.borderBottom = '2px solid #0d6efd';
        });
        link.addEventListener('mouseleave', () => {
            link.style.backgroundColor = '';
            link.style.borderBottom = '';
        });
    });
</script>
