<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetScore extends Model
{
    protected $fillable = [
        'set_id',
        'team_id',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
