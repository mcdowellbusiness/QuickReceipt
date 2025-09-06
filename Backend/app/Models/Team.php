<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'org_id',
        'name',
        'description',
    ];

    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
