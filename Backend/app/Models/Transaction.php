<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'org_id',
        'team_id',
        'budget_id',
        'user_id',
        'receipt_id',
        'type',
        'amount_cents',
        'date',
        'vendor',
        'memo',
        'category_id',
        'payment_type',
        'lost_receipt',
        'reference_code',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'date' => 'date',
        'lost_receipt' => 'boolean',
    ];

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
        return $this->belongsTo(Receipt::class);
    }
}
