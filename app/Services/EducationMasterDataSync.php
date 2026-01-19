<?php

namespace App\Services;

use App\Models\Discipline;
use App\Models\Institution;

class EducationMasterDataSync
{
    /**
     * Upsert institution/discipline values from an education array payload.
     *
     * Expected item shape (best-effort):
     * - ['university' => string, 'discipline' => string]
     * - ['institution' => string, 'discipline' => string]
     */
    public function syncFromEducationArray(array $education): void
    {
        $now = now();

        /** @var array<string, string> $institutions */
        $institutions = [];
        /** @var array<string, string> $disciplines */
        $disciplines = [];

        foreach ($education as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $institution = $entry['university'] ?? $entry['institution'] ?? null;
            $discipline = $entry['discipline'] ?? null;

            if (is_string($institution)) {
                $name = trim($institution);
                if ($name !== '') {
                    $institutions[Institution::normalizeName($name)] = $name;
                }
            }

            if (is_string($discipline)) {
                $name = trim($discipline);
                if ($name !== '') {
                    $disciplines[Discipline::normalizeName($name)] = $name;
                }
            }
        }

        if (!empty($institutions)) {
            $rows = [];
            foreach ($institutions as $normalized => $name) {
                $rows[] = [
                    'name' => $name,
                    'name_normalized' => $normalized,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Institution::query()->upsert(
                $rows,
                uniqueBy: ['name_normalized'],
                update: ['name', 'is_active', 'updated_at']
            );
        }

        if (!empty($disciplines)) {
            $rows = [];
            foreach ($disciplines as $normalized => $name) {
                $rows[] = [
                    'name' => $name,
                    'name_normalized' => $normalized,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Discipline::query()->upsert(
                $rows,
                uniqueBy: ['name_normalized'],
                update: ['name', 'is_active', 'updated_at']
            );
        }
    }
}

