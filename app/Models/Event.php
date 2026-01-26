<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'tournament_id',
        'name',
        'type',
        'default_discipline',
        'best_of_sets',
        'status',
    ];

    protected $casts = [
        'best_of_sets' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }
}
