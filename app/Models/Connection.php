<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Connection extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'connected_at' => 'datetime',
        ];
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }
}
