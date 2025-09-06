<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'org_id',
        'budget_id',
        'name',
    ];

    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
