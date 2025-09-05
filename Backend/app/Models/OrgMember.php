<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgMember extends Model
{
    protected $fillable = [
        'org_id',
        'user_id',
        'global_role',
    ];

    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}