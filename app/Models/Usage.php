<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usage extends Model
{
    protected $fillable = [
        'item_id',
        'qty',
        'used_for',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
