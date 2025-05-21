<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Get counts for dashboard stats
        $stats = [
            'totalClients' => User::where('role_id', 3)->count(), // Client role_id = 3
            'totalManagers' => User::where('role_id', 2)->count(), // Manager role_id = 2
            'totalProjects' => Project::count(),
            'completedProjects' => Project::where('status', 'completed')->count(),
            'inProgressProjects' => Project::where('status', 'in_progress')->count(),
            'notStartedProjects' => Project::where('status', 'not_started')->count(),
            'onHoldProjects' => Project::where('status', 'on_hold')->count(),
        ];

        // Get recent projects
        $recentProjects = Project::with(['client', 'manager'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get upcoming deadlines
        $upcomingDeadlines = Project::where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->with(['client', 'manager'])
            ->orderBy('end_date')
            ->take(5)
            ->get();

        // Get clients with most projects
        $topClients = DB::table('users')
            ->join('projects', 'users.id', '=', 'projects.client_id')
            ->select('users.id', 'users.name', DB::raw('count(projects.id) as project_count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('project_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentProjects',
            'upcomingDeadlines',
            'topClients'
        ));
    }
}