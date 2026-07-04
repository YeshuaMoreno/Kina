<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendConnectionRequestRequest;
use App\Models\Connection;
use App\Models\ConnectionRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ProfileVisibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConnectionRequestController extends Controller
{
    public function __construct(private readonly ProfileVisibilityService $visibility) {}

    /** Bandeja de solicitudes recibidas (pendientes). */
    public function index(Request $request): View
    {
        $received = ConnectionRequest::with('sender.profile', 'sender.photos')
            ->where('receiver_id', $request->user()->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $sent = ConnectionRequest::with('receiver.profile')
            ->where('sender_id', $request->user()->id)
            ->latest()
            ->get();

        return view('solicitudes.index', compact('received', 'sent'));
    }

    /** Enviar solicitud de conexión. */
    public function store(SendConnectionRequestRequest $request, User $user): RedirectResponse
    {
        $me = $request->user();

        if ($me->id === $user->id) {
            return back()->with('status', 'No puedes conectarte contigo mismo.');
        }

        if (in_array($user->id, $me->blockedUserIds(), true)) {
            return back()->with('status', 'No puedes conectar con esta persona.');
        }

        if ($user->is_suspended || ! $this->visibility->canView($me, $user)) {
            return back()->with('status', 'Ese perfil no está disponible.');
        }

        if ($this->visibility->areConnected($me, $user)) {
            return back()->with('status', 'Ya están conectados.');
        }

        // ¿Existe alguna solicitud en cualquier dirección?
        $pending = ConnectionRequest::query()
            ->where('status', 'pending')
            ->where(function ($q) use ($me, $user) {
                $q->where(fn ($q) => $q->where('sender_id', $me->id)->where('receiver_id', $user->id))
                    ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('receiver_id', $me->id));
            })
            ->exists();

        if ($pending) {
            return back()->with('status', 'Ya hay una solicitud pendiente entre ustedes.');
        }

        // Reintento permitido si una previa fue rechazada/cancelada (misma dirección).
        ConnectionRequest::updateOrCreate(
            ['sender_id' => $me->id, 'receiver_id' => $user->id],
            ['status' => 'pending', 'message' => $request->validated()['message'] ?? null],
        );

        return back()->with('status', 'Solicitud enviada. Te avisaremos si la aceptan.');
    }

    /** Aceptar una solicitud recibida: crea conexión y conversación. */
    public function accept(Request $request, ConnectionRequest $connectionRequest): RedirectResponse
    {
        abort_unless($connectionRequest->receiver_id === $request->user()->id, 403);

        if ($connectionRequest->status !== 'pending') {
            return redirect()->route('solicitudes.index')->with('status', 'Esa solicitud ya no está pendiente.');
        }

        DB::transaction(function () use ($connectionRequest) {
            $connectionRequest->update(['status' => 'accepted']);

            [$one, $two] = $connectionRequest->sender_id < $connectionRequest->receiver_id
                ? [$connectionRequest->sender_id, $connectionRequest->receiver_id]
                : [$connectionRequest->receiver_id, $connectionRequest->sender_id];

            $connection = Connection::firstOrCreate(
                ['user_one_id' => $one, 'user_two_id' => $two],
                ['connected_at' => now()],
            );

            Conversation::firstOrCreate(['connection_id' => $connection->id]);
        });

        return redirect()->route('solicitudes.index')->with('status', '¡Nueva conexión! Ya pueden escribirse.');
    }

    /** Rechazar una solicitud recibida. */
    public function reject(Request $request, ConnectionRequest $connectionRequest): RedirectResponse
    {
        abort_unless($connectionRequest->receiver_id === $request->user()->id, 403);

        if ($connectionRequest->status === 'pending') {
            $connectionRequest->update(['status' => 'rejected']);
        }

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud rechazada.');
    }
}
