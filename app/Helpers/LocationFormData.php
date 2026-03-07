<?php

namespace App\Helpers;

use App\Models\GeopoliticalZone;
use App\Models\State;

class LocationFormData
{
    /**
     * Get location data for forms (zone, state, LGA dropdowns).
     * Used by onboarding, officer edit, recruit forms so "add once" in HRD Location Data "shows everywhere".
     *
     * @return array{zoneNames: array, stateNames: array, stateToZoneMap: array<string, string>, stateLgas: array<string, array>}
     */
    public static function getForForms(): array
    {
        $zones = GeopoliticalZone::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $states = State::where('is_active', true)
            ->with(['geopoliticalZone', 'lgas' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $zoneNames = $zones->pluck('name')->values()->all();
        $stateNames = $states->pluck('name')->values()->all();

        $stateToZoneMap = [];
        $stateLgas = [];
        foreach ($states as $state) {
            $stateToZoneMap[$state->name] = $state->geopoliticalZone?->name ?? '';
            $stateLgas[$state->name] = $state->lgas->pluck('name')->values()->all();
        }

        return [
            'zoneNames' => $zoneNames,
            'stateNames' => $stateNames,
            'stateToZoneMap' => $stateToZoneMap,
            'stateLgas' => $stateLgas,
        ];
    }
}
