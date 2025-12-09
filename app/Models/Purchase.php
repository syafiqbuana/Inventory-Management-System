<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'total_amount',
        'note',
    ];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getCurrentBalanceAttribute()
    {
$globalBalance = Balance::find(1); // Ambil record Balance dengan ID 1
        return $globalBalance->amount ?? 0;
    }
}
