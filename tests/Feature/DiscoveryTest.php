<?php

namespace Tests\Feature;

use App\Models\ConnectionRequest;
use App\Models\IdentityTag;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryTest extends TestCase
{
    use RefreshDatabase;

    /** Crea un usuario con perfil completo de onboarding. */
    private function onboardedUser(array $profile = [], string $name = 'Persona'): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'is_adult_confirmed' => true,
        ]);

        $user->profile()->create(array_merge([
            'display_name' => $name,
            'looking_for' => 'amistad',
            'social_battery' => 'media',
            'profile_visibility' => 'publico',
            'onboarding_completed' => true,
        ], $profile));

        return $user;
    }

    public function test_no_muestra_bloqueados_en_descubrir(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $blocked = $this->onboardedUser([], 'Bloqueado');
        $visible = $this->onboardedUser([], 'Visible');

        $me->blocks()->create(['blocked_id' => $blocked->id]);

        $this->actingAs($me)->get(route('descubrir.index'))
            ->assertOk()
            ->assertSee('Visible')
            ->assertDontSee('Bloqueado');
    }

    public function test_no_muestra_a_quien_me_bloqueo_en_descubrir(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $blocker = $this->onboardedUser([], 'MeBloqueo');

        $blocker->blocks()->create(['blocked_id' => $me->id]);

        $this->actingAs($me)->get(route('descubrir.index'))
            ->assertOk()
            ->assertDontSee('MeBloqueo');
    }

    public function test_no_permite_ver_perfil_bloqueado(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $blocked = $this->onboardedUser([], 'Bloqueado');
        $me->blocks()->create(['blocked_id' => $blocked->id]);

        $this->actingAs($me)->get(route('perfiles.show', $blocked))
            ->assertRedirect(route('descubrir.index'));
    }

    public function test_no_muestra_perfil_con_visibilidad_nunca(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $hidden = $this->onboardedUser(['profile_visibility' => 'nunca'], 'Oculto');

        $this->actingAs($me)->get(route('perfiles.show', $hidden))
            ->assertRedirect(route('descubrir.index'));
    }

    public function test_no_muestra_etiquetas_sensibles_sin_permiso(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $other = $this->onboardedUser([
            'show_sensitive_tags' => false,
            'sensitive_tags_visibility' => 'nunca',
        ], 'Otro');

        $tag = IdentityTag::create(['name' => 'Autismo', 'is_sensitive' => true]);
        $other->profile->identityTags()->attach($tag->id, ['is_visible' => false, 'visibility' => 'nunca']);

        $this->actingAs($me)->get(route('perfiles.show', $other))
            ->assertOk()
            ->assertDontSee('Autismo');
    }

    public function test_muestra_etiquetas_sensibles_cuando_visibilidad_lo_permite(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $other = $this->onboardedUser([
            'show_sensitive_tags' => true,
            'sensitive_tags_visibility' => 'publico',
        ], 'Otro');

        $tag = IdentityTag::create(['name' => 'Autismo', 'is_sensitive' => true]);
        $other->profile->identityTags()->attach($tag->id, ['is_visible' => true, 'visibility' => 'publico']);

        $this->actingAs($me)->get(route('perfiles.show', $other))
            ->assertOk()
            ->assertSee('Autismo');
    }

    public function test_puede_enviar_solicitud_de_conexion(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $other = $this->onboardedUser([], 'Otro');

        $this->actingAs($me)->post(route('perfiles.conectar', $other), ['message' => 'Hola'])
            ->assertRedirect();

        $this->assertDatabaseHas('connection_requests', [
            'sender_id' => $me->id,
            'receiver_id' => $other->id,
            'status' => 'pending',
            'message' => 'Hola',
        ]);
    }

    public function test_puede_aceptar_solicitud_y_crea_conexion_y_conversacion(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $other = $this->onboardedUser([], 'Otro');

        $req = ConnectionRequest::create([
            'sender_id' => $other->id,
            'receiver_id' => $me->id,
            'status' => 'pending',
        ]);

        $this->actingAs($me)->post(route('solicitudes.aceptar', $req))
            ->assertRedirect(route('solicitudes.index'));

        $this->assertDatabaseHas('connection_requests', ['id' => $req->id, 'status' => 'accepted']);

        [$one, $two] = $me->id < $other->id ? [$me->id, $other->id] : [$other->id, $me->id];
        $connection = \App\Models\Connection::where('user_one_id', $one)->where('user_two_id', $two)->first();
        $this->assertNotNull($connection, 'Debe existir la conexión entre ambos.');

        // La conversación debe quedar ligada a ESA conexión.
        $this->assertDatabaseHas('conversations', ['connection_id' => $connection->id]);
        $this->assertSame(1, $connection->conversation()->count());
    }

    public function test_puede_rechazar_solicitud(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $other = $this->onboardedUser([], 'Otro');

        $req = ConnectionRequest::create([
            'sender_id' => $other->id,
            'receiver_id' => $me->id,
            'status' => 'pending',
        ]);

        $this->actingAs($me)->post(route('solicitudes.rechazar', $req))
            ->assertRedirect(route('solicitudes.index'));

        $this->assertDatabaseHas('connection_requests', ['id' => $req->id, 'status' => 'rejected']);
        $this->assertDatabaseMissing('connections', []);
    }

    public function test_no_puede_aceptar_solicitud_ajena(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $a = $this->onboardedUser([], 'A');
        $b = $this->onboardedUser([], 'B');

        $req = ConnectionRequest::create(['sender_id' => $a->id, 'receiver_id' => $b->id, 'status' => 'pending']);

        $this->actingAs($me)->post(route('solicitudes.aceptar', $req))->assertForbidden();
    }

    public function test_puede_reportar_usuario(): void
    {
        $me = $this->onboardedUser([], 'Yo');
        $other = $this->onboardedUser([], 'Otro');

        $this->actingAs($me)->post(route('perfiles.reportar.store', $other), [
            'reason' => 'acoso',
            'description' => 'Mensajes molestos',
        ])->assertRedirect(route('descubrir.index'));

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $me->id,
            'reported_id' => $other->id,
            'reason' => 'acoso',
            'status' => 'pending',
        ]);
    }

    public function test_no_puede_reportarse_a_si_mismo(): void
    {
        $me = $this->onboardedUser([], 'Yo');

        $this->actingAs($me)->post(route('perfiles.reportar.store', $me), ['reason' => 'acoso'])
            ->assertRedirect(route('descubrir.index'));

        $this->assertDatabaseMissing('reports', ['reporter_id' => $me->id, 'reported_id' => $me->id]);
    }

    public function test_no_puede_bloquearse_a_si_mismo(): void
    {
        $me = $this->onboardedUser([], 'Yo');

        $this->actingAs($me)->post(route('perfiles.bloquear', $me))->assertRedirect();

        $this->assertDatabaseMissing('blocks', ['blocker_id' => $me->id, 'blocked_id' => $me->id]);
    }
}
