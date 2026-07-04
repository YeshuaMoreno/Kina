<?php

namespace App\Http\Controllers;

use App\Models\ConnectionRequest;
use App\Models\User;
use App\Services\CompatibilityService;
use App\Services\ProfileVisibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function __construct(
        private readonly CompatibilityService $compatibility,
        private readonly ProfileVisibilityService $visibility,
    ) {}

    public function show(Request $request, User $user): View|RedirectResponse
    {
        $me = $request->user();

        // Tu propio perfil público -> te mandamos a editarlo.
        if ($me->id === $user->id) {
            return redirect()->route('profile.edit');
        }

        if (! $this->visibility->canView($me, $user)) {
            return redirect()->route('descubrir.index')
                ->with('status', 'Ese perfil no está disponible.');
        }

        $user->load([
            'profile.interests',
            'profile.communicationPreference',
            'profile.identityTags',
            'photos' => fn ($q) => $q->where('is_primary', true)->where('status', 'approved'),
        ]);

        $result = $this->compatibility->evaluate($me->profile, $user->profile);

        // Estado de la relación para los botones.
        $outgoing = ConnectionRequest::where('sender_id', $me->id)->where('receiver_id', $user->id)->first();
        $incoming = ConnectionRequest::where('sender_id', $user->id)->where('receiver_id', $me->id)->first();

        return view('perfiles.show', [
            'user' => $user,
            'profile' => $user->profile,
            'photo' => $user->photos->first(),
            'sintonias' => $result['sintonias'],
            'visibleTags' => $this->visibility->visibleTags($me, $user->profile),
            'connected' => $this->visibility->areConnected($me, $user),
            'outgoing' => $outgoing,
            'incoming' => $incoming,
            'lookingForLabels' => DiscoverController::lookingForLabels(),
            'commLabels' => $this->commLabels(),
        ]);
    }

    /** @return array<string,string> */
    private function commLabels(): array
    {
        return [
            'prefers_text' => 'Prefiere texto antes que llamada',
            'direct_communication' => 'Le gusta la comunicación directa',
            'slow_responder' => 'A veces tarda en responder',
            'prefers_quiet_plans' => 'Prefiere planes tranquilos',
            'chat_before_meeting' => 'Prefiere conocer primero por chat',
            'no_surprise_calls' => 'No le gustan las llamadas sorpresa',
        ];
    }
}
