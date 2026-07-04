<?php

namespace App\Services;

use App\Models\Profile;

/**
 * Calcula compatibilidad simple entre dos perfiles (0-100) y traduce el
 * resultado a "Áreas de Sintonía" legibles. El puntaje NO se muestra al usuario.
 */
class CompatibilityService
{
    private const COMM_FIELDS = [
        'prefers_text',
        'direct_communication',
        'slow_responder',
        'prefers_quiet_plans',
        'chat_before_meeting',
        'no_surprise_calls',
    ];

    private const BATTERY = ['baja' => 1, 'media' => 2, 'alta' => 3];

    /**
     * @return array{score:int, sintonias:array<int,string>}
     */
    public function evaluate(Profile $viewer, Profile $candidate): array
    {
        $sintonias = [];

        // --- Intereses compartidos: hasta 40 ---
        $shared = $viewer->interests->pluck('id')
            ->intersect($candidate->interests->pluck('id'))
            ->count();
        $interestPoints = min(40, $shared * 10);

        if ($shared >= 3) {
            $sintonias[] = 'Coinciden en varios intereses';
        } elseif ($shared >= 1) {
            $sintonias[] = 'Comparten algún interés';
        }

        // --- Misma intención: hasta 25 ---
        $sameIntent = $viewer->looking_for && $viewer->looking_for === $candidate->looking_for;
        $intentPoints = $sameIntent ? 25 : 0;
        if ($sameIntent) {
            $sintonias[] = 'Buscan algo similar';
        }

        // --- Preferencias de comunicación similares: hasta 20 ---
        $vp = $viewer->communicationPreference;
        $cp = $candidate->communicationPreference;
        $commMatches = 0;
        if ($vp && $cp) {
            foreach (self::COMM_FIELDS as $field) {
                if ((bool) $vp->{$field} === (bool) $cp->{$field}) {
                    $commMatches++;
                }
            }
        }
        $commPoints = ($vp && $cp) ? (int) round($commMatches / count(self::COMM_FIELDS) * 20) : 0;

        if ($commMatches >= 4) {
            $sintonias[] = 'Buena compatibilidad de comunicación';
        }
        if ($vp && $cp && $vp->prefers_text && $cp->prefers_text) {
            $sintonias[] = 'Ambos prefieren hablar por chat';
        }
        if ($vp && $cp && $vp->prefers_quiet_plans && $cp->prefers_quiet_plans) {
            $sintonias[] = 'Ambos prefieren planes tranquilos';
        }

        // --- Batería social compatible: hasta 10 ---
        $batteryPoints = 0;
        if (isset(self::BATTERY[$viewer->social_battery], self::BATTERY[$candidate->social_battery])) {
            $diff = abs(self::BATTERY[$viewer->social_battery] - self::BATTERY[$candidate->social_battery]);
            $batteryPoints = match ($diff) {
                0 => 10,
                1 => 5,
                default => 0,
            };
            if ($diff <= 1) {
                $sintonias[] = 'Batería social compatible';
            }
        }

        // --- Misma ciudad/estado: hasta 5 ---
        $locationPoints = 0;
        if ($this->sameText($viewer->city, $candidate->city)) {
            $locationPoints = 5;
            $sintonias[] = 'Cerca de tu zona';
        } elseif ($this->sameText($viewer->state, $candidate->state)) {
            $locationPoints = 3;
            $sintonias[] = 'Cerca de tu zona';
        }

        $score = min(100, $interestPoints + $intentPoints + $commPoints + $batteryPoints + $locationPoints);

        // Cierre cálido si hay poco que resaltar pero algo de afinidad.
        if (empty($sintonias) && $score > 0) {
            $sintonias[] = 'Podría ser buena conexión';
        }

        return [
            'score' => $score,
            'sintonias' => array_values(array_unique(array_slice($sintonias, 0, 5))),
        ];
    }

    private function sameText(?string $a, ?string $b): bool
    {
        if (! $a || ! $b) {
            return false;
        }

        return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
    }
}
