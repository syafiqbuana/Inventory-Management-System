<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'name',
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

    public function getLatestPriceAttribute(){
        return $this->purchaseItems()->latest()->value('unit_price');
    }
}
