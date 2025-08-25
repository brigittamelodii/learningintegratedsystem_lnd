<!-- Header Top -->
<div class="bg-primary text-white d-flex justify-content-between align-items-center px-4" style="height: 60px;">
    <span class="h5 mb-0"><strong>LEARNING INTEGRATED SYSTEM</strong></span>
    <div class="navbar-profile d-flex align-items-center gap-2">
        <a href="#" id="user" class="text-white d-flex align-items-center">
            <i data-feather="user"></i>
        </a>
        <span class="text-white">|</span>
        <form method="POST" action="{{ route('logout') }}" class="d-flex align-items-center mb-0">
            @csrf
            <button type="submit" class="btn btn-light btn-sm py-1 px-2">Log Out</button>
        </form>
    </div>
</div>


<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg bg-black text-white shadow-sm" style="height: 52px;">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="{{ url('dashboard') }}" style="font-size: 14px;"><strong>MY
                DASHBOARD</strong></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <!-- Dropdown menu tetap -->
                @role('manager|superadmin|executive|pic')
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('tna.index') }}">TNA</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('program.index') }}">Program</a>
                    </li>
                @endrole
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('classes.index') }}">Class</a>
                </li>
                @role('manager|superadmin|pic')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Participants</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('participants.create') }}">Upload Participants</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('participants.index') }}">List Participants</a></li>
                        </ul>
                    </li>
                @endrole
                <!-- Bagian Transaction -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('payments.index') }}">Transaction</a>
                </li>
                @role('manager|superadmin|pic|executive')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Certification</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Create Certificate</a></li>
                            <li><a class="dropdown-item" href="#">List Certification</a></li>
                        </ul>
                    </li>
                @endrole
                @role('manager|superadmin|pic|executive')
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('letters.index') }}">Memo</a>
                    </li>
                @endrole
                @role('manager|superadmin|pic|executive')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">E-LAPSE</a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"
                                    onclick="toggleSubmenu(event)">Commitment Letter</a>
                                <ul class="submenu list-unstyled ps-3">
                                    <li><a class="dropdown-item" href="#">CL 1</a></li>
                                    <li><a class="dropdown-item" href="#">CL 2</a></li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"
                                    onclick="toggleSubmenu(event)">E-Statement Letter for Consumer Loan Take Over</a>
                                <ul class="submenu list-unstyled ps-3">
                                    <li><a class="dropdown-item" href="#">ELCLTO 1</a></li>
                                    <li><a class="dropdown-item" href="#">ELCLTO 2</a></li>
                                </ul>
                            </li>

                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"
                                    onclick="toggleSubmenu(event)">Employee Consent (Personal Data Protection Policy)</a>
                                <ul class="submenu list-unstyled ps-3">
                                    <li><a class="dropdown-item" href="#">EC 1</a></li>
                                    <li><a class="dropdown-item" href="#">EC 2</a></li>
                                </ul>
                            </li>

                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"
                                    onclick="toggleSubmenu(event)">E-Statement Letter for Frontliner Services</a>
                                <ul class="submenu list-unstyled ps-3">
                                    <li><a class="dropdown-item" href="#">ELFS 1</a></li>
                                    <li><a class="dropdown-item" href="#">ELFS 2</a></li>
                                </ul>
                            </li>

                        </ul>
                    </li>
                @endrole
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">Task</a>
                    <ul class="dropdown-menu">
                        @role('manager|superadmin|pic|executive')
                            <li><a class="dropdown-item" href="{{ route('tasks.create') }}">Create Task</a></li>
                        @endrole
                        <li><a class="dropdown-item" href="{{ route('tasks.index') }}">List Task</a></li>
                    </ul>
                </li>
                @role('manager|superadmin|executive')
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('monitoring.training-operations') }}">Training
                            Operations
                            Monitoring</a>
                    </li>
                @endrole
                @role('manager|superadmin|executive')
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('letters.index') }}">E-Library</a>
                    </li>
                @endrole

                @role('superadmin')
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('admin.users.index') }}">Users Role</a>
                    </li>
                @endrole
            </ul>
        </div>
    </div>
</nav>
<style>
    /* Umum - desktop view */
    .dropdown-menu {
        background-color: #fff;
    }

    .dropdown-menu .dropdown-item {
        color: #000;
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: #e9ecef;
    }

    /* Responsive - mobile (hamburger mode) */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            position: absolute;
            top: 50px;
            left: 0;
            width: 100%;
            background-color: #000;
            z-index: 1000;
            padding: 1rem;
        }

        .navbar-collapse .nav-link,
        .navbar-collapse .dropdown-item {
            color: white;
        }

        .navbar-collapse .dropdown-menu {
            background-color: #000;
            border: none;
            box-shadow: none;
        }

        .navbar-collapse .dropdown-item:hover {
            background-color: #343a40;
        }
    }
</style>
<script>
    feather.replace();
</script>
<script>
    function toggleSubmenu(e) {
        e.preventDefault();
        e.stopPropagation();
        const submenu = e.target.nextElementSibling;
        submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
    }
</script>
