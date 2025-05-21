<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status');
        $clientFilter = $request->input('client_id');
        $managerFilter = $request->input('manager_id');
        $search = $request->input('search');
        
        $query = Project::with(['client', 'manager']);
        
        // Apply status filter if provided
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        
        // Apply client filter if provided
        if ($clientFilter) {
            $query->where('client_id', $clientFilter);
        }
        
        // Apply manager filter if provided
        if ($managerFilter) {
            $query->where('manager_id', $managerFilter);
        }
        
        // Apply search if provided
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        $projects = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get clients and managers for filter dropdowns
        $clients = User::whereHas('role', function($q) {
            $q->where('name', 'client');
        })->get();
        
        $managers = User::whereHas('role', function($q) {
            $q->where('name', 'project_manager');
        })->get();
        
        // Status options
        $statuses = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        
        return view('admin.projects.index', compact(
            'projects', 
            'clients', 
            'managers', 
            'statuses', 
            'statusFilter', 
            'clientFilter', 
            'managerFilter', 
            'search'
        ));
    }

    /**
     * Show the form for creating a new project.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $clients = User::whereHas('role', function($q) {
            $q->where('name', 'client');
        })->get();
        
        $managers = User::whereHas('role', function($q) {
            $q->where('name', 'project_manager');
        })->get();
        
        $statuses = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        
        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
        
        return view('admin.projects.create', compact('clients', 'managers', 'statuses', 'priorities'));
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:not_started,in_progress,on_hold,completed,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the project
        $project = Project::create($validator->validated());

        // TODO: Send email notifications to client and manager

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function show(Project $project)
    {
        // $project->load(['client', 'manager', 'tasks', 'milestones', 'documents']);
        
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function edit(Project $project)
    {
        $clients = User::whereHas('role', function($q) {
            $q->where('name', 'client');
        })->get();
        
        $managers = User::whereHas('role', function($q) {
            $q->where('name', 'project_manager');
        })->get();
        
        $statuses = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        
        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
        
        return view('admin.projects.edit', compact('project', 'clients', 'managers', 'statuses', 'priorities'));
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:not_started,in_progress,on_hold,completed,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update the project
        $project->update($validator->validated());

        // TODO: Send email notifications about project update if needed

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project)
    {
        // Delete related records if needed
        // For now, we'll just delete the project
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}