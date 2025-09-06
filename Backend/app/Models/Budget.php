<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'team_id',
        'year',
        'total_limit_cents',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_limit_cents' => 'integer',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
