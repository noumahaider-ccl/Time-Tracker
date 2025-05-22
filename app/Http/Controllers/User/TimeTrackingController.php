<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\ActiveTimeSession;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TimeTrackingController extends Controller
{
    /**
     * Display the time tracking dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentDate = $request->get('date', now()->format('Y-m-d'));
        
        // Get active session
        $activeSession = ActiveTimeSession::where('user_id', $user->id)->first();
        
        // Get today's time entries
        $todayEntries = TimeEntry::forUser($user->id)
            ->where('work_date', $currentDate)
            ->with('project')
            ->orderBy('start_time')
            ->get();
            
        // Calculate today's total
        $todayTotal = $todayEntries->sum('duration_minutes');
        
        // Get this week's summary
        $weekStart = Carbon::parse($currentDate)->startOfWeek();
        $weekEnd = Carbon::parse($currentDate)->endOfWeek();
        
        $weekEntries = TimeEntry::forUser($user->id)
            ->inDateRange($weekStart, $weekEnd)
            ->get()
            ->groupBy('work_date');
            
        // Get user's assigned projects
        $projects = Project::where('client_id', $user->id)
            ->where('status', '!=', 'completed')
            ->get();
        
        return view('user.time-tracking.index', compact(
            'activeSession',
            'todayEntries',
            'todayTotal',
            'weekEntries',
            'projects',
            'currentDate'
        ));
    }

    /**
     * Start time tracking.
     */
    public function start(Request $request)
    {
        $user = auth()->user();
        
        // Check if user already has an active session
        $existingSession = ActiveTimeSession::where('user_id', $user->id)->first();
        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active time tracking session.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'nullable|exists:projects,id',
            'task_description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Create active session
        $session = ActiveTimeSession::create([
            'user_id' => $user->id,
            'project_id' => $request->project_id,
            'task_description' => $request->task_description,
            'started_at' => now(),
            'last_ping_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time tracking started successfully.',
            'session' => $session->load('project')
        ]);
    }

    /**
     * Stop time tracking.
     */
    public function stop(Request $request)
    {
        $user = auth()->user();
        
        $session = ActiveTimeSession::where('user_id', $user->id)->first();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active time tracking session found.'
            ]);
        }

        // Calculate duration
        $duration = $session->elapsed_minutes;
        
        // Create time entry
        $timeEntry = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $session->project_id,
            'task_description' => $session->task_description,
            'work_date' => $session->started_at->format('Y-m-d'),
            'start_time' => $session->started_at->format('H:i'),
            'end_time' => now()->format('H:i'),
            'duration_minutes' => $duration,
            'entry_type' => 'automatic',
            'notes' => $session->notes,
        ]);

        // Delete active session
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time tracking stopped successfully.',
            'duration' => $timeEntry->formatted_duration,
            'timeEntry' => $timeEntry->load('project')
        ]);
    }

    /**
     * Ping to keep session alive.
     */
    public function ping()
    {
        $user = auth()->user();
        
        $session = ActiveTimeSession::where('user_id', $user->id)->first();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session found.'
            ]);
        }

        $session->ping();

        return response()->json([
            'success' => true,
            'elapsed_time' => $session->formatted_elapsed_time
        ]);
    }

    /**
     * Get current session status.
     */
    public function status()
    {
        $user = auth()->user();
        
        $session = ActiveTimeSession::where('user_id', $user->id)->first();
        
        return response()->json([
            'hasActiveSession' => (bool) $session,
            'session' => $session ? $session->load('project') : null
        ]);
    }

    /**
     * Add manual time entry.
     */
    public function addManualEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'nullable|exists:projects,id',
            'task_description' => 'required|string|max:255',
            'work_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();
        
        // Calculate duration
        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);
        $duration = $start->diffInMinutes($end);

        // Create time entry
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $request->project_id,
            'task_description' => $request->task_description,
            'work_date' => $request->work_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $duration,
            'entry_type' => 'manual',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Manual time entry added successfully.');
    }

    /**
     * Update time entry.
     */
    public function updateEntry(Request $request, TimeEntry $timeEntry)
    {
        // Check if user owns this entry
        if ($timeEntry->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'nullable|exists:projects,id',
            'task_description' => 'required|string|max:255',
            'work_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate new duration
        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);
        $duration = $start->diffInMinutes($end);

        $timeEntry->update([
            'project_id' => $request->project_id,
            'task_description' => $request->task_description,
            'work_date' => $request->work_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $duration,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Time entry updated successfully.');
    }

    /**
     * Delete time entry.
     */
    public function deleteEntry(TimeEntry $timeEntry)
    {
        // Check if user owns this entry
        if ($timeEntry->user_id !== auth()->id()) {
            abort(403);
        }

        $timeEntry->delete();

        return redirect()->back()->with('success', 'Time entry deleted successfully.');
    }

    /**
     * Get time reports.
     */
    public function reports(Request $request)
    {
        $user = auth()->user();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $timeEntries = TimeEntry::forUser($user->id)
            ->inDateRange($startDate, $endDate)
            ->with('project')
            ->orderBy('work_date', 'desc')
            ->paginate(15);
            
        // Summary data
        $totalMinutes = TimeEntry::forUser($user->id)
            ->inDateRange($startDate, $endDate)
            ->sum('duration_minutes');
            
        $projectSummary = TimeEntry::forUser($user->id)
            ->inDateRange($startDate, $endDate)
            ->with('project')
            ->get()
            ->groupBy('project.name')
            ->map(function ($entries) {
                return [
                    'total_minutes' => $entries->sum('duration_minutes'),
                    'total_hours' => round($entries->sum('duration_minutes') / 60, 2),
                    'entries_count' => $entries->count()
                ];
            });
        
        return view('user.time-tracking.reports', compact(
            'timeEntries',
            'totalMinutes',
            'projectSummary',
            'startDate',
            'endDate'
        ));
    }
}