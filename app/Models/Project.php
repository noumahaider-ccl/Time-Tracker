<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'client_id',
        'manager_id',
        'start_date',
        'end_date',
        'status',
        'budget',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    /**
     * Get the client that owns the project.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the project manager that manages the project.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the milestones for the project.
     */
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Get the documents for the project.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the invoices for the project.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if the project is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get project completion percentage.
     *
     * @return float
     */
    public function getCompletionPercentage()
    {
        $tasks = $this->tasks;
        
        if ($tasks->isEmpty()) {
            return 0;
        }
        
        $completedTasks = $tasks->where('status', 'completed')->count();
        return ($completedTasks / $tasks->count()) * 100;
    }
}