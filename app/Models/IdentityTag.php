<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class IdentityTag extends Model
{
    protected $fillable = [
        'name',
        'is_sensitive',
    ];

    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
        ];
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class)
            ->withPivot(['is_visible', 'visibility'])
            ->withTimestamps();
    }
}
