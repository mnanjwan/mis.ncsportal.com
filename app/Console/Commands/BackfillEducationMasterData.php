<?php

namespace App\Console\Commands;

use App\Models\Discipline;
use App\Models\Institution;
use App\Models\Qualification;
use App\Models\Officer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillEducationMasterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'education:backfill-master-data {--dry-run : Do not write to DB} {--chunk=500 : Officers per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill institutions/disciplines master lists from existing officer education data';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $this->info('Backfilling education master data...');
        $this->line('Dry-run: ' . ($dryRun ? 'yes' : 'no') . ' | Chunk: ' . $chunkSize);

        $totalOfficers = 0;
        $invalidEducationJson = 0;
        $institutionsSeen = 0;
        $disciplinesSeen = 0;
        $qualificationsSeen = 0;
        $institutionsUpserted = 0;
        $disciplinesUpserted = 0;
        $qualificationsUpserted = 0;

        /** @var array<string, string> $institutionBuffer */
        $institutionBuffer = [];
        /** @var array<string, string> $disciplineBuffer */
        $disciplineBuffer = [];
        /** @var array<string, string> $qualificationBuffer */
        $qualificationBuffer = [];

        $flushBuffers = function () use (
            &$institutionBuffer,
            &$disciplineBuffer,
            &$qualificationBuffer,
            &$institutionsUpserted,
            &$disciplinesUpserted,
            &$qualificationsUpserted,
            $dryRun
        ): void {
            if ($dryRun) {
                $institutionsUpserted += count($institutionBuffer);
                $disciplinesUpserted += count($disciplineBuffer);
                $qualificationsUpserted += count($qualificationBuffer);
                $institutionBuffer = [];
                $disciplineBuffer = [];
                $qualificationBuffer = [];
                return;
            }

            if (!empty($institutionBuffer)) {
                $rows = [];
                foreach ($institutionBuffer as $normalized => $name) {
                    $rows[] = [
                        'name' => $name,
                        'name_normalized' => $normalized,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Institution::query()->upsert(
                    $rows,
                    uniqueBy: ['name_normalized'],
                    update: ['name', 'is_active', 'updated_at']
                );

                $institutionsUpserted += count($institutionBuffer);
                $institutionBuffer = [];
            }

            if (!empty($disciplineBuffer)) {
                $rows = [];
                foreach ($disciplineBuffer as $normalized => $name) {
                    $rows[] = [
                        'name' => $name,
                        'name_normalized' => $normalized,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Discipline::query()->upsert(
                    $rows,
                    uniqueBy: ['name_normalized'],
                    update: ['name', 'is_active', 'updated_at']
                );

                $disciplinesUpserted += count($disciplineBuffer);
                $disciplineBuffer = [];
            }

            if (!empty($qualificationBuffer)) {
                $rows = [];
                foreach ($qualificationBuffer as $normalized => $name) {
                    $rows[] = [
                        'name' => $name,
                        'name_normalized' => $normalized,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Qualification::query()->upsert(
                    $rows,
                    uniqueBy: ['name_normalized'],
                    update: ['name', 'is_active', 'updated_at']
                );

                $qualificationsUpserted += count($qualificationBuffer);
                $qualificationBuffer = [];
            }
        };

        $addInstitution = function (?string $name) use (&$institutionBuffer, &$institutionsSeen): void {
            $name = trim((string) ($name ?? ''));
            if ($name === '') {
                return;
            }
            $normalized = Institution::normalizeName($name);
            $institutionBuffer[$normalized] = $name;
            $institutionsSeen++;
        };

        $addDiscipline = function (?string $name) use (&$disciplineBuffer, &$disciplinesSeen): void {
            $name = trim((string) ($name ?? ''));
            if ($name === '') {
                return;
            }
            $normalized = Discipline::normalizeName($name);
            $disciplineBuffer[$normalized] = $name;
            $disciplinesSeen++;
        };

        $addQualification = function (?string $name) use (&$qualificationBuffer, &$qualificationsSeen): void {
            $name = trim((string) ($name ?? ''));
            if ($name === '') {
                return;
            }
            $normalized = Qualification::normalizeName($name);
            $qualificationBuffer[$normalized] = $name;
            $qualificationsSeen++;
        };

        Officer::query()
            ->select(['id', 'discipline', 'additional_qualification'])
            ->orderBy('id')
            ->chunk($chunkSize, function ($officers) use (
                &$totalOfficers,
                &$invalidEducationJson,
                $addInstitution,
                $addDiscipline,
                $addQualification,
                $flushBuffers
            ) {
                foreach ($officers as $officer) {
                    $totalOfficers++;

                    // Legacy single field
                    $addDiscipline($officer->discipline);

                    $raw = $officer->additional_qualification;
                    if (!$raw) {
                        continue;
                    }

                    $education = json_decode($raw, true);
                    if (!is_array($education)) {
                        $invalidEducationJson++;
                        Log::warning('Invalid additional_qualification JSON while backfilling', [
                            'officer_id' => $officer->id,
                        ]);
                        continue;
                    }

                    foreach ($education as $entry) {
                        if (!is_array($entry)) {
                            continue;
                        }

                        // Support both keys seen in views
                        $institution = $entry['university'] ?? $entry['institution'] ?? null;
                        $discipline = $entry['discipline'] ?? null;
                        $qualification = $entry['qualification'] ?? null;

                        $addInstitution(is_string($institution) ? $institution : null);
                        $addDiscipline(is_string($discipline) ? $discipline : null);
                        $addQualification(is_string($qualification) ? $qualification : null);
                    }
                }

                // Flush periodically to keep memory low
                $flushBuffers();
            });

        // Final flush (in case chunk callback never ran)
        $flushBuffers();

        $this->info('Done.');
        $this->line("Officers scanned: {$totalOfficers}");
        $this->line("Invalid education JSON: {$invalidEducationJson}");
        $this->line("Institutions seen: {$institutionsSeen} | Upserted: {$institutionsUpserted}");
        $this->line("Disciplines seen: {$disciplinesSeen} | Upserted: {$disciplinesUpserted}");
        $this->line("Qualifications seen: {$qualificationsSeen} | Upserted: {$qualificationsUpserted}");

        return 0;
    }
}

