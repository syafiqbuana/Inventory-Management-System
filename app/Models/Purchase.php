<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Purchase extends Model
{
    protected $fillable = [
        'total_amount',
        'note',
        'purchase_date',

    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        "purchase_date" => "date",
    ];
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public static function booted()
    {
        static::creating(function (Purchase $purchase) {
            $purchase->created_by ??= Auth::id();

            if (!$purchase->period_id) {
                $activePeriodId = Period::query()
                    ->where('is_closed', false)
                    ->orderByDesc('id')
                    ->value('id');

                if (!$activePeriodId) {
                    throw ValidationException::withMessages([
                        'period_id' => 'Tidak ada periode aktif.',
                    ]);
                }

                $purchase->period_id = $activePeriodId;
            }
        });

        static::updating(function (Purchase $purchase) {

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
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

}
