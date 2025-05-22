<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeEntry extends Model
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
        'work_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'entry_type',
        'is_billable',
        'hourly_rate',
        'notes',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_billable' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Get the user that owns the time entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project associated with the time entry.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who approved this entry.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate duration from start and end time.
     *
     * @return int Duration in minutes
     */
    public function calculateDuration()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        // Handle overnight shifts
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        return $start->diffInMinutes($end);
    }

    /**
     * Get formatted duration.
     *
     * @return string
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Get duration in hours as decimal.
     *
     * @return float
     */
    public function getDurationHoursAttribute()
    {
        return round($this->duration_minutes / 60, 2);
    }

    /**
     * Calculate billable amount.
     *
     * @return float
     */
    public function getBillableAmountAttribute()
    {
        if (!$this->is_billable || !$this->hourly_rate) {
            return 0;
        }
        
        return $this->duration_hours * $this->hourly_rate;
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by project.
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for approved entries.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for pending approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }
}