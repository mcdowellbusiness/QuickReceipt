<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'name',
        'team_id',
        'invited_by',
        'role',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted()
    {
        return !is_null($this->accepted_at);
    }

    public static function createInvitation($email, $name, $teamId, $invitedBy, $role = 'admin')
    {
        return self::create([
            'email' => $email,
            'name' => $name,
            'team_id' => $teamId,
            'invited_by' => $invitedBy,
            'role' => $role,
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7), // Invitation expires in 7 days
        ]);
    }
}
