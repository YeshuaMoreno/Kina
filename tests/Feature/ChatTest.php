<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private function onboardedUser(string $name = 'Persona'): User
    {
        $user = User::factory()->create(['name' => $name, 'is_adult_confirmed' => true]);
        $user->profile()->create([
            'display_name' => $name,
            'looking_for' => 'amistad',
            'social_battery' => 'media',
            'profile_visibility' => 'publico',
            'onboarding_completed' => true,
        ]);

        return $user;
    }

    /** Crea conexión + conversación entre dos usuarios y devuelve la conversación. */
    private function connect(User $a, User $b): Conversation
    {
        [$one, $two] = $a->id < $b->id ? [$a->id, $b->id] : [$b->id, $a->id];
        $connection = Connection::create(['user_one_id' => $one, 'user_two_id' => $two, 'connected_at' => now()]);

        return Conversation::create(['connection_id' => $connection->id]);
    }

    public function test_lista_conexiones_aceptadas(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $this->connect($me, $friend);

        $this->actingAs($me)->get(route('conexiones.index'))
            ->assertOk()
            ->assertSee('Amiga');
    }

    public function test_no_muestra_usuarios_no_conectados_en_conexiones(): void
    {
        $me = $this->onboardedUser('Yo');
        $stranger = $this->onboardedUser('Desconocido');

        $this->actingAs($me)->get(route('conexiones.index'))
            ->assertOk()
            ->assertDontSee('Desconocido');
    }

    public function test_puede_abrir_conversacion_propia(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $conv = $this->connect($me, $friend);

        $this->actingAs($me)->get(route('conversaciones.show', $conv))
            ->assertOk()
            ->assertSee('Amiga');
    }

    public function test_no_puede_abrir_conversacion_ajena(): void
    {
        $a = $this->onboardedUser('A');
        $b = $this->onboardedUser('B');
        $intruder = $this->onboardedUser('Intruso');
        $conv = $this->connect($a, $b);

        $this->actingAs($intruder)->get(route('conversaciones.show', $conv))->assertForbidden();
    }

    public function test_puede_enviar_mensaje_en_conversacion_propia(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $conv = $this->connect($me, $friend);

        $this->actingAs($me)->post(route('conversaciones.mensajes.store', $conv), ['body' => 'Hola con calma'])
            ->assertRedirect(route('conversaciones.show', $conv));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'sender_id' => $me->id,
            'body' => 'Hola con calma',
        ]);
    }

    public function test_no_puede_enviar_mensaje_vacio(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $conv = $this->connect($me, $friend);

        $this->actingAs($me)->post(route('conversaciones.mensajes.store', $conv), ['body' => '   '])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_no_puede_enviar_mensaje_muy_largo(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $conv = $this->connect($me, $friend);

        $this->actingAs($me)->post(route('conversaciones.mensajes.store', $conv), ['body' => str_repeat('a', 2001)])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_no_puede_enviar_mensaje_en_conversacion_ajena(): void
    {
        $a = $this->onboardedUser('A');
        $b = $this->onboardedUser('B');
        $intruder = $this->onboardedUser('Intruso');
        $conv = $this->connect($a, $b);

        $this->actingAs($intruder)->post(route('conversaciones.mensajes.store', $conv), ['body' => 'colado'])
            ->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_no_puede_ver_conversacion_si_hay_bloqueo(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $conv = $this->connect($me, $friend);
        $me->blocks()->create(['blocked_id' => $friend->id]);

        $this->actingAs($me)->get(route('conversaciones.show', $conv))
            ->assertRedirect(route('conexiones.index'));
    }

    public function test_no_puede_enviar_mensaje_si_hay_bloqueo(): void
    {
        $me = $this->onboardedUser('Yo');
        $friend = $this->onboardedUser('Amiga');
        $conv = $this->connect($me, $friend);
        // El otro me bloqueó a mí.
        $friend->blocks()->create(['blocked_id' => $me->id]);

        $this->actingAs($me)->post(route('conversaciones.mensajes.store', $conv), ['body' => 'hola'])
            ->assertRedirect(route('conexiones.index'));

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_no_conectados_no_muestra_conversaciones_en_lista(): void
    {
        $me = $this->onboardedUser('Yo');
        // Sin conexiones.
        $this->actingAs($me)->get(route('conexiones.index'))
            ->assertOk()
            ->assertSee('Todavía no tienes conexiones', false);
    }
}
