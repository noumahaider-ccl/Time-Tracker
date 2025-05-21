@extends('layouts.admin')

@section('title', 'Manage Projects')

@push('styles')
<style>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Manage Projects</h1>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <i class='bx bx-plus'></i> Add New Project
        </a>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Project List</h6>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3 mb-2">
                    <form action="{{ route('admin.projects.index') }}" method="GET">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $key => $value)
                                <option value="{{ $key }}" {{ $statusFilter == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-3 mb-2">
                    <form action="{{ route('admin.projects.index') }}" method="GET">
                        <select name="client_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ $clientFilter == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-3 mb-2">
                    <form action="{{ route('admin.projects.index') }}" method="GET">
                        <select name="manager_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Managers</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ $managerFilter == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-3 mb-2">
                    <form action="{{ route('admin.projects.index') }}" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search projects..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class='bx bx-search'></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>Client</th>
                            <th>Project Manager</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Timeline</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $project->id }}</td>
                                <td>
                                    <a href="{{ route('admin.projects.show', $project) }}" class="fw-bold">
                                        {{ $project->name }}
                                    </a>
                                    <small class="d-block text-muted">
                                        {{ Str::limit($project->description, 50) }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($project->client->profile_photo)
                                            <img src="{{ asset('storage/' . $project->client->profile_photo) }}" class="rounded-circle me-2" width="24" height="24" alt="{{ $project->client->name }}">
                                        @else
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                                {{ strtoupper(substr($project->client->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        {{ $project->client->name }}
                                    </div>
                                </td>
                                <td>
                                    @if($project->manager)
                                        <div class="d-flex align-items-center">
                                            @if($project->manager->profile_photo)
                                                <img src="{{ asset('storage/' . $project->manager->profile_photo) }}" class="rounded-circle me-2" width="24" height="24" alt="{{ $project->manager->name }}">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                                    {{ strtoupper(substr($project->manager->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            {{ $project->manager->name }}
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="project-status status-{{ $project->status }}"></span>
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
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
                                <td>
                                    <small>
                                        <strong>Start:</strong> {{ $project->start_date->format('M d, Y') }}<br>
                                        @if($project->end_date)
                                            <strong>End:</strong> {{ $project->end_date->format('M d, Y') }}
                                        @else
                                            <strong>End:</strong> <span class="text-muted">Not set</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                            <i class='bx bx-show'></i>
                                        </a>
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $project->id }}" title="Delete">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal-{{ $project->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $project->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel-{{ $project->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the project <strong>{{ $project->name }}</strong>? This action cannot be undone and will delete all associated tasks, milestones, and documents.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('admin.projects.destroy', $project) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No projects found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</div>
@endsection