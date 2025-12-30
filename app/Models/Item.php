<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'initial_stock',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function usages()
    {
        return $this->hasMany(Usage::class);
    }
    public function getTotalStockAttribute()
    {
        $initialStock = $this->initial_stock;
        $purchasedQty = $this->purchaseItems()->sum('qty');
        $usedQty = $this->usages()->sum('qty');
        return $initialStock + $purchasedQty - $usedQty;
    }

    public function getLatestPriceAttribute()
    {
        return $this->purchaseItems()->latest()->value('unit_price');
    }
}
