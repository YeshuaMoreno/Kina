<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConnectionController extends Controller
{
    public function index(Request $request): View
    {
        $me = $request->user();
        $blockedIds = $me->blockedUserIds();

        $connections = Connection::query()
            ->where(fn ($q) => $q->where('user_one_id', $me->id)->orWhere('user_two_id', $me->id))
            ->with(['userOne.profile', 'userTwo.profile', 'conversation'])
            ->latest('connected_at')
            ->get()
            // Oculta conexiones con bloqueo de por medio.
            ->reject(function (Connection $c) use ($me, $blockedIds) {
                $otherId = $c->user_one_id === $me->id ? $c->user_two_id : $c->user_one_id;

                return in_array($otherId, $blockedIds, true);
            });

        // Garantiza que cada conexión tenga conversación (por si faltara).
        $connections->each(function (Connection $c) {
            if (! $c->conversation) {
                $c->setRelation('conversation', Conversation::firstOrCreate(['connection_id' => $c->id]));
            }
        });

        // Último mensaje por conversación en una sola consulta.
        $conversationIds = $connections->pluck('conversation.id')->filter();
        $lastMessages = Message::whereIn('conversation_id', $conversationIds)
            ->latest()
            ->get()
            ->groupBy('conversation_id')
            ->map->first();

        $items = $connections->map(function (Connection $c) use ($me, $lastMessages) {
            $other = $c->user_one_id === $me->id ? $c->userTwo : $c->userOne;

            return [
                'conversation' => $c->conversation,
                'other' => $other,
                'profile' => $other->profile,
                'last' => $lastMessages[$c->conversation->id] ?? null,
            ];
        })->sortByDesc(fn ($i) => optional($i['last'])->created_at ?? $i['conversation']->created_at)
            ->values();

        return view('conexiones.index', ['items' => $items]);
    }
}
