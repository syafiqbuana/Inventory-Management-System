<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $fillable = [
        'amount'
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function getLatestPurchaseAttribute()
    {
        // Ambil satu record Purchase terbaru, diasumsikan tidak ada relasi langsung ke Balance
        return Purchase::orderByDesc('created_at')->first();
    }
}

