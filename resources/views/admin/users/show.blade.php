@extends('layouts.admin')

@section('title', 'User Details')

@push('styles')
<style>
    .user-info {
        padding: 20px;
        border-radius: 10px;
        background-color: #f8f9fa;
    }
    
    .user-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .user-initial {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #fff;
        background-color: #6c757d;
        border: 3px solid #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .role-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    
    .info-title {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .info-value {
        margin-bottom: 15px;
    }
    
    .project-card {
        transition: transform 0.3s;
    }
    
    .project-card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3">User Details</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
                <i class='bx bx-edit'></i> Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                <i class='bx bx-arrow-back'></i> Back to Users
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="user-avatar mb-3">
                    @else
                        <div class="user-initial mb-3 mx-auto">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    
                    @php
                        $roleClass = [
                            'admin' => 'bg-danger',
                            'project_manager' => 'bg-primary',
                            'client' => 'bg-success'
                        ][$user->role->name] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $roleClass }} role-badge">
                        {{ ucfirst(str_replace('_', ' ', $user->role->name)) }}
                    </span>
                    
                    <hr>
                    
                    <div class="text-start">
                        @if($user->company)
                            <div class="mb-3">
                                <div class="info-title">Company</div>
                                <div class="info-value">{{ $user->company }}</div>
                            </div>
                        @endif
                        
                        @if($user->phone)
                            <div class="mb-3">
                                <div class="info-title">Phone</div>
                                <div class="info-value">{{ $user->phone }}</div>
                            </div>
                        @endif
                        
                        @if($user->address)
                            <div class="mb-3">
                                <div class="info-title">Address</div>
                                <div class="info-value">{{ $user->address }}</div>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <div class="info-title">Account Created</div>
                            <div class="info-value">{{ $user->created_at->format('F d, Y') }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="info-title">Last Login</div>
                            <div class="info-value">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('F d, Y h:i A') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            @if($user->isClient() && count($clientProjects) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Projects as Client</h6>
                        <span class="badge bg-info">{{ count($clientProjects) }} Projects</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($clientProjects as $project)
                                <div class="col-md-6 mb-3">
                                    <div class="card project-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                                            </h5>
                                            <p class="card-text text-muted small">
                                                {{ Str::limit($project->description, 100) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in_progress' ? 'primary' : ($project->status === 'on_hold' ? 'warning' : 'secondary')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $project->start_date->format('M d, Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            
            @if($user->isProjectManager() && count($managedProjects) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Projects as Manager</h6>
                        <span class="badge bg-primary">{{ count($managedProjects) }} Projects</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($managedProjects as $project)
                                <div class="col-md-6 mb-3">
                                    <div class="card project-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                                            </h5>
                                            <p class="card-text text-muted small">
                                                {{ Str::limit($project->description, 100) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in_progress' ? 'primary' : ($project->status === 'on_hold' ? 'warning' : 'secondary')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                </span>
                                                <small class="text-muted">
                                                    Client: {{ $project->client->name }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            
            @if((!$user->isClient() || count($clientProjects) === 0) && (!$user->isProjectManager() || count($managedProjects) === 0))
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class='bx bx-folder-open' style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">No Projects Found</h5>
                        <p class="text-muted">This user is not associated with any projects yet.</p>
                        
                        @if($user->isClient())
                            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                                <i class='bx bx-plus'></i> Create Project for this Client
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection