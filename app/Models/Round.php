<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
