<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'company',
        'address',
        'profile_photo',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

    /**
     * Check if the user is a project manager.
     *
     * @return bool
     */
    public function isProjectManager()
    {
        return $this->role->name === 'project_manager';
    }

    /**
     * Check if the user is a client.
     *
     * @return bool
     */
    public function isClient()
    {
        return $this->role->name === 'client';
    }

    /**
     * Get the projects that belong to this user (for clients).
     */
    public function clientProjects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    /**
     * Get the projects managed by this user (for project managers).
     */
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }
}