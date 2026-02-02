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

  public static function active(): self
{
    if (static::$resolvedActivePeriod !== null) {
        return static::$resolvedActivePeriod;
    }

    return static::$resolvedActivePeriod = cache()->rememberForever(
        'active_period',
        fn () => self::where('is_closed', false)->firstOrFail()
    );
}
}
