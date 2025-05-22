<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class InvitationController extends Controller
{
    /**
     * Show the invitation setup form.
     *
     * @param  string  $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($token)
    {
        $invitation = UserInvitation::where('token', $token)->first();
        
        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Invalid invitation link.');
        }
        
        if ($invitation->accepted) {
            return redirect()->route('login')
                ->with('info', 'This invitation has already been accepted. Please log in with your credentials.');
        }
        
        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired. Please contact your administrator for a new invitation.');
        }
        
        return view('auth.invitation-setup', compact('invitation'));
    }
    
    /**
     * Process the invitation setup.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setup(Request $request, $token)
    {
        $invitation = UserInvitation::where('token', $token)->first();
        
        if (!$invitation || $invitation->accepted || $invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired invitation link.');
        }
        
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Create the user account
        $user = User::create([
            'name' => $invitation->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role_id' => $invitation->role_id,
            'company' => $invitation->company,
            'phone' => $invitation->phone,
            'address' => $invitation->address,
            'profile_photo' => $invitation->profile_photo,
            'email_verified_at' => now(),
        ]);
        
        // Mark invitation as accepted
        $invitation->markAsAccepted();
        
        // Log the user in
        auth()->login($user);
        
        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome! Your account has been set up successfully.');
        } elseif ($user->isProjectManager()) {
            return redirect()->route('manager.dashboard')
                ->with('success', 'Welcome! Your account has been set up successfully.');
        } elseif ($user->isClient()) {
            return redirect()->route('user.dashboard')
                ->with('success', 'Welcome! Your account has been set up successfully.');
        }
        
        return redirect()->route('login');
    }
    
    /**
     * Resend invitation (for admins).
     *
     * @param  \App\Models\UserInvitation  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(UserInvitation $invitation)
    {
        if ($invitation->accepted) {
            return redirect()->back()
                ->with('error', 'This invitation has already been accepted.');
        }
        
        // Extend expiration date
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);
        
        // Resend email
        \Mail::to($invitation->email)->send(new \App\Mail\UserInvitationMail($invitation));
        
        return redirect()->back()
            ->with('success', 'Invitation resent successfully to ' . $invitation->email);
    }
    
    /**
     * Cancel invitation (for admins).
     *
     * @param  \App\Models\UserInvitation  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(UserInvitation $invitation)
    {
        if ($invitation->accepted) {
            return redirect()->back()
                ->with('error', 'Cannot cancel an accepted invitation.');
        }
        
        $invitation->delete();
        
        return redirect()->back()
            ->with('success', 'Invitation cancelled successfully.');
    }
}