<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'city',
        'state',
        'country',
        'bio',
        'looking_for',
        'social_battery',
        'show_sensitive_tags',
        'sensitive_tags_visibility',
        'profile_visibility',
        'onboarding_completed',
        'paused_at',
    ];

    protected function casts(): array
    {
        return [
            'show_sensitive_tags' => 'boolean',
            'onboarding_completed' => 'boolean',
            'paused_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class)->withTimestamps();
    }

    public function communicationPreference(): HasOne
    {
        return $this->hasOne(CommunicationPreference::class);
    }

    public function identityTags(): BelongsToMany
    {
        return $this->belongsToMany(IdentityTag::class)
            ->withPivot(['is_visible', 'visibility'])
            ->withTimestamps();
    }
}
