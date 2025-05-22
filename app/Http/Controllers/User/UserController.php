<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\ActiveTimeSession;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get user's projects
        $projects = Project::where('client_id', $user->id)
            ->with(['manager'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get active time session
        $activeSession = ActiveTimeSession::where('user_id', $user->id)->first();
        
        // Today's time summary
        $todayTime = TimeEntry::forUser($user->id)
            ->where('work_date', now()->format('Y-m-d'))
            ->sum('duration_minutes');
            
        // This week's time summary
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $weekTime = TimeEntry::forUser($user->id)
            ->inDateRange($weekStart, $weekEnd)
            ->sum('duration_minutes');
            
        // This month's time summary
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthTime = TimeEntry::forUser($user->id)
            ->inDateRange($monthStart, $monthEnd)
            ->sum('duration_minutes');
            
        // Recent time entries
        $recentEntries = TimeEntry::forUser($user->id)
            ->with('project')
            ->orderBy('work_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->take(5)
            ->get();
            
        // Project time breakdown for current month
        $projectTimeBreakdown = TimeEntry::forUser($user->id)
            ->inDateRange($monthStart, $monthEnd)
            ->with('project')
            ->get()
            ->groupBy('project.name')
            ->map(function ($entries) {
                return $entries->sum('duration_minutes');
            });

        $stats = [
            'totalProjects' => $projects->count(),
            'activeProjects' => $projects->where('status', 'in_progress')->count(),
            'completedProjects' => $projects->where('status', 'completed')->count(),
            'todayHours' => round($todayTime / 60, 2),
            'weekHours' => round($weekTime / 60, 2),
            'monthHours' => round($monthTime / 60, 2),
        ];

        return view('user.dashboard', compact(
            'projects',
            'activeSession',
            'recentEntries',
            'projectTimeBreakdown',
            'stats'
        ));
    }

    /**
     * Display user's projects.
     */
    public function projects()
    {
        $user = auth()->user();
        
        $projects = Project::where('client_id', $user->id)
            ->with(['manager'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.projects.index', compact('projects'));
    }

    /**
     * Show specific project details.
     */
    public function showProject(Project $project)
    {
        // Check if user owns this project
        if ($project->client_id !== auth()->id()) {
            abort(403);
        }
        
        $user = auth()->user();
        
        // Get time entries for this project
        $timeEntries = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->orderBy('work_date', 'desc')
            ->take(10)
            ->get();
            
        // Total time spent on this project
        $totalTime = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->sum('duration_minutes');

        return view('user.projects.show', compact('project', 'timeEntries', 'totalTime'));
    }
}