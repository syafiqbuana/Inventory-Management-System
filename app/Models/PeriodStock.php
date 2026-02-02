<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodStock extends Model
{
    protected $fillable = [
        'period_id',
        'item_id',
        'initial_stock',
        'final_stock',
        'price',
    ];

    protected $casts = [
        'initial_stock' => 'integer',
        'final_stock' => 'integer',
        'price' => 'integer',
    ];

    public function period(){
        return $this->belongsTo(Period::class);
    }

    public function item(){
        return $this->belongsTo(Item::class);
    }
}
