<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Usage extends Model
{
    protected $fillable = [
        'used_by',
        'usage_date',
        'used_for',
    ];

    protected $casts = [
        'usage_date' => 'date'
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->created_by = Auth::id();
        });

            static::creating(function (Usage $purchase) {

            $purchase->created_by ??= Auth::id();

            if (! $purchase->period_id) {
                $activePeriodId = Period::query()
                    ->where('is_closed', false)
                    ->value('id');

                if (! $activePeriodId) {
                    throw ValidationException::withMessages([
                        'period_id' => 'No active period available.',
                    ]);
                }

                $purchase->period_id = $activePeriodId;
            }

            $isClosed = Period::query()
                ->where('id', $purchase->period_id)
                ->where('is_closed', true)
                ->exists();

            if ($isClosed) {
                throw ValidationException::withMessages([
                    'period_id' => 'Cannot create purchase in a closed period.',
                ]);
            }
        });

        static::updating(function (Usage $purchase) {

            $isClosed = Period::query()
                ->where('id', $purchase->period_id)
                ->where('is_closed', true)
                ->exists();

            if ($isClosed) {
                throw ValidationException::withMessages([
                    'period_id' => 'Cannot modify purchase in a closed period.',
                ]);
            }
        });

        static::deleting(function (Usage $purchase) {

            $isClosed = Period::query()
                ->where('id', $purchase->period_id)
                ->where('is_closed', true)
                ->exists();

            if ($isClosed) {
                throw ValidationException::withMessages([
                    'period_id' => 'Cannot delete purchase in a closed period.',
                ]);
            }
        });
    }

    public function period(){
        return $this->belongsTo(Period::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function usageItems()
    {
        return $this->hasMany(UsageItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
