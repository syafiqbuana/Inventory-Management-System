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
        'price' => 'decimal:2',
        'initial_period_id'=>'integer'
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

    public function periodStocks()
{
    return $this->hasMany(PeriodStock::class);
}

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function stockForPeriod(int $periodId): int
    {
        // ✅ Cast ke integer untuk comparison
        $initial = (int)$this->initial_period_id === $periodId
            ? $this->initial_stock
            : 0;

        // ✅ Gunakan magic getter, bukan attributes array
        $purchased = (int) ($this->purchased_qty ?? 0);
        $used = (int) ($this->used_qty ?? 0);

        return $initial + $purchased - $used;
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