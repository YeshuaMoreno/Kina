<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Reglas de visibilidad de perfiles y etiquetas sensibles.
 */
class ProfileVisibilityService
{
    /** IDs de usuarios con conexión aceptada con $user. */
    public function connectedUserIds(User $user): array
    {
        return Connection::query()
            ->where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->get()
            ->map(fn (Connection $c) => $c->user_one_id === $user->id ? $c->user_two_id : $c->user_one_id)
            ->unique()
            ->values()
            ->all();
    }

    public function areConnected(User $a, User $b): bool
    {
        [$one, $two] = $a->id < $b->id ? [$a->id, $b->id] : [$b->id, $a->id];

        return Connection::where('user_one_id', $one)->where('user_two_id', $two)->exists();
    }

    /**
     * ¿Puede $viewer ver el perfil público de $target?
     */
    public function canView(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }

        if ($target->is_suspended) {
            return false;
        }

        if (in_array($target->id, $viewer->blockedUserIds(), true)) {
            return false;
        }

        $profile = $target->profile;
        if (! $profile || ! $profile->onboarding_completed) {
            return false;
        }

        return match ($profile->profile_visibility) {
            'nunca' => false,
            'solo_conexiones' => $this->areConnected($viewer, $target),
            default => true, // publico
        };
    }

    /**
     * Etiquetas de $profile que $viewer puede ver, respetando lo sensible.
     */
    public function visibleTags(User $viewer, Profile $profile): Collection
    {
        $isOwner = $viewer->id === $profile->user_id;
        $connected = $isOwner ? true : $this->areConnected($viewer, $profile->user);

        return $profile->identityTags->filter(function ($tag) use ($isOwner, $connected, $profile) {
            if ($isOwner) {
                return true;
            }

            if (! $tag->is_sensitive) {
                return (bool) $tag->pivot->is_visible;
            }

            // Etiqueta sensible: requiere permiso explícito del dueño.
            if (! $profile->show_sensitive_tags) {
                return false;
            }

            return match ($profile->sensitive_tags_visibility) {
                'publico' => true,
                'solo_conexiones' => $connected,
                default => false, // nunca
            };
        })->values();
    }
}
