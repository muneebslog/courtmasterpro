<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Round extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'order_no',
    ];

    protected $casts = [
        'order_no' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
    // add relation to matches
    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class);
    }    

    public function getShortLabelAttribute()
{
    return match ($this->name) {
        'Quarter Finals' => 'QF',
        'Semi Finals' => 'SF',
        'Final' => 'F',
        default => Str::upper(Str::replace(' ', '', $this->name)),
    };
}

}
