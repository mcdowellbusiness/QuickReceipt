<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgMember extends Model
{
    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
