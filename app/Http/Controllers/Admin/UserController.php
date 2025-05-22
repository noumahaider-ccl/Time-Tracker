<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Project;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $roleFilter = $request->input('role');
        $search = $request->input('search');
        
        $query = User::with('role');
        
        // Apply role filter if provided
        if ($roleFilter) {
            $query->whereHas('role', function($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }
        
        // Apply search if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles', 'roleFilter', 'search'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users|unique:user_invitations',
            'role_id' => 'required|exists:roles,id',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|max:2048',
            'send_invitation' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $validator->validated();
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo'] = $path;
        }

        // Check if admin wants to send invitation
        if ($request->boolean('send_invitation', true)) {
            // Create invitation instead of user directly
            $invitation = UserInvitation::create([
                'email' => $userData['email'],
                'token' => UserInvitation::generateToken(),
                'name' => $userData['name'],
                'role_id' => $userData['role_id'],
                'company' => $userData['company'] ?? null,
                'phone' => $userData['phone'] ?? null,
                'address' => $userData['address'] ?? null,
                'profile_photo' => $userData['profile_photo'] ?? null,
                'invited_by' => auth()->id(),
                'expires_at' => now()->addDays(7), // 7 days to accept invitation
            ]);

            // Send invitation email
            \Mail::to($invitation->email)->send(new \App\Mail\UserInvitationMail($invitation));

            return redirect()->route('admin.users.index')
                ->with('success', 'User invitation sent successfully to ' . $invitation->email);
        } else {
            // Create user directly with a temporary password (old method for backward compatibility)
            $userData['password'] = Hash::make('TempPassword123!'); // They'll need to reset it
            $user = User::create($userData);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully. Please provide them with temporary password: TempPassword123!');
        }
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        // Get projects associated with the user
        $clientProjects = [];
        $managedProjects = [];
        
        if ($user->isClient()) {
            $clientProjects = Project::where('client_id', $user->id)->get();
        }
        
        if ($user->isProjectManager()) {
            $managedProjects = Project::where('manager_id', $user->id)->get();
        }
        
        return view('admin.users.show', compact('user', 'clientProjects', 'managedProjects'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'password' => ['nullable', Password::defaults()],
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->except(['password', 'profile_photo']);
        
        // Update password if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->input('password'));
        }
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo'] = $path;
        }

        // Update the user
        $user->update($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        // Check if user has associated projects as client
        $clientProjects = Project::where('client_id', $user->id)->count();
        if ($clientProjects > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete this user because they have associated projects as a client. Please reassign or delete those projects first.');
        }
        
        // Check if user has associated projects as manager
        $managedProjects = Project::where('manager_id', $user->id)->count();
        if ($managedProjects > 0) {
            // For managers, we can set the manager_id to null since it's nullable
            Project::where('manager_id', $user->id)->update(['manager_id' => null]);
        }

        try {
            // Delete profile photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->delete();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
                
        } catch (QueryException $e) {
            // Handle any other database constraint violations
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete this user because they have related records in the system.');
        }
    }
}