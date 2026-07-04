<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthdate',
        'is_adult_confirmed',
        'is_admin',
        'is_suspended',
        'suspended_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'is_adult_confirmed' => 'boolean',
            'is_admin' => 'boolean',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    // ----- Relaciones -----

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProfilePhoto::class);
    }

    public function sentConnectionRequests(): HasMany
    {
        return $this->hasMany(ConnectionRequest::class, 'sender_id');
    }

    public function receivedConnectionRequests(): HasMany
    {
        return $this->hasMany(ConnectionRequest::class, 'receiver_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /** Usuarios que este usuario ha bloqueado */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    /** Bloqueos donde este usuario es el bloqueado */
    public function blockedBy(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_id');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    // ----- Helpers -----

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    /** IDs de usuarios bloqueados o que me bloquearon (para excluir en descubrir/chat). */
    public function blockedUserIds(): array
    {
        $iBlocked = $this->blocks()->pluck('blocked_id');
        $blockedMe = $this->blockedBy()->pluck('blocker_id');

        return $iBlocked->merge($blockedMe)->unique()->values()->all();
    }
}
