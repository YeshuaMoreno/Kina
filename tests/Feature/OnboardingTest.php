<?php

namespace Tests\Feature;

use App\Models\IdentityTag;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirige_a_onboarding_si_no_esta_completo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('onboarding.basico'));
    }

    public function test_no_permite_continuar_si_es_menor_de_18(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.basico.store'), [
                'display_name' => 'Menor',
                'birthdate' => now()->subYears(15)->format('Y-m-d'),
                'confirm_adult' => '1',
            ])
            ->assertSessionHasErrors('birthdate');

        $this->assertFalse($user->fresh()->is_adult_confirmed);
    }

    public function test_exige_confirmacion_de_mayoria_de_edad(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.basico.store'), [
                'display_name' => 'Adulto',
                'birthdate' => now()->subYears(25)->format('Y-m-d'),
                // sin confirm_adult
            ])
            ->assertSessionHasErrors('confirm_adult');
    }

    public function test_etiqueta_sensible_requiere_consentimiento(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);
        $sensitive = IdentityTag::create(['name' => 'Autismo', 'is_sensitive' => true]);

        $this->actingAs($user)
            ->post(route('onboarding.etiquetas.store'), [
                'tags' => [$sensitive->id],
                // sin consent_sensitive
            ])
            ->assertSessionHasErrors('consent_sensitive');
    }

    public function test_cada_paso_renderiza(): void
    {
        $user = User::factory()->create();
        Interest::create(['name' => 'Cine', 'category' => 'Arte']);
        IdentityTag::create(['name' => 'Autismo', 'is_sensitive' => true]);

        $this->actingAs($user);

        foreach (['basico', 'intencion', 'intereses', 'comunicacion', 'etiquetas', 'privacidad', 'foto'] as $step) {
            $this->get(route("onboarding.$step"))
                ->assertOk()
                ->assertSee('Kina', false);
        }
    }

    public function test_flujo_completo_de_onboarding(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $interest = Interest::create(['name' => 'Lectura', 'category' => 'Arte']);
        $sensitive = IdentityTag::create(['name' => 'TDAH', 'is_sensitive' => true]);
        $normal = IdentityTag::create(['name' => 'Introvertido', 'is_sensitive' => false]);

        // Paso 1
        $this->actingAs($user)->post(route('onboarding.basico.store'), [
            'display_name' => 'Rylm',
            'birthdate' => '1995-05-20',
            'city' => 'CDMX',
            'confirm_adult' => '1',
        ])->assertRedirect(route('onboarding.intencion'));

        $user->refresh();
        $this->assertTrue($user->is_adult_confirmed);
        $this->assertSame('Rylm', $user->profile->display_name);
        $this->assertDatabaseHas('consents', ['user_id' => $user->id, 'consent_type' => 'age_18', 'accepted' => true]);

        // Paso 2
        $this->post(route('onboarding.intencion.store'), ['looking_for' => 'amistad'])
            ->assertRedirect(route('onboarding.intereses'));

        // Paso 3
        $this->post(route('onboarding.intereses.store'), ['interests' => [$interest->id]])
            ->assertRedirect(route('onboarding.comunicacion'));
        $this->assertDatabaseHas('interest_profile', [
            'profile_id' => $user->profile->id,
            'interest_id' => $interest->id,
        ]);

        // Paso 4
        $this->post(route('onboarding.comunicacion.store'), [
            'social_battery' => 'media',
            'prefers_text' => '1',
            'no_surprise_calls' => '1',
        ])->assertRedirect(route('onboarding.etiquetas'));
        $this->assertDatabaseHas('communication_preferences', [
            'profile_id' => $user->profile->id,
            'prefers_text' => true,
            'no_surprise_calls' => true,
            'direct_communication' => false,
        ]);

        // Paso 5 (con consentimiento sensible)
        $this->post(route('onboarding.etiquetas.store'), [
            'tags' => [$sensitive->id, $normal->id],
            'consent_sensitive' => '1',
        ])->assertRedirect(route('onboarding.privacidad'));

        // La sensible queda oculta por default
        $this->assertDatabaseHas('identity_tag_profile', [
            'profile_id' => $user->profile->id,
            'identity_tag_id' => $sensitive->id,
            'is_visible' => false,
            'visibility' => 'nunca',
        ]);
        $this->assertDatabaseHas('consents', ['user_id' => $user->id, 'consent_type' => 'sensitive_data']);

        // Paso 6
        $this->post(route('onboarding.privacidad.store'), [
            'profile_visibility' => 'solo_conexiones',
            'sensitive_tags_visibility' => 'solo_conexiones',
            'show_sensitive_tags' => '1',
            'consent_privacy' => '1',
        ])->assertRedirect(route('onboarding.foto'));

        // La decisión se propaga a la etiqueta sensible
        $this->assertDatabaseHas('identity_tag_profile', [
            'identity_tag_id' => $sensitive->id,
            'is_visible' => true,
            'visibility' => 'solo_conexiones',
        ]);

        // Paso 7 (con foto). Usamos create() con MIME para no depender de GD.
        $this->post(route('onboarding.foto.store'), [
            'photo' => UploadedFile::fake()->create('yo.jpg', 500, 'image/jpeg'),
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertTrue($user->profile->onboarding_completed);
        $this->assertDatabaseHas('profile_photos', [
            'user_id' => $user->id,
            'is_primary' => true,
            'status' => 'approved',
        ]);

        // Ya con onboarding completo, el dashboard es accesible
        $this->get('/dashboard')->assertOk();
    }
}
