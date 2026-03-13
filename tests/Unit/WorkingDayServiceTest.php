<?php

namespace Tests\Unit;

use App\Models\Holiday;
use App\Services\WorkingDayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkingDayServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WorkingDayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkingDayService();
    }

    /** @test */
    public function it_identifies_weekends_as_non_working_days()
    {
        $saturday = Carbon::parse('2026-03-14'); // Saturday
        $sunday = Carbon::parse('2026-03-15'); // Sunday
        $monday = Carbon::parse('2026-03-16'); // Monday

        $this->assertFalse($this->service->isWorkingDay($saturday));
        $this->assertFalse($this->service->isWorkingDay($sunday));
        $this->assertTrue($this->service->isWorkingDay($monday));
    }

    /** @test */
    public function it_identifies_fixed_holidays_as_non_working_days()
    {
        $newYear = Carbon::parse('2026-01-01');
        $christmas = Carbon::parse('2026-12-25');

        $this->assertFalse($this->service->isWorkingDay($newYear));
        $this->assertFalse($this->service->isWorkingDay($christmas));
    }

    /** @test */
    public function it_identifies_floating_holidays_from_database()
    {
        $date = Carbon::parse('2026-03-20');
        $this->assertTrue($this->service->isWorkingDay($date));

        Holiday::create([
            'name' => 'Test Holiday',
            'date' => '2026-03-20',
            'is_floating' => true,
            'year' => 2026
        ]);

        $this->assertFalse($this->service->isWorkingDay($date));
    }

    /** @test */
    public function it_calculates_working_days_between_dates()
    {
        // Monday (16) to Friday (20) = 5 days
        $start = '2026-03-16';
        $end = '2026-03-20';
        $this->assertEquals(5, $this->service->workingDaysBetween($start, $end));

        // Thursday (12) to Tuesday (17) = 4 days (Thu, Fri, Mon, Tue)
        $start = '2026-03-12';
        $end = '2026-03-17';
        $this->assertEquals(4, $this->service->workingDaysBetween($start, $end));
    }

    /** @test */
    public function it_calculates_working_days_skipping_holidays()
    {
        // Monday (16) to Friday (20). Add holiday on Wednesday (18)
        Holiday::create([
            'name' => 'Mid-week Break',
            'date' => '2026-03-18',
            'is_floating' => true,
            'year' => 2026
        ]);

        $start = '2026-03-16';
        $end = '2026-03-20';
        $this->assertEquals(4, $this->service->workingDaysBetween($start, $end));
    }
}
