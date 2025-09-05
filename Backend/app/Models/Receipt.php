<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
