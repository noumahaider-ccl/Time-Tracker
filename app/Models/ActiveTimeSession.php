<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActiveTimeSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'task_description',
        'started_at',
        'last_ping_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'last_ping_at' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project associated with the session.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get elapsed time in minutes.
     *
     * @return int
     */
    public function getElapsedMinutesAttribute()
    {
        return $this->started_at->diffInMinutes(now());
    }

    /**
     * Get formatted elapsed time.
     *
     * @return string
     */
    public function getFormattedElapsedTimeAttribute()
    {
        $minutes = $this->elapsed_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Check if session is stale (no ping for more than 5 minutes).
     *
     * @return bool
     */
    public function isStale()
    {
        if (!$this->last_ping_at) {
            return $this->started_at->diffInMinutes(now()) > 5;
        }
        
        return $this->last_ping_at->diffInMinutes(now()) > 5;
    }

    /**
     * Update the last ping timestamp.
     */
    public function ping()
    {
        $this->update(['last_ping_at' => now()]);
    }
}