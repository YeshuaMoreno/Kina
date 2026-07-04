<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Conversation;
use App\Models\User;

/**
 * Reglas de acceso al chat: solo los dos miembros de la conexión, y nunca
 * si existe un bloqueo entre ellos (en cualquier dirección).
 */
class ChatAccessService
{
    /** @return array{0:int,1:int} IDs de los dos usuarios de la conversación. */
    public function participantIds(Conversation $conversation): array
    {
        $connection = $conversation->connection;

        return [$connection->user_one_id, $connection->user_two_id];
    }

    public function isParticipant(User $user, Conversation $conversation): bool
    {
        return in_array($user->id, $this->participantIds($conversation), true);
    }

    public function otherUserId(User $user, Conversation $conversation): int
    {
        [$one, $two] = $this->participantIds($conversation);

        return $user->id === $one ? $two : $one;
    }

    public function blockExistsBetween(int $a, int $b): bool
    {
        return Block::query()
            ->where(fn ($q) => $q->where('blocker_id', $a)->where('blocked_id', $b))
            ->orWhere(fn ($q) => $q->where('blocker_id', $b)->where('blocked_id', $a))
            ->exists();
    }

    /** ¿Puede $user ver/usar esta conversación? */
    public function canAccess(User $user, Conversation $conversation): bool
    {
        if (! $this->isParticipant($user, $conversation)) {
            return false;
        }

        return ! $this->blockExistsBetween(...$this->participantIds($conversation));
    }
}
