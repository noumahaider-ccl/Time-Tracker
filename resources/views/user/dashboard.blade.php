@extends('layouts.user')

@section('title', 'User Dashboard')

@push('styles')
<style>
    .stats-card {
        transition: transform 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .project-card {
        transition: transform 0.3s;
        height: 100%;
    }
    
    .project-card:hover {
        transform: translateY(-3px);
    }
    
    .time-entry-item {
        border-left: 4px solid var(--success-color);
        padding: 12px;
        background: white;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    .time-entry-item.manual {
        border-left-color: var(--warning-color);
    }
    
    .project-status {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .status-not-started { background-color: #6c757d; }
    .status-in-progress { background-color: #0d6efd; }
    .status-on-hold { background-color: #ffc107; }
    .status-completed { background-color: #198754; }
    .status-cancelled { background-color: #dc3545; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-muted">Here's your productivity overview</p>
        </div>
        <div>
            @if($activeSession)
                <div class="alert alert-info d-flex align-items-center">
                    <i class='bx bx-time me-2'></i>
                    <span>Time tracking active: {{ $activeSession->project->name ?? 'No project' }}</span>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Today's Hours</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['todayHours'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-time fa-2x text-primary"></i>
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
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Week's Hours</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['weekHours'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-calendar-week fa-2x text-success"></i>
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
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Month's Hours</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['monthHours'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-calendar fa-2x text-info"></i>
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
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Active Projects</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['activeProjects'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-folder fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Projects -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">My Projects</h6>
                    <a href="{{ route('user.projects') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($projects->count() > 0)
                        <div class="row">
                            @foreach($projects->take(4) as $project)
                                <div class="col-md-6 mb-3">
                                    <div class="card project-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">{{ $project->name }}</h6>
                                                <span class="project-status status-{{ $project->status }}"></span>
                                            </div>
                                            <p class="card-text text-muted small mb-2">
                                                {{ Str::limit($project->description, 80) }}
                                            </p>
                                            
                                            @if($project->manager)
                                                <small class="text-muted">
                                                    <i class='bx bx-user'></i> {{ $project->manager->name }}
                                                </small>
                                            @endif
                                            
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Status: {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                </small>
                                            </div>
                                            
                                            @if($project->end_date)
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class='bx bx-calendar'></i> Due: {{ $project->end_date->format('M d, Y') }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="mt-2">
                                                <a href="{{ route('user.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class='bx bx-folder-open' style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No projects assigned to you yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Time Breakdown & Recent Entries -->
        <div class="col-lg-4">
            <!-- Project Time Breakdown -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">This Month's Project Time</h6>
                </div>
                <div class="card-body">
                    @if($projectTimeBreakdown->count() > 0)
                        @foreach($projectTimeBreakdown as $projectName => $minutes)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">{{ Str::limit($projectName ?: 'No Project', 20) }}</span>
                                <span class="badge bg-primary">{{ round($minutes / 60, 1) }}h</span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ $projectTimeBreakdown->max() > 0 ? ($minutes / $projectTimeBreakdown->max()) * 100 : 0 }}%">
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No time logged this month</p>
                    @endif
                </div>
            </div>
            
            <!-- Recent Time Entries -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Recent Time Entries</h6>
                    <a href="{{ route('user.time-tracking') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentEntries->count() > 0)
                        @foreach($recentEntries as $entry)
                            <div class="time-entry-item {{ $entry->entry_type }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $entry->task_description ?: 'No description' }}</h6>
                                        <small class="text-muted">
                                            {{ $entry->project->name ?? 'No project' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success">{{ $entry->formatted_duration }}</span>
                                        <br>
                                        <small class="text-muted">{{ $entry->work_date->format('M d') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class='bx bx-time' style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No time entries yet</p>
                            <a href="{{ route('user.time-tracking') }}" class="btn btn-sm btn-primary">Start Tracking</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start Timer Modal -->
<div class="modal fade" id="startTimerModal" tabindex="-1" aria-labelledby="startTimerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="startTimerModalLabel">Start Time Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="startTimerForm">
                    <div class="mb-3">
                        <label for="project_select" class="form-label">Project (Optional)</label>
                        <select class="form-select" id="project_select" name="project_id">
                            <option value="">No specific project</option>
                            @foreach($projects->where('status', '!=', 'completed') as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="task_description" class="form-label">What are you working on?</label>
                        <input type="text" class="form-control" id="task_description" name="task_description" 
                               placeholder="Enter task description..." required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="startTimeTracking(document.getElementById('project_select').value, document.getElementById('task_description').value)">
                    <i class='bx bx-play'></i> Start Timer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection