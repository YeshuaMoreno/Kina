<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\BasicInfoRequest;
use App\Http\Requests\Onboarding\CommunicationRequest;
use App\Http\Requests\Onboarding\IntentionRequest;
use App\Http\Requests\Onboarding\InterestsRequest;
use App\Http\Requests\Onboarding\PhotoRequest;
use App\Http\Requests\Onboarding\PrivacyRequest;
use App\Http\Requests\Onboarding\TagsRequest;
use App\Models\IdentityTag;
use App\Models\Interest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /** Devuelve (o crea) el perfil del usuario autenticado. */
    private function profile(Request $request): Profile
    {
        return $request->user()->profile()->firstOrCreate([]);
    }

    /** Registra un consentimiento con trazabilidad. */
    private function recordConsent(Request $request, string $type, bool $accepted): void
    {
        $request->user()->consents()->create([
            'consent_type' => $type,
            'accepted' => $accepted,
            'accepted_at' => $accepted ? now() : null,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }

    // ----- Paso 1: Datos básicos -----

    public function basicoShow(Request $request): View
    {
        return view('onboarding.basico', [
            'profile' => $this->profile($request),
            'user' => $request->user(),
        ]);
    }

    public function basicoStore(BasicInfoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->update([
            'birthdate' => $data['birthdate'],
            'is_adult_confirmed' => true,
        ]);

        $this->profile($request)->update([
            'display_name' => $data['display_name'],
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
        ]);

        $this->recordConsent($request, 'age_18', true);

        return redirect()->route('onboarding.intencion');
    }

    // ----- Paso 2: Qué buscas -----

    public function intencionShow(Request $request): View
    {
        return view('onboarding.intencion', [
            'profile' => $this->profile($request),
        ]);
    }

    public function intencionStore(IntentionRequest $request): RedirectResponse
    {
        $this->profile($request)->update([
            'looking_for' => $request->validated()['looking_for'],
        ]);

        return redirect()->route('onboarding.intereses');
    }

    // ----- Paso 3: Intereses -----

    public function interesesShow(Request $request): View
    {
        $profile = $this->profile($request);

        return view('onboarding.intereses', [
            'profile' => $profile,
            'interestsByCategory' => Interest::orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'selected' => $profile->interests()->pluck('interests.id')->all(),
        ]);
    }

    public function interesesStore(InterestsRequest $request): RedirectResponse
    {
        $this->profile($request)->interests()->sync($request->validated()['interests']);

        return redirect()->route('onboarding.comunicacion');
    }

    // ----- Paso 4: Estilo de comunicación -----

    public function comunicacionShow(Request $request): View
    {
        $profile = $this->profile($request);

        return view('onboarding.comunicacion', [
            'profile' => $profile,
            'preferences' => $profile->communicationPreference,
        ]);
    }

    public function comunicacionStore(CommunicationRequest $request): RedirectResponse
    {
        $profile = $this->profile($request);

        $profile->update([
            'social_battery' => $request->validated()['social_battery'],
        ]);

        $profile->communicationPreference()->updateOrCreate([], [
            'prefers_text' => $request->boolean('prefers_text'),
            'direct_communication' => $request->boolean('direct_communication'),
            'slow_responder' => $request->boolean('slow_responder'),
            'prefers_quiet_plans' => $request->boolean('prefers_quiet_plans'),
            'chat_before_meeting' => $request->boolean('chat_before_meeting'),
            'no_surprise_calls' => $request->boolean('no_surprise_calls'),
        ]);

        return redirect()->route('onboarding.etiquetas');
    }

    // ----- Paso 5: Etiquetas opcionales + consentimiento -----

    public function etiquetasShow(Request $request): View
    {
        $profile = $this->profile($request);

        return view('onboarding.etiquetas', [
            'profile' => $profile,
            'tags' => IdentityTag::orderBy('is_sensitive')->orderBy('name')->get(),
            'selected' => $profile->identityTags()->pluck('identity_tags.id')->all(),
        ]);
    }

    public function etiquetasStore(TagsRequest $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $tagIds = $request->validated()['tags'] ?? [];

        $sensitiveIds = IdentityTag::whereIn('id', $tagIds)
            ->where('is_sensitive', true)
            ->pluck('id')
            ->all();

        // Construye el pivote: las sensibles quedan ocultas por default.
        $sync = [];
        foreach ($tagIds as $id) {
            $isSensitive = in_array((int) $id, array_map('intval', $sensitiveIds), true);
            $sync[$id] = [
                'is_visible' => ! $isSensitive,
                'visibility' => $isSensitive ? 'nunca' : 'publico',
            ];
        }

        $profile->identityTags()->sync($sync);

        if (! empty($sensitiveIds)) {
            $this->recordConsent($request, 'sensitive_data', $request->boolean('consent_sensitive'));
        }

        return redirect()->route('onboarding.privacidad');
    }

    // ----- Paso 6: Privacidad -----

    public function privacidadShow(Request $request): View
    {
        return view('onboarding.privacidad', [
            'profile' => $this->profile($request),
        ]);
    }

    public function privacidadStore(PrivacyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $profile = $this->profile($request);

        $showSensitive = $request->boolean('show_sensitive_tags');

        $profile->update([
            'profile_visibility' => $data['profile_visibility'],
            'sensitive_tags_visibility' => $data['sensitive_tags_visibility'],
            'show_sensitive_tags' => $showSensitive,
        ]);

        // Propaga la decisión a las etiquetas sensibles ya guardadas.
        $sensitiveTagIds = $profile->identityTags()->where('is_sensitive', true)->pluck('identity_tags.id')->all();
        foreach ($sensitiveTagIds as $tagId) {
            $profile->identityTags()->updateExistingPivot($tagId, [
                'is_visible' => $showSensitive,
                'visibility' => $data['sensitive_tags_visibility'],
            ]);
        }

        $this->recordConsent($request, 'privacy_policy', true);

        return redirect()->route('onboarding.foto');
    }

    // ----- Paso 7: Foto opcional (fin del onboarding) -----

    public function fotoShow(Request $request): View
    {
        return view('onboarding.foto', [
            'profile' => $this->profile($request),
        ]);
    }

    public function fotoStore(PhotoRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $this->profile($request);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile-photos', 'public');

            DB::transaction(function () use ($user, $path) {
                $user->photos()->update(['is_primary' => false]);
                $user->photos()->create([
                    'path' => $path,
                    'is_primary' => true,
                    'status' => 'approved',
                ]);
            });
        }

        $profile->update(['onboarding_completed' => true]);

        return redirect()->route('dashboard')
            ->with('status', '¡Tu espacio en Kina está listo!');
    }
}
