<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(private readonly ChatAccessService $chat) {}

    public function show(Request $request, Conversation $conversation): View|RedirectResponse
    {
        $me = $request->user();
        $conversation->load('connection');

        // Conversación ajena -> 403.
        abort_unless($this->chat->isParticipant($me, $conversation), 403);

        // Bloqueo de por medio -> no se puede ver.
        if (! $this->chat->canAccess($me, $conversation)) {
            return redirect()->route('conexiones.index')
                ->with('status', 'Esta conversación no está disponible.');
        }

        // Marca como leídos los mensajes entrantes.
        $conversation->messages()
            ->where('sender_id', '!=', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $other = User::with('profile', 'photos')->find($this->chat->otherUserId($me, $conversation));

        $messages = $conversation->messages()->orderBy('created_at')->get();

        return view('conversaciones.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'other' => $other,
            'photo' => $other->photos->firstWhere('is_primary', true),
        ]);
    }

    public function storeMessage(StoreMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        $me = $request->user();
        $conversation->load('connection');

        abort_unless($this->chat->isParticipant($me, $conversation), 403);

        if (! $this->chat->canAccess($me, $conversation)) {
            return redirect()->route('conexiones.index')
                ->with('status', 'No puedes enviar mensajes en esta conversación.');
        }

        $conversation->messages()->create([
            'sender_id' => $me->id,
            'body' => $request->validated()['body'],
        ]);

        return redirect()->route('conversaciones.show', $conversation);
    }
}
