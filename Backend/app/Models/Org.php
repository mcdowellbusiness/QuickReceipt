<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Org extends Model
{
    protected $fillable = [
        'name',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function members()
    {
        return $this->hasMany(OrgMember::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}