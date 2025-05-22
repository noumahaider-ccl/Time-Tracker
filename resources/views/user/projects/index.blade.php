@extends('layouts.user')

@section('title', 'My Projects')

@push('styles')
<style>
    .project-card {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .project-header {
        background: linear-gradient(135deg, var(--primary-color), #d90000);
        color: white;
        padding: 20px;
        position: relative;
    }
    
    .project-status-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-not-started { background-color: rgba(108, 117, 125, 0.9); }
    .status-in-progress { background-color: rgba(13, 110, 253, 0.9); }
    .status-on-hold { background-color: rgba(255, 193, 7, 0.9); }
    .status-completed { background-color: rgba(25, 135, 84, 0.9); }
    .status-cancelled { background-color: rgba(220, 53, 69, 0.9); }
    
    .project-progress {
        height: 6px;
        background-color: rgba(255,255,255,0.3);
        border-radius: 3px;
        overflow: hidden;
        margin-top: 10px;
    }
    
    .project-progress-bar {
        height: 100%;
        background-color: white;
        border-radius: 3px;
        transition: width 0.3s;
    }
    
    .project-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .project-meta {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 5rem;
        color: #ccc;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">My Projects</h1>
            <p class="text-muted">Projects assigned to you</p>
        </div>
    </div>
    
    @if($projects->count() > 0)
        <div class="row">
            @foreach($projects as $project)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card project-card shadow">
                        <div class="project-header">
                            <span class="project-status-badge status-{{ $project->status }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                            <h5 class="mb-2">{{ $project->name }}</h5>
                            <small class="opacity-75">
                                Created {{ $project->created_at->format('M d, Y') }}
                            </small>
                            
                            <!-- Progress Bar (Mock - you can calculate real progress based on tasks) -->
                            <div class="project-progress">
                                @php
                                    $progress = match($project->status) {
                                        'completed' => 100,
                                        'in_progress' => 60,
                                        'on_hold' => 30,
                                        'not_started' => 0,
                                        default => 0
                                    };
                                @endphp
                                <div class="project-progress-bar" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <div class="project-body">
                            <p class="text-muted mb-3">
                                {{ $project->description ? Str::limit($project->description, 120) : 'No description available.' }}
                            </p>
                            
                            @if($project->manager)
                                <div class="d-flex align-items-center mb-3">
                                    @if($project->manager->profile_photo)
                                        <img src="{{ asset('storage/' . $project->manager->profile_photo) }}" 
                                             class="rounded-circle me-2" width="24" height="24" alt="{{ $project->manager->name }}">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                             style="width: 24px; height: 24px; font-size: 12px;">
                                            {{ strtoupper(substr($project->manager->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <small class="text-muted">
                                        <strong>Manager:</strong> {{ $project->manager->name }}
                                    </small>
                                </div>
                            @endif
                            
                            <div class="project-meta">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary">{{ $progress }}%</div>
                                        <small class="text-muted">Complete</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-success">0</div>
                                        <small class="text-muted">Tasks</small>
                                    </div>
                                    <div class="col-4">
                                        @if($project->end_date)
                                            @php
                                                $daysLeft = now()->diffInDays($project->end_date, false);
                                                $textClass = $daysLeft < 0 ? 'text-danger' : ($daysLeft < 7 ? 'text-warning' : 'text-info');
                                            @endphp
                                            <div class="fw-bold {{ $textClass }}">{{ abs($daysLeft) }}</div>
                                            <small class="text-muted">{{ $daysLeft < 0 ? 'Overdue' : 'Days left' }}</small>
                                        @else
                                            <div class="fw-bold text-muted">--</div>
                                            <small class="text-muted">No deadline</small>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mt-3 d-grid">
                                    <a href="{{ route('user.projects.show', $project) }}" class="btn btn-primary">
                                        <i class='bx bx-show'></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $projects->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-folder-open'></i>
            <h4>No Projects Assigned</h4>
            <p class="text-muted">You don't have any projects assigned to you yet. Check back later or contact your project manager.</p>
        </div>
    @endif
</div>
@endsection