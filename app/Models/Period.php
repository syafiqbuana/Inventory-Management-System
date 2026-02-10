<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected static $resolvedActivePeriod;
    protected $fillable = [
        'year',
        'is_closed',
        'closed_at'
    ];

    protected $casts = [
        'is_closed' => 'boolean'
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function usages()
    {
        return $this->hasMany(Usage::class);
    }

    public function item()
    {
        return $this->hasMany(Item::class);
    }

    public function isOpen()
    {
        return !$this->is_closed;
    }

    public static function active(): ?self
    {
        if (self::$resolvedActivePeriod) {
            return self::$resolvedActivePeriod;
        }

        return self::$resolvedActivePeriod = cache()->remember(
            'active_period',
            now()->addMinutes(10),
            fn() => self::where('is_closed', false)->latest('id')->first()
        );
    }
    public static function forgetActive(): void
    {
        self::$resolvedActivePeriod = null;
        cache()->forget('active_period');
    }
}
