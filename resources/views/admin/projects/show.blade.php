@extends('layouts.admin')

@section('title', 'Project Details')

@push('styles')
<style>
    .project-header {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
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
    
    .project-description {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .timeline-item {
        padding-left: 20px;
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: 100%;
        background-color: #dee2e6;
    }
    
    .timeline-item::after {
        content: '';
        position: absolute;
        left: -4px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #0d6efd;
    }
    
    .timeline-item:last-child::before {
        height: 0;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .user-initial {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3">Project Details</h1>
        <div>
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-warning me-2">
                <i class='bx bx-edit'></i> Edit Project
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary">
                <i class='bx bx-arrow-back'></i> Back to Projects
            </a>
        </div>
    </div>
    
    <div class="project-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>{{ $project->name }}</h2>
                <div class="d-flex align-items-center mb-2">
                    <span class="project-status status-{{ $project->status }}"></span>
                    <span class="me-3">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                    
                    @php
                        $priorityClass = [
                            'low' => 'bg-success',
                            'medium' => 'bg-info',
                            'high' => 'bg-warning',
                            'urgent' => 'bg-danger'
                        ][$project->priority] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $priorityClass }} priority-badge">
                        {{ ucfirst($project->priority) }} Priority
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="mb-2">
                    <strong>Created:</strong> {{ $project->created_at->format('M d, Y') }}
                </div>
                @if($project->budget)
                    <div class="mb-2">
                        <strong>Budget:</strong> ${{ number_format($project->budget, 2) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Project Description -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Project Description</h6>
                </div>
                <div class="card-body">
                    @if($project->description)
                        <p>{{ $project->description }}</p>
                    @else
                        <p class="text-muted">No description provided for this project.</p>
                    @endif
                </div>
            </div>
            
            <!-- Project Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Project Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <h6 class="mb-1">Project Created</h6>
                            <p class="text-muted mb-0">{{ $project->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                        
                        @if($project->start_date)
                            <div class="timeline-item">
                                <h6 class="mb-1">Project Started</h6>
                                <p class="text-muted mb-0">{{ $project->start_date->format('F d, Y') }}</p>
                            </div>
                        @endif
                        
                        @if($project->end_date)
                            <div class="timeline-item">
                                <h6 class="mb-1">Project Deadline</h6>
                                <p class="text-muted mb-0">{{ $project->end_date->format('F d, Y') }}</p>
                                @if($project->end_date->isPast() && $project->status !== 'completed')
                                    <span class="badge bg-danger">Overdue</span>
                                @elseif($project->end_date->diffInDays(now()) <= 7 && $project->status !== 'completed')
                                    <span class="badge bg-warning">Due Soon</span>
                                @endif
                            </div>
                        @endif
                        
                        @if($project->status === 'completed')
                            <div class="timeline-item">
                                <h6 class="mb-1">Project Completed</h6>
                                <p class="text-muted mb-0">{{ $project->updated_at->format('F d, Y h:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Tasks (Placeholder for future implementation) -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Tasks</h6>
                    <button class="btn btn-sm btn-primary" disabled>
                        <i class='bx bx-plus'></i> Add Task
                    </button>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class='bx bx-task' style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">Task Management Coming Soon</h5>
                        <p class="text-muted">This feature is currently under development.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Client Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Client Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($project->client->profile_photo)
                            <img src="{{ asset('storage/' . $project->client->profile_photo) }}" alt="{{ $project->client->name }}" class="user-avatar me-3">
                        @else
                            <div class="user-initial me-3 bg-success">
                                {{ strtoupper(substr($project->client->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $project->client->name }}</h6>
                            <a href="{{ route('admin.users.show', $project->client) }}" class="small">View Profile</a>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Email:</strong> {{ $project->client->email }}
                    </div>
                    
                    @if($project->client->phone)
                        <div class="mb-2">
                            <strong>Phone:</strong> {{ $project->client->phone }}
                        </div>
                    @endif
                    
                    @if($project->client->company)
                        <div class="mb-2">
                            <strong>Company:</strong> {{ $project->client->company }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Project Manager Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Project Manager</h6>
                    @if(!$project->manager)
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">
                            <i class='bx bx-user-plus'></i> Assign
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($project->manager)
                        <div class="d-flex align-items-center mb-3">
                            @if($project->manager->profile_photo)
                                <img src="{{ asset('storage/' . $project->manager->profile_photo) }}" alt="{{ $project->manager->name }}" class="user-avatar me-3">
                            @else
                                <div class="user-initial me-3 bg-primary">
                                    {{ strtoupper(substr($project->manager->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $project->manager->name }}</h6>
                                <a href="{{ route('admin.users.show', $project->manager) }}" class="small">View Profile</a>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Email:</strong> {{ $project->manager->email }}
                        </div>
                        
                        @if($project->manager->phone)
                            <div class="mb-2">
                                <strong>Phone:</strong> {{ $project->manager->phone }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class='bx bx-user-x' style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No project manager assigned yet.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Project Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Project Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Project Progress</span>
                            <span>
                                @if($project->status === 'completed')
                                    100%
                                @elseif($project->status === 'in_progress')
                                    50%
                                @elseif($project->status === 'on_hold')
                                    25%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $project->status === 'completed' ? '100' : ($project->status === 'in_progress' ? '50' : ($project->status === 'on_hold' ? '25' : '0')) }}%" aria-valuenow="{{ $project->status === 'completed' ? '100' : ($project->status === 'in_progress' ? '50' : ($project->status === 'on_hold' ? '25' : '0')) }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h5>0</h5>
                            <span class="text-muted small">Total Tasks</span>
                        </div>
                        <div class="col-6 mb-3">
                            <h5>0</h5>
                            <span class="text-muted small">Completed Tasks</span>
                        </div>
                        <div class="col-6 mb-3">
                            <h5>
                                @if($project->end_date && $project->end_date->isFuture())
                                    {{ now()->diffInDays($project->end_date) }}
                                @else
                                    0
                                @endif
                            </h5>
                            <span class="text-muted small">Days Remaining</span>
                        </div>
                        <div class="col-6 mb-3">
                            <h5>{{ $project->created_at->diffInDays(now()) + 1 }}</h5>
                            <span class="text-muted small">Days Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection