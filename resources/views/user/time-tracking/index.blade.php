@extends('layouts.user')

@section('title', 'Time Tracking')

@push('styles')
<style>
    .time-tracker-main {
        background: linear-gradient(135deg, var(--primary-color), #d90000);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .time-display {
        font-family: 'Courier New', monospace;
        font-size: 3rem;
        font-weight: bold;
        margin: 20px 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .time-entry-card {
        border-left: 4px solid var(--success-color);
        margin-bottom: 15px;
        transition: transform 0.2s;
    }
    
    .time-entry-card:hover {
        transform: translateX(5px);
    }
    
    .time-entry-card.manual {
        border-left-color: var(--warning-color);
    }
    
    .week-overview {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .day-card {
        background: white;
        border-radius: 10px;
        padding: 15px 10px;
        text-align: center;
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    
    .day-card.today {
        border-color: var(--primary-color);
        background: rgba(255, 0, 0, 0.05);
    }
    
    .day-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .day-name {
        font-size: 0.8rem;
        color: var(--dark-gray);
        margin-bottom: 5px;
    }
    
    .day-hours {
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .manual-entry-form {
        background: var(--light-gray);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .time-display {
            font-size: 2rem;
        }
        
        .week-overview {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Time Tracking</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manualEntryModal">
                <i class='bx bx-plus'></i> Add Manual Entry
            </button>
        </div>
    </div>
    
    <!-- Main Time Tracker -->
    <div class="time-tracker-main">
        @if($activeSession)
            <div>
                <h4><i class='bx bx-play-circle'></i> Currently Tracking</h4>
                <div class="time-display" id="liveTimer">{{ $activeSession->formatted_elapsed_time }}</div>
                <p class="mb-3">{{ $activeSession->task_description ?: 'No description' }}</p>
                @if($activeSession->project)
                    <p class="mb-3"><i class='bx bx-folder'></i> {{ $activeSession->project->name }}</p>
                @endif
                <button class="btn btn-light btn-lg" onclick="stopTimer()">
                    <i class='bx bx-stop'></i> Stop Tracking
                </button>
            </div>
        @else
            <div>
                <h4><i class='bx bx-time'></i> Ready to Track Time</h4>
                <div class="time-display">00:00:00</div>
                <p class="mb-3">Start tracking your work time</p>
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#startTimerModal">
                    <i class='bx bx-play'></i> Start Tracking
                </button>
            </div>
        @endif
    </div>
    
    <!-- Week Overview -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">This Week Overview</h6>
        </div>
        <div class="card-body">
            <div class="week-overview">
                @php
                    $weekStart = \Carbon\Carbon::parse($currentDate)->startOfWeek();
                    $today = now()->format('Y-m-d');
                @endphp
                
                @for($i = 0; $i < 7; $i++)
                    @php
                        $day = $weekStart->copy()->addDays($i);
                        $dayStr = $day->format('Y-m-d');
                        $dayHours = $weekEntries->get($dayStr)?->sum('duration_minutes') ?? 0;
                        $dayHoursFormatted = round($dayHours / 60, 1);
                    @endphp
                    
                    <div class="day-card {{ $dayStr === $today ? 'today' : '' }}">
                        <div class="day-name">{{ $day->format('D') }}</div>
                        <div class="day-hours">{{ $dayHoursFormatted }}h</div>
                        <small class="text-muted">{{ $day->format('M j') }}</small>
                    </div>
                @endfor
            </div>
            
            <div class="text-center mt-3">
                <span class="badge bg-primary">Total: {{ round($weekEntries->flatten()->sum('duration_minutes') / 60, 1) }} hours this week</span>
            </div>
        </div>
    </div>
    
    <!-- Today's Time Entries -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Today's Time Entries ({{ \Carbon\Carbon::parse($currentDate)->format('M d, Y') }})</h6>
            <span class="badge bg-success">Total: {{ round($todayTotal / 60, 2) }} hours</span>
        </div>
        <div class="card-body">
            @if($todayEntries->count() > 0)
                @foreach($todayEntries as $entry)
                    <div class="card time-entry-card {{ $entry->entry_type }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">{{ $entry->task_description ?: 'No description' }}</h6>
                                    <small class="text-muted">
                                        <i class='bx bx-folder'></i> {{ $entry->project->name ?? 'No project' }}
                                        @if($entry->entry_type === 'manual')
                                            <span class="badge bg-warning ms-2">Manual</span>
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
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editEntryModal-{{ $entry->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <form action="{{ route('user.time-tracking.delete', $entry) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this entry?')">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            @if($entry->notes)
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted"><strong>Notes:</strong> {{ $entry->notes }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class='bx bx-time' style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">No time entries for today</h5>
                    <p class="text-muted">Start tracking time or add a manual entry</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startTimerModal">
                        <i class='bx bx-play'></i> Start Tracking
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Start Timer Modal -->
<div class="modal fade" id="startTimerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start Time Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="startTimerForm">
                    <div class="mb-3">
                        <label for="project_select" class="form-label">Project (Optional)</label>
                        <select class="form-select" id="project_select" name="project_id">
                            <option value="">No specific project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="task_description" class="form-label">What are you working on? *</label>
                        <input type="text" class="form-control" id="task_description" name="task_description" 
                               placeholder="Enter task description..." required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="startTimeTracking()">
                    <i class='bx bx-play'></i> Start Timer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Manual Entry Modal -->
<div class="modal fade" id="manualEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Manual Time Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.time-tracking.manual') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="manual_project_id" class="form-label">Project (Optional)</label>
                        <select class="form-select" id="manual_project_id" name="project_id">
                            <option value="">No specific project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="manual_task_description" class="form-label">Task Description *</label>
                        <input type="text" class="form-control" id="manual_task_description" name="task_description" 
                               placeholder="What did you work on?" required>
                    </div>
                    <div class="mb-3">
                        <label for="manual_work_date" class="form-label">Date *</label>
                        <input type="date" class="form-control" id="manual_work_date" name="work_date" 
                               value="{{ $currentDate }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manual_start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="manual_start_time" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manual_end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="manual_end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="manual_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="manual_notes" name="notes" rows="3" 
                                  placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Add Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($todayEntries as $entry)
<!-- Edit Entry Modal -->
<div class="modal fade" id="editEntryModal-{{ $entry->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Time Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.time-tracking.update', $entry) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project (Optional)</label>
                        <select class="form-select" name="project_id">
                            <option value="">No specific project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $entry->project_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Description *</label>
                        <input type="text" class="form-control" name="task_description" 
                               value="{{ $entry->task_description }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="work_date" 
                               value="{{ $entry->work_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" class="form-control" name="start_time" 
                                   value="{{ $entry->start_time ? $entry->start_time->format('H:i') : '' }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" class="form-control" name="end_time" 
                                   value="{{ $entry->end_time ? $entry->end_time->format('H:i') : '' }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3">{{ $entry->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-save'></i> Update Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
    let liveTimerInterval;
    
    @if($activeSession)
        // Update live timer
        const startTime = new Date('{{ $activeSession->started_at }}');
        liveTimerInterval = setInterval(function() {
            const now = new Date();
            const elapsed = Math.floor((now - startTime) / 1000);
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;
            
            document.getElementById('liveTimer').textContent = 
                String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0');
        }, 1000);
    @endif
    
    function startTimeTracking() {
        const form = document.getElementById('startTimerForm');
        const formData = new FormData(form);
        
        fetch('{{ route("user.time-tracking.start") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                project_id: formData.get('project_id'),
                task_description: formData.get('task_description'),
                notes: formData.get('notes'),
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to start timer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to start timer');
        });
    }
    
    function stopTimer() {
        if (confirm('Are you sure you want to stop the timer?')) {
            fetch('{{ route("user.time-tracking.stop") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to stop timer');
                }
            });
        }
    }
</script>
@endpush