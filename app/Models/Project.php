<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    //add relation to user model

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_user_roles', // 👈 SAME TABLE
            'project_id',
            'user_id'
        )->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }
}
