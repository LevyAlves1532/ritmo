<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Habit extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'frequency',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
