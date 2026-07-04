<?php

namespace App\Http\Controllers;

use App\Models\ConnectionRequest;
use App\Models\User;
use App\Services\CompatibilityService;
use App\Services\ProfileVisibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function __construct(
        private readonly CompatibilityService $compatibility,
        private readonly ProfileVisibilityService $visibility,
    ) {}

    public function index(Request $request): View
    {
        $me = $request->user();
        $myProfile = $me->profile;

        $blockedIds = $me->blockedUserIds();
        $connectedIds = $this->visibility->connectedUserIds($me);

        $candidates = User::query()
            ->where('id', '!=', $me->id)
            ->where('is_suspended', false)
            ->when($blockedIds, fn ($q) => $q->whereNotIn('id', $blockedIds))
            ->whereHas('profile', function ($q) use ($connectedIds) {
                $q->where('onboarding_completed', true)
                    ->where(function ($q) use ($connectedIds) {
                        $q->where('profile_visibility', 'publico')
                            ->orWhere(function ($q) use ($connectedIds) {
                                $q->where('profile_visibility', 'solo_conexiones')
                                    ->whereIn('user_id', $connectedIds ?: [0]);
                            });
                    });
            })
            ->with([
                'profile.interests',
                'profile.communicationPreference',
                'profile.identityTags',
                'photos' => fn ($q) => $q->where('is_primary', true)->where('status', 'approved'),
            ])
            ->limit(60)
            ->get();

        // Solicitudes que YO ya envié (para el estado del botón).
        $sentStatuses = ConnectionRequest::where('sender_id', $me->id)
            ->whereIn('receiver_id', $candidates->pluck('id'))
            ->pluck('status', 'receiver_id');

        $people = $candidates
            ->map(function (User $candidate) use ($myProfile, $connectedIds, $sentStatuses) {
                $result = $this->compatibility->evaluate($myProfile, $candidate->profile);

                return [
                    'user' => $candidate,
                    'sintonias' => $result['sintonias'],
                    'score' => $result['score'],
                    'interests' => $candidate->profile->interests->take(5),
                    'photo' => $candidate->photos->first(),
                    'connected' => in_array($candidate->id, $connectedIds, true),
                    'request_status' => $sentStatuses[$candidate->id] ?? null,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(30);

        return view('descubrir.index', [
            'people' => $people,
            'lookingForLabels' => $this->lookingForLabels(),
        ]);
    }

    /** @return array<string,string> */
    public static function lookingForLabels(): array
    {
        return [
            'amistad' => 'Amistad',
            'pareja_formal' => 'Pareja formal',
            'algo_casual' => 'Algo casual',
            'comunidad' => 'Comunidad',
        ];
    }
}
