<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | Time Tracker</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    
    <style>
        :root {
            --primary-color: #FF0000;
            --secondary-color: #000000;
            --background-color: #FFFFFF;
            --light-gray: #f8f9fa;
            --medium-gray: #dee2e6;
            --dark-gray: #6c757d;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            overflow-x: hidden;
            background-color: var(--light-gray);
        }
        
        /* Sidebar */
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background-color: var(--secondary-color);
            color: var(--background-color);
            transition: all 0.3s;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
        }
        
        #sidebar.active {
            margin-left: -250px;
        }
        
        #sidebar .sidebar-header {
            padding: 20px;
            background-color: var(--primary-color);
        }
        
        #sidebar ul.components {
            padding: 20px 0;
        }
        
        #sidebar ul li a {
            padding: 10px 20px;
            font-size: 1.1em;
            display: block;
            color: var(--background-color);
            text-decoration: none;
        }
        
        #sidebar ul li a:hover {
            background-color: rgba(255, 0, 0, 0.1);
        }
        
        #sidebar ul li a.active {
            background-color: var(--primary-color);
            color: var(--background-color);
        }
        
        #sidebar ul li a i {
            margin-right: 10px;
        }
        
        /* Content */
        #content {
            width: calc(100% - 250px);
            min-height: 100vh;
            transition: all 0.3s;
            position: absolute;
            top: 0;
            right: 0;
        }
        
        #content.active {
            width: 100%;
        }
        
        /* Navbar */
        nav.navbar {
            background-color: var(--background-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
        }
        
        .navbar-btn {
            background-color: transparent;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
            font-size: 1.5rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--background-color);
            border-bottom: 1px solid var(--medium-gray);
            font-weight: 500;
        }
        
        /* Stats Cards */
        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        /* Tables */
        .table th {
            border-top: none;
            background-color: var(--light-gray);
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #d90000;
            border-color: #d90000;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--background-color);
        }
        
        /* Footer */
        footer {
            background-color: var(--background-color);
            color: var(--dark-gray);
            text-align: center;
            padding: 15px 0;
            margin-top: 30px;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            
            #sidebar.active {
                margin-left: 0;
            }
            
            #content {
                width: 100%;
            }
            
            #content.active {
                width: calc(100% - 250px);
            }
            
            #sidebarCollapse span {
                display: none;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Time Tracker</h3>
            </div>
            
            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class='bx bxs-user'></i> Users
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                        <i class='bx bxs-folder'></i> Projects
                    </a>
                </li>
                <li>
                    <a href="#tasksSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class='bx bx-task'></i> Tasks
                    </a>
                    <ul class="collapse list-unstyled" id="tasksSubmenu">
                        <li>
                            <a href="#"><i class='bx bx-list-ul'></i> All Tasks</a>
                        </li>
                        <li>
                            <a href="#"><i class='bx bx-kanban'></i> Kanban Board</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class='bx bx-calendar'></i> Calendar
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class='bx bx-money'></i> Invoices
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class='bx bx-cog'></i> Settings
                    </a>
                </li>
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class='bx bx-log-out'></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
        
        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="navbar-btn">
                        <i class='bx bx-menu'></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <a class="dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(auth()->user()->profile_photo)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" class="rounded-circle me-2" width="32" height="32" alt="Profile">
                                @else
                                    <i class='bx bxs-user-circle me-2' style="font-size: 32px;"></i>
                                @endif
                                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#"><i class='bx bxs-user me-2'></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class='bx bx-log-out me-2'></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
            
            <footer>
                <div class="container-fluid">
                    <p>&copy; {{ date('Y') }} Time Tracker. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar, #content').toggleClass('active');
            });
            
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>