<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'User Portal') | Time Tracker</title>
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
            --success-color: #198754;
            --warning-color: #ffc107;
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
        
        /* Time Tracker Widget */
        .time-tracker-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--background-color);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 250px;
        }
        
        .timer-display {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-align: center;
            margin: 10px 0;
        }
        
        .timer-controls .btn {
            margin: 0 5px;
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
        
        /* Time Entry Row */
        .time-entry-row {
            border-left: 4px solid var(--success-color);
            margin-bottom: 10px;
            padding: 10px;
            background: var(--background-color);
            border-radius: 5px;
        }
        
        .time-entry-row.manual {
            border-left-color: var(--warning-color);
        }
        
        /* Project Progress */
        .project-progress {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
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
            
            .time-tracker-widget {
                position: relative;
                bottom: auto;
                right: auto;
                margin: 20px 0;
                width: 100%;
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
                <small>User Portal</small>
            </div>
            
            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.time-tracking') }}" class="{{ request()->routeIs('user.time-tracking*') ? 'active' : '' }}">
                        <i class='bx bx-time'></i> Time Tracking
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.projects') }}" class="{{ request()->routeIs('user.projects*') ? 'active' : '' }}">
                        <i class='bx bxs-folder'></i> My Projects
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.time-tracking.reports') }}" class="{{ request()->routeIs('user.time-tracking.reports') ? 'active' : '' }}">
                        <i class='bx bx-chart'></i> Time Reports
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
        </div>
    </div>
    
    <!-- Time Tracker Widget -->
    <div class="time-tracker-widget d-none d-md-block" id="timeTrackerWidget">
        <div class="text-center">
            <div id="timerStatus">
                <div class="timer-display" id="timerDisplay">00:00:00</div>
                <div class="timer-controls">
                    <button class="btn btn-success btn-sm" id="startBtn" onclick="startTimer()">
                        <i class='bx bx-play'></i> Start
                    </button>
                    <button class="btn btn-danger btn-sm d-none" id="stopBtn" onclick="stopTimer()">
                        <i class='bx bx-stop'></i> Stop
                    </button>
                </div>
                <small class="text-muted" id="currentTask"></small>
            </div>
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
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            // Initialize time tracker
            initializeTimeTracker();
        });
        
        let timerInterval;
        let isTracking = false;
        
        function initializeTimeTracker() {
            // Check if there's an active session
            fetch('/user/time-tracking/status')
                .then(response => response.json())
                .then(data => {
                    if (data.hasActiveSession) {
                        startTimerDisplay(data.session);
                    }
                });
        }
        
        function startTimer() {
            // Show modal to get project and task details
            $('#startTimerModal').modal('show');
        }
        
        function startTimeTracking(projectId, taskDescription) {
            fetch('/user/time-tracking/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    project_id: projectId,
                    task_description: taskDescription
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    startTimerDisplay(data.session);
                    $('#startTimerModal').modal('hide');
                } else {
                    alert(data.message || 'Failed to start timer');
                }
            });
        }
        
        function stopTimer() {
            fetch('/user/time-tracking/stop', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    stopTimerDisplay();
                    alert('Timer stopped. Duration: ' + data.duration);
                    // Refresh page to show new entry
                    location.reload();
                }
            });
        }
        
        function startTimerDisplay(session) {
            isTracking = true;
            document.getElementById('startBtn').classList.add('d-none');
            document.getElementById('stopBtn').classList.remove('d-none');
            
            if (session.project) {
                document.getElementById('currentTask').textContent = session.project.name + ' - ' + (session.task_description || 'No description');
            }
            
            // Start the timer display
            const startTime = new Date(session.started_at);
            timerInterval = setInterval(() => {
                const now = new Date();
                const elapsed = Math.floor((now - startTime) / 1000);
                const hours = Math.floor(elapsed / 3600);
                const minutes = Math.floor((elapsed % 3600) / 60);
                const seconds = elapsed % 60;
                
                document.getElementById('timerDisplay').textContent = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }, 1000);
            
            // Ping server every minute
            setInterval(() => {
                if (isTracking) {
                    fetch('/user/time-tracking/ping', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                }
            }, 60000);
        }
        
        function stopTimerDisplay() {
            isTracking = false;
            clearInterval(timerInterval);
            document.getElementById('startBtn').classList.remove('d-none');
            document.getElementById('stopBtn').classList.add('d-none');
            document.getElementById('timerDisplay').textContent = '00:00:00';
            document.getElementById('currentTask').textContent = '';
        }
    </script>
    
    @stack('scripts')
</body>
</html>