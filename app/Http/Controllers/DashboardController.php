<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\ConnectionRequest;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $me = $request->user();
        $profile = $me->profile()->with(['interests', 'identityTags'])->first();

        $pendingRequests = ConnectionRequest::where('receiver_id', $me->id)
            ->where('status', 'pending')
            ->count();

        $connectionIds = Connection::query()
            ->where(fn ($q) => $q->where('user_one_id', $me->id)->orWhere('user_two_id', $me->id))
            ->pluck('id');

        $connectionsCount = $connectionIds->count();

        $conversationIds = Conversation::whereIn('connection_id', $connectionIds)->pluck('id');
        $unreadMessages = Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $me->id)
            ->whereNull('read_at')
            ->count();

        return view('dashboard', [
            'me' => $me,
            'profile' => $profile,
            'pendingRequests' => $pendingRequests,
            'connectionsCount' => $connectionsCount,
            'unreadMessages' => $unreadMessages,
        ]);
    }
}
