<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationPreference extends Model
{
    protected $fillable = [
        'profile_id',
        'prefers_text',
        'direct_communication',
        'slow_responder',
        'prefers_quiet_plans',
        'chat_before_meeting',
        'no_surprise_calls',
    ];

    protected function casts(): array
    {
        return [
            'prefers_text' => 'boolean',
            'direct_communication' => 'boolean',
            'slow_responder' => 'boolean',
            'prefers_quiet_plans' => 'boolean',
            'chat_before_meeting' => 'boolean',
            'no_surprise_calls' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
