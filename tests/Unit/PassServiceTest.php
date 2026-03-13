<?php

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Services\PassService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PassService $passService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passService = new PassService();
    }

    /** @test */
    public function working_days_between_counts_weekdays_only(): void
    {
        // Mon 10 Mar 2025 to Fri 14 Mar 2025 = 5 working days
        $this->assertEquals(5, $this->passService->workingDaysBetween('2025-03-10', '2025-03-14'));

        // Mon to Wed = 3 working days
        $this->assertEquals(3, $this->passService->workingDaysBetween('2025-03-10', '2025-03-12'));

        // Saturday to Sunday = 0 working days
        $this->assertEquals(0, $this->passService->workingDaysBetween('2025-03-15', '2025-03-16'));

        // Thu 13 Mar to Mon 17 Mar (Thu, Fri, Mon) = 3 working days
        $this->assertEquals(3, $this->passService->workingDaysBetween('2025-03-13', '2025-03-17'));

        // Single weekday
        $this->assertEquals(1, $this->passService->workingDaysBetween('2025-03-10', '2025-03-10'));
    }

    /** @test */
    public function working_days_between_excludes_weekends(): void
    {
        // 7 calendar days Mon–Sun: 5 working days
        $this->assertEquals(5, $this->passService->workingDaysBetween('2025-03-10', '2025-03-16'));
    }

    /** @test */
    public function pass_max_working_days_gl07_and_above_returns_30(): void
    {
        $this->assertEquals(30, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 07'));
        $this->assertEquals(30, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL07'));
        $this->assertEquals(30, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 08'));
        $this->assertEquals(30, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 12'));
    }

    /** @test */
    public function pass_max_working_days_gl04_to_gl06_returns_21(): void
    {
        $this->assertEquals(21, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 04'));
        $this->assertEquals(21, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 05'));
        $this->assertEquals(21, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 06'));
        $this->assertEquals(21, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL06'));
    }

    /** @test */
    public function pass_max_working_days_gl03_and_below_returns_14(): void
    {
        $this->assertEquals(14, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 01'));
        $this->assertEquals(14, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 02'));
        $this->assertEquals(14, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 03'));
        $this->assertEquals(14, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL03'));
    }

    /** @test */
    public function pass_max_working_days_null_or_empty_returns_14(): void
    {
        $this->assertEquals(14, $this->passService->getPassMaxWorkingDaysForGradeLevel(null));
        $this->assertEquals(14, $this->passService->getPassMaxWorkingDaysForGradeLevel(''));
    }

    /** @test */
    public function pass_max_working_days_respects_system_settings(): void
    {
        SystemSetting::create([
            'setting_key' => 'pass_max_days_gl07_above',
            'setting_value' => '45',
            'description' => 'Test',
        ]);
        SystemSetting::create([
            'setting_key' => 'pass_max_days_gl04_06',
            'setting_value' => '25',
            'description' => 'Test',
        ]);
        SystemSetting::create([
            'setting_key' => 'pass_max_days_gl03_below',
            'setting_value' => '10',
            'description' => 'Test',
        ]);

        $this->assertEquals(45, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 07'));
        $this->assertEquals(25, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 05'));
        $this->assertEquals(10, $this->passService->getPassMaxWorkingDaysForGradeLevel('GL 03'));
    }
}
