<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageItem extends Model
{
    protected $fillable = [
        'usage_id',
        'item_id',
        'qty',
    ];

    public function usage()
    {
        return $this->belongsTo(Usage::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}
