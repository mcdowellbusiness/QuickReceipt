<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}
