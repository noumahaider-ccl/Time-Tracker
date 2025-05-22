<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserInvitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'name',
        'role_id',
        'company',
        'phone',
        'address',
        'profile_photo',
        'invited_by',
        'expires_at',
        'accepted',
        'accepted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'accepted' => 'boolean',
    ];

    /**
     * Generate a unique token for the invitation.
     *
     * @return string
     */
    public static function generateToken()
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if the invitation is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->accepted && !$this->isExpired();
    }

    /**
     * Get the role that belongs to the invitation.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Mark invitation as accepted.
     */
    public function markAsAccepted()
    {
        $this->update([
            'accepted' => true,
            'accepted_at' => now(),
        ]);
    }
}