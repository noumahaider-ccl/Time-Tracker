@extends('layouts.user')

@section('title', 'Time Reports')

@push('styles')
<style>
    .report-summary {
        background: linear-gradient(135deg, var(--primary-color), #d90000);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .summary-stat {
        text-align: center;
        padding: 20px;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
    }
    
    .stat-label {
        font-size: 1rem;
        opacity: 0.9;
    }
    
    .project-breakdown {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .project-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-left: 4px solid var(--primary-color);
    }
    
    .date-filter-form {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .time-entry-row {
        border-left: 4px solid var(--success-color);
        margin-bottom: 10px;
    }
    
    .time-entry-row.manual {
        border-left-color: var(--warning-color);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Time Reports</h1>
        <button class="btn btn-primary" onclick="exportReport()">
            <i class='bx bx-download'></i> Export CSV
        </button>
    </div>
    
    <!-- Date Filter -->
    <div class="date-filter-form">
        <form method="GET" action="{{ route('user.time-tracking.reports') }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-filter'></i> Filter
                    </button>
                    <a href="{{ route('user.time-tracking.reports') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Summary -->
    <div class="report-summary">
        <div class="row">
            <div class="col-md-4">
                <div class="summary-stat">
                    <div class="stat-number">{{ round($totalMinutes / 60, 1) }}</div>
                    <div class="stat-label">Total Hours</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-stat">
                    <div class="stat-number">{{ $timeEntries->total() }}</div>
                    <div class="stat-label">Time Entries</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-stat">
                    <div class="stat-number">{{ round($totalMinutes / 60 / max(1, \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1), 1) }}</div>
                    <div class="stat-label">Avg Hours/Day</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Project Breakdown -->
    @if($projectSummary->count() > 0)
        <div class="mb-4">
            <h5 class="mb-3">Project Breakdown</h5>
            <div class="project-breakdown">
                @foreach($projectSummary as $projectName => $data)
                    <div class="project-card">
                        <h6 class="mb-2">{{ $projectName ?: 'No Project' }}</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $data['total_hours'] }} hours</span>
                            <span class="badge bg-primary">{{ $data['entries_count'] }} entries</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $projectSummary->max('total_minutes') > 0 ? ($data['total_minutes'] / $projectSummary->max('total_minutes')) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Time Entries List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">
                Time Entries 
                <small class="text-muted">({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</small>
            </h6>
        </div>
        <div class="card-body">
            @if($timeEntries->count() > 0)
                @foreach($timeEntries as $entry)
                    <div class="card time-entry-row {{ $entry->entry_type }} mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <h6 class="mb-1">{{ $entry->task_description ?: 'No description' }}</h6>
                                    <small class="text-muted">
                                        <i class='bx bx-folder'></i> {{ $entry->project->name ?? 'No project' }}
                                        @if($entry->entry_type === 'manual')
                                            <span class="badge bg-warning ms-2">Manual</span>
                                        @endif
                                    </small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="fw-bold">{{ $entry->formatted_duration }}</div>
                                    <small class="text-muted">
                                        {{ $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '--' }} - 
                                        {{ $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '--' }}
                                    </small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="fw-bold">{{ $entry->work_date->format('M d') }}</div>
                                    <small class="text-muted">{{ $entry->work_date->format('Y') }}</small>
                                </div>
                                <div class="col-md-3">
                                    @if($entry->notes)
                                        <small class="text-muted">
                                            <i class='bx bx-note'></i> {{ Str::limit($entry->notes, 50) }}
                                        </small>
                                    @else
                                        <small class="text-muted">No notes</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $timeEntries->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class='bx bx-chart' style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">No time entries found</h5>
                    <p class="text-muted">Try adjusting your date range or start tracking time</p>
                    <a href="{{ route('user.time-tracking') }}" class="btn btn-primary">
                        <i class='bx bx-time'></i> Start Tracking
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportReport() {
        const startDate = '{{ $startDate }}';
        const endDate = '{{ $endDate }}';
        
        // Create CSV content
        let csvContent = "Date,Project,Task,Start Time,End Time,Duration (Hours),Type,Notes\n";
        
        @foreach($timeEntries as $entry)
            csvContent += [
                '{{ $entry->work_date->format('Y-m-d') }}',
                '"{{ $entry->project->name ?? 'No project' }}"',
                '"{{ str_replace('"', '""', $entry->task_description ?: 'No description') }}"',
                '{{ $entry->start_time ? $entry->start_time->format('H:i') : '' }}',
                '{{ $entry->end_time ? $entry->end_time->format('H:i') : '' }}',
                '{{ $entry->duration_hours }}',
                '{{ ucfirst($entry->entry_type) }}',
                '"{{ str_replace('"', '""', $entry->notes ?: '') }}"'
            ].join(',') + '\n';
        @endforeach
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `time_report_${startDate}_to_${endDate}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush