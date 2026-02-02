<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'item_id',
        'qty',
        'unit_price',
        'supplier',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
