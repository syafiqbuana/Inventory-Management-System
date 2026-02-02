<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'initial_stock',
        'price',
        'item_type_id',
        'type',
        'initial_period_id',
        'created_by',
    ];

    protected $casts = [
        'initial_stock' => 'integer',
        'price' => 'integer',
    ];

    protected $appends = ['total_stock'];

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->created_by = Auth::id();
        });

        static::creating(function ($item) {
            if (!$item->initial_period_id) {
                $active = \App\Models\Period::active();

                if (!$active) {
                    throw new \Exception('No active period found.');
                }

                $item->initial_period_id = $active->id;
            }
        });

    }

    public function initialPeriod()
    {
        return $this->belongsTo(Period::class, 'initial_period_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function usageItems()
    {
        return $this->hasMany(UsageItem::class);
    }

    public function usages()
    {
        return $this->hasMany(Usage::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function stockForPeriod(int $periodId): int
    {
        $initial = $this->initial_period_id === $periodId
            ? $this->initial_stock
            : 0;

        return $initial
            + ($this->purchased_qty ?? 0)
            - ($this->used_qty ?? 0);
    }


    public function getTotalStockAttribute(): int
    {
        $activePeriod = Period::active();

        if (!$activePeriod) {
            return 0;
        }

        return $this->stockForPeriod($activePeriod->id);
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
