<?php

namespace App\Services;

use App\Models\OnboardingDocumentCategory;
use Illuminate\Support\Str;

class OnboardingDocumentCategoryService
{
    public function getCategoryMap(bool $activeOnly = true, ?string $audience = null): array
    {
        try {
            $query = OnboardingDocumentCategory::query()
                ->orderBy('sort_order')
                ->orderBy('name');

            if ($activeOnly) {
                $query->active();
            }

            if (in_array($audience, [
                OnboardingDocumentCategory::APPLIES_TO_RECRUIT,
                OnboardingDocumentCategory::APPLIES_TO_OFFICER,
            ], true)) {
                $query->whereIn('applies_to', [
                    OnboardingDocumentCategory::APPLIES_TO_BOTH,
                    $audience,
                ]);
            }

            $categories = $query->get(['key', 'name']);
            if ($categories->isNotEmpty()) {
                return $categories->pluck('name', 'key')->toArray();
            }
        } catch (\Throwable $e) {
            // Fallback to config when table is not migrated yet.
        }

        return config('document_categories', []);
    }

    public function getAllowedKeys(bool $activeOnly = true, ?string $audience = null): array
    {
        return array_keys($this->getCategoryMap($activeOnly, $audience));
    }

    public function generateUniqueKey(string $name): string
    {
        $baseKey = trim((string) Str::of($name)->lower()->replaceMatches('/[^a-z0-9]+/', '_'), '_');
        if ($baseKey === '') {
            $baseKey = 'category';
        }

        $candidate = $baseKey;
        $counter = 2;
        while (OnboardingDocumentCategory::where('key', $candidate)->exists()) {
            $candidate = $baseKey . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
