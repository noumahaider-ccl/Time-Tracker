@extends('layouts.user')

@section('title', 'Project Details')

@push('styles')
<style>
    .project-hero {
        background: linear-gradient(135deg, var(--primary-color), #d90000);
        color: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .project-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        animation: float 20s infinite linear;
    }
    
    @keyframes float {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    
    .project-status-badge {
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .status-not-started { background-color: rgba(108, 117, 125, 0.9); }
    .status-in-progress { background-color: rgba(13, 110, 253, 0.9); }
    .status-on-hold { background-color: rgba(255, 193, 7, 0.9); color: #000; }
    .status-completed { background-color: rgba(25, 135, 84, 0.9); }
    .status-cancelled { background-color: rgba(220, 53, 69, 0.9); }
    
    .time-entry-card {
        border-left: 4px solid var(--success-color);
        transition: transform 0.2s;
        margin-bottom: 15px;
    }
    
    .time-entry-card:hover {
        transform: translateX(5px);
    }
    
    .time-entry-card.manual {
        border-left-color: var(--warning-color);
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: 100%;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .stat-label {
        color: var(--dark-gray);
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('user.projects') }}">My Projects</a></li>
                <li class="breadcrumb-item active">{{ $project->name }}</li>
            </ol>
        </nav>
        <a href="{{ route('user.projects') }}" class="btn btn-outline-primary">
            <i class='bx bx-arrow-back'></i> Back to Projects
        </a>
    </div>
    
    <!-- Project Hero Section -->
    <div class="project-hero">
        <div class="row align-items-center position-relative">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <h1 class="h2 mb-0 me-3">{{ $project->name }}</h1>
                    <span class="project-status-badge status-{{ $project->status }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>
                
                @if($project->description)
                    <p class="lead mb-3 opacity-90">{{ $project->description }}</p>
                @endif
                
                <div class="row">
                    <div class="col-auto">
                        <small class="opacity-75">
                            <i class='bx bx-calendar'></i> Started: {{ $project->start_date->format('M d, Y') }}
                        </small>
                    </div>
                    @if($project->end_date)
                        <div class="col-auto">
                            <small class="opacity-75">
                                <i class='bx bx-flag'></i> Deadline: {{ $project->end_date->format('M d, Y') }}
                            </small>
                        </div>
                    @endif
                    @if($project->budget)
                        <div class="col-auto">
                            <small class="opacity-75">
                                <i class='bx bx-dollar'></i> Budget: ${{ number_format($project->budget, 2) }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="col-md-4 text-end">
                @if($project->manager)
                    <div class="d-flex align-items-center justify-content-end">
                        @if($project->manager->profile_photo)
                            <img src="{{ asset('storage/' . $project->manager->profile_photo) }}" 
                                 class="rounded-circle me-2" width="40" height="40" alt="{{ $project->manager->name }}">
                        @else
                            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center me-2" 
                                 style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($project->manager->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="text-start">
                            <div class="fw-bold">{{ $project->manager->name }}</div>
                            <small class="opacity-75">Project Manager</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Project Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ round($totalTime / 60, 1) }}</div>
                <div class="stat-label">Total Hours Logged</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $timeEntries->count() }}</div>
                <div class="stat-label">Time Entries</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                @php
                    $progress = match($project->status) {
                        'completed' => 100,
                        'in_progress' => 60,
                        'on_hold' => 30,
                        'not_started' => 0,
                        default => 0
                    };
                @endphp
                <div class="stat-number">{{ $progress }}%</div>
                <div class="stat-label">Progress</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                @if($project->end_date)
                    @php
                        $daysLeft = now()->diffInDays($project->end_date, false);
                    @endphp
                    <div class="stat-number {{ $daysLeft < 0 ? 'text-danger' : ($daysLeft < 7 ? 'text-warning' : '') }}">
                        {{ abs($daysLeft) }}
                    </div>
                    <div class="stat-label">{{ $daysLeft < 0 ? 'Days Overdue' : 'Days Remaining' }}</div>
                @else
                    <div class="stat-number text-muted">--</div>
                    <div class="stat-label">No Deadline Set</div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Time Tracking Section -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Recent Time Entries</h6>
                    <a href="{{ route('user.time-tracking') }}" class="btn btn-sm btn-primary">
                        <i class='bx bx-time'></i> Track Time
                    </a>
                </div>
                <div class="card-body">
                    @if($timeEntries->count() > 0)
                        @foreach($timeEntries as $entry)
                            <div class="card time-entry-card {{ $entry->entry_type }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-1">{{ $entry->task_description ?: 'No description' }}</h6>
                                            <small class="text-muted">
                                                {{ $entry->work_date->format('M d, Y') }}
                                                @if($entry->entry_type === 'manual')
                                                    <span class="badge bg-warning ms-1">Manual</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="fw-bold">{{ $entry->formatted_duration }}</div>
                                            <small class="text-muted">
                                                {{ $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '--' }} - 
                                                {{ $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '--' }}
                                            </small>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <span class="badge bg-success">{{ $entry->duration_hours }}h</span>
                                        </div>
                                    </div>
                                    
                                    @if($entry->notes)
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <small class="text-muted">
                                                    <i class='bx bx-note'></i> {{ $entry->notes }}
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        
                        @if($timeEntries->count() >= 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('user.time-tracking.reports') }}?project_id={{ $project->id }}" class="btn btn-outline-primary">
                                    View All Time Entries
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class='bx bx-time' style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3">No time logged yet</h5>
                            <p class="text-muted">Start tracking time for this project</p>
                            <a href="{{ route('user.time-tracking') }}" class="btn btn-primary">
                                <i class='bx bx-play'></i> Start Tracking
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Project Details Sidebar -->
        <div class="col-lg-4">
            <!-- Project Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Project Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <div>
                            <span class="badge bg-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in_progress' ? 'primary' : ($project->status === 'on_hold' ? 'warning' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Priority</label>
                        <div>
                            @php
                                $priorityClass = [
                                    'low' => 'bg-success',
                                    'medium' => 'bg-info',
                                    'high' => 'bg-warning',
                                    'urgent' => 'bg-danger'
                                ][$project->priority] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $priorityClass }}">
                                {{ ucfirst($project->priority) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Timeline</label>
                        <div>
                            <small class="text-muted">
                                <strong>Start:</strong> {{ $project->start_date->format('M d, Y') }}<br>
                                @if($project->end_date)
                                    <strong>End:</strong> {{ $project->end_date->format('M d, Y') }}
                                @else
                                    <strong>End:</strong> Not set
                                @endif
                            </small>
                        </div>
                    </div>
                    
                    @if($project->budget)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Budget</label>
                            <div class="h5 text-success">${{ number_format($project->budget, 2) }}</div>
                        </div>
                    @endif
                    
                    <div class="mb-0">
                        <label class="form-label fw-bold">Created</label>
                        <div>
                            <small class="text-muted">{{ $project->created_at->format('F d, Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.time-tracking') }}" class="btn btn-primary">
                            <i class='bx bx-play'></i> Start Time Tracking
                        </a>
                        <a href="{{ route('user.time-tracking.reports') }}?project_id={{ $project->id }}" class="btn btn-outline-info">
                            <i class='bx bx-chart'></i> View Time Reports
                        </a>
                        @if($project->manager)
                            <button class="btn btn-outline-secondary" onclick="alert('Chat feature coming soon!')">
                                <i class='bx bx-chat'></i> Message Manager
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection