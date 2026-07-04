<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\ConnectionRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        if ($me->id === $user->id) {
            return back()->with('status', 'No puedes bloquearte a ti mismo.');
        }

        Block::firstOrCreate([
            'blocker_id' => $me->id,
            'blocked_id' => $user->id,
        ]);

        // Cancela cualquier solicitud pendiente entre ambos.
        ConnectionRequest::where('status', 'pending')
            ->where(function ($q) use ($me, $user) {
                $q->where(fn ($q) => $q->where('sender_id', $me->id)->where('receiver_id', $user->id))
                    ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('receiver_id', $me->id));
            })
            ->update(['status' => 'cancelled']);

        return redirect()->route('descubrir.index')
            ->with('status', 'Has bloqueado a esta persona. No volverá a aparecer ni podrá contactarte.');
    }
}
