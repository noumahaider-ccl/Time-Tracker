@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .stats-card {
        transition: transform 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.8rem;
    }
    
    .project-status {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .status-not-started {
        background-color: #6c757d;
    }
    
    .status-in-progress {
        background-color: #0d6efd;
    }
    
    .status-on-hold {
        background-color: #ffc107;
    }
    
    .status-completed {
        background-color: #198754;
    }
    
    .status-cancelled {
        background-color: #dc3545;
    }
    
    .priority-badge {
        text-transform: uppercase;
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Projects</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['totalProjects'] }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bx bxs-folder"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Clients</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['totalClients'] }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-success bg-opacity-10 text-success">
                                <i class="bx bxs-user"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Project Managers</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['totalManagers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-info bg-opacity-10 text-info">
                                <i class="bx bxs-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Completed Projects</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['completedProjects'] }}</div>
                            <div class="text-xs text-muted">{{ $stats['totalProjects'] > 0 ? round(($stats['completedProjects'] / $stats['totalProjects']) * 100) : 0 }}% Completion Rate</div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bx bxs-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Project Status Distribution -->
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Project Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="p-3">
                                <span class="d-block font-weight-bold">{{ $stats['notStartedProjects'] }}</span>
                                <span class="text-sm text-muted">Not Started</span>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3">
                                <span class="d-block font-weight-bold">{{ $stats['inProgressProjects'] }}</span>
                                <span class="text-sm text-muted">In Progress</span>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3">
                                <span class="d-block font-weight-bold">{{ $stats['onHoldProjects'] }}</span>
                                <span class="text-sm text-muted">On Hold</span>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3">
                                <span class="d-block font-weight-bold">{{ $stats['completedProjects'] }}</span>
                                <span class="text-sm text-muted">Completed</span>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $total = $stats['totalProjects'] > 0 ? $stats['totalProjects'] : 1;
                        $notStartedPercent = ($stats['notStartedProjects'] / $total) * 100;
                        $inProgressPercent = ($stats['inProgressProjects'] / $total) * 100;
                        $onHoldPercent = ($stats['onHoldProjects'] / $total) * 100;
                        $completedPercent = ($stats['completedProjects'] / $total) * 100;
                    @endphp
                    
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $notStartedPercent }}%" aria-valuenow="{{ $notStartedPercent }}" aria-valuemin="0" aria-valuemax="100" title="Not Started: {{ $notStartedPercent }}%"></div>
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $inProgressPercent }}%" aria-valuenow="{{ $inProgressPercent }}" aria-valuemin="0" aria-valuemax="100" title="In Progress: {{ $inProgressPercent }}%"></div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $onHoldPercent }}%" aria-valuenow="{{ $onHoldPercent }}" aria-valuemin="0" aria-valuemax="100" title="On Hold: {{ $onHoldPercent }}%"></div>
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completedPercent }}%" aria-valuenow="{{ $completedPercent }}" aria-valuemin="0" aria-valuemax="100" title="Completed: {{ $completedPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Top Clients</h6>
                </div>
                <div class="card-body">
                    @if($topClients->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($topClients as $client)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="font-weight-bold">{{ $client->name }}</span>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $client->project_count }} Projects</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted my-4">No clients with projects yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Projects & Upcoming Deadlines -->
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Recent Projects</h6>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentProjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentProjects as $project)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                                            </td>
                                            <td>{{ $project->client->name }}</td>
                                            <td>
                                                <span class="project-status status-{{ $project->status }}"></span>
                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </td>
                                            <td>{{ $project->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted my-4">No projects created yet.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Upcoming Deadlines</h6>
                </div>
                <div class="card-body">
                    @if($upcomingDeadlines->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Client</th>
                                        <th>Deadline</th>
                                        <th>Priority</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingDeadlines as $project)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                                            </td>
                                            <td>{{ $project->client->name }}</td>
                                            <td>
                                                {{ $project->end_date->format('M d, Y') }}
                                                <small class="text-muted d-block">
                                                    {{ $project->end_date->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                @php
                                                    $priorityClass = [
                                                        'low' => 'bg-success',
                                                        'medium' => 'bg-info',
                                                        'high' => 'bg-warning',
                                                        'urgent' => 'bg-danger'
                                                    ][$project->priority] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $priorityClass }} priority-badge">
                                                    {{ ucfirst($project->priority) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted my-4">No upcoming deadlines in the next 7 days.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush