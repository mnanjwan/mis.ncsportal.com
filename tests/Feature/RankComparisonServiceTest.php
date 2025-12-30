<?php

namespace Tests\Feature;

use App\Models\Officer;
use App\Models\User;
use App\Services\RankComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankComparisonServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RankComparisonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RankComparisonService();
    }

    /**
     * Test rank hierarchy - CGC should be higher than DCG
     */
    public function test_cgc_is_higher_than_dcg(): void
    {
        $cgcOfficer = $this->createOfficerWithRank('CGC');
        $dcgOfficer = $this->createOfficerWithRank('DCG');

        $this->assertEquals(1, $this->service->compareRanks($cgcOfficer->id, $dcgOfficer->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($cgcOfficer->id, $dcgOfficer->id));
        $this->assertTrue($this->service->isRankHigher($cgcOfficer->id, $dcgOfficer->id));
    }

    /**
     * Test rank hierarchy - IC should be higher than AIC
     */
    public function test_ic_is_higher_than_aic(): void
    {
        $icOfficer = $this->createOfficerWithRank('IC');
        $aicOfficer = $this->createOfficerWithRank('AIC');

        $this->assertEquals(1, $this->service->compareRanks($icOfficer->id, $aicOfficer->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($icOfficer->id, $aicOfficer->id));
        $this->assertTrue($this->service->isRankHigher($icOfficer->id, $aicOfficer->id));
    }

    /**
     * Test equal ranks
     */
    public function test_equal_ranks(): void
    {
        $officer1 = $this->createOfficerWithRank('SC');
        $officer2 = $this->createOfficerWithRank('SC');

        $this->assertEquals(0, $this->service->compareRanks($officer1->id, $officer2->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($officer1->id, $officer2->id));
        $this->assertFalse($this->service->isRankHigher($officer1->id, $officer2->id));
    }

    /**
     * Test full rank name mapping - Comptroller General of Customs (CGC) GL18
     */
    public function test_full_rank_name_mapping(): void
    {
        $officer1 = $this->createOfficerWithRank('Comptroller General of Customs (CGC) GL18');
        $officer2 = $this->createOfficerWithRank('DCG');

        $this->assertEquals(1, $this->service->compareRanks($officer1->id, $officer2->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($officer1->id, $officer2->id));
    }

    /**
     * Test partial rank name mapping - Inspector of Customs (IC) GL07
     */
    public function test_partial_rank_name_mapping(): void
    {
        $officer1 = $this->createOfficerWithRank('Inspector of Customs (IC) GL07');
        $officer2 = $this->createOfficerWithRank('AIC');

        $this->assertEquals(1, $this->service->compareRanks($officer1->id, $officer2->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($officer1->id, $officer2->id));
    }

    /**
     * Test Assistant Superintendent variations
     */
    public function test_assistant_superintendent_variations(): void
    {
        $asc1Officer = $this->createOfficerWithRank('Assistant Superintendent of Customs Grade I (ASC I) GL 09');
        $asc2Officer = $this->createOfficerWithRank('ASC II');

        $this->assertEquals(1, $this->service->compareRanks($asc1Officer->id, $asc2Officer->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($asc1Officer->id, $asc2Officer->id));
    }

    /**
     * Test complete rank hierarchy from highest to lowest
     */
    public function test_complete_rank_hierarchy(): void
    {
        $ranks = [
            'CGC' => 18,
            'DCG' => 17,
            'ACG' => 16,
            'CC' => 15,
            'DC' => 14,
            'AC' => 13,
            'CSC' => 12,
            'SC' => 11,
            'DSC' => 10,
            'ASC I' => 9,
            'ASC II' => 8,
            'IC' => 7,
            'AIC' => 6,
            'CA I' => 5,
            'CA II' => 4,
            'CA III' => 3,
        ];

        $officers = [];
        foreach ($ranks as $rank => $level) {
            $officers[$rank] = $this->createOfficerWithRank($rank);
        }

        // Test each rank is higher than the next lower rank
        $rankArray = array_keys($ranks);
        for ($i = 0; $i < count($rankArray) - 1; $i++) {
            $higherRank = $rankArray[$i];
            $lowerRank = $rankArray[$i + 1];
            
            $this->assertEquals(
                1,
                $this->service->compareRanks($officers[$higherRank]->id, $officers[$lowerRank]->id),
                "{$higherRank} should be higher than {$lowerRank}"
            );
            
            $this->assertTrue(
                $this->service->isRankHigherOrEqual($officers[$higherRank]->id, $officers[$lowerRank]->id),
                "{$higherRank} should be higher or equal to {$lowerRank}"
            );
        }
    }

    /**
     * Test case-insensitive rank matching
     */
    public function test_case_insensitive_matching(): void
    {
        $officer1 = $this->createOfficerWithRank('cgc'); // lowercase
        $officer2 = $this->createOfficerWithRank('DCG');

        $this->assertEquals(1, $this->service->compareRanks($officer1->id, $officer2->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($officer1->id, $officer2->id));
    }

    /**
     * Test empty rank handling (treated as lowest rank)
     */
    public function test_empty_rank_handling(): void
    {
        $officer1 = $this->createOfficerWithRank('SC');
        $officer2 = $this->createOfficerWithRank('');

        // Empty rank should be treated as lowest (0)
        $this->assertEquals(1, $this->service->compareRanks($officer1->id, $officer2->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($officer1->id, $officer2->id));
    }

    /**
     * Test unknown rank handling
     */
    public function test_unknown_rank_handling(): void
    {
        $officer1 = $this->createOfficerWithRank('SC');
        $officer2 = $this->createOfficerWithRank('Unknown Rank XYZ');

        // Unknown rank should be treated as lowest (0)
        $this->assertEquals(1, $this->service->compareRanks($officer1->id, $officer2->id));
        $this->assertTrue($this->service->isRankHigherOrEqual($officer1->id, $officer2->id));
    }

    /**
     * Test APER form scenario - Reporting Officer must be same or higher rank
     */
    public function test_aper_reporting_officer_rank_validation(): void
    {
        // Reporting Officer (SC) assessing Officer (IC)
        $reportingOfficer = $this->createOfficerWithRank('SC');
        $assessedOfficer = $this->createOfficerWithRank('IC');

        // SC is higher than IC, so this should pass
        $this->assertTrue($this->service->isRankHigherOrEqual($reportingOfficer->id, $assessedOfficer->id));

        // Reverse - IC cannot assess SC
        $this->assertFalse($this->service->isRankHigherOrEqual($assessedOfficer->id, $reportingOfficer->id));
    }

    /**
     * Test APER form scenario - Counter Signing Officer must be same or higher than Reporting Officer
     */
    public function test_aper_countersigning_officer_rank_validation(): void
    {
        $reportingOfficer = $this->createOfficerWithRank('SC');
        $countersigningOfficer = $this->createOfficerWithRank('CSC');

        // CSC is higher than SC, so this should pass
        $this->assertTrue($this->service->isRankHigherOrEqual($countersigningOfficer->id, $reportingOfficer->id));

        // Same rank should also pass
        $sameRankOfficer = $this->createOfficerWithRank('SC');
        $this->assertTrue($this->service->isRankHigherOrEqual($sameRankOfficer->id, $reportingOfficer->id));
    }

    /**
     * Test error handling when officers don't exist
     */
    public function test_error_handling_nonexistent_officers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('One or both officers not found');

        $this->service->compareRanks(99999, 99998);
    }

    /**
     * Helper method to create an officer with a specific rank
     */
    private function createOfficerWithRank(?string $rank): Officer
    {
        $user = User::factory()->create();
        
        return Officer::create([
            'user_id' => $user->id,
            'service_number' => 'SN' . rand(1000, 9999),
            'email' => 'test' . uniqid() . '@ncs.gov.ng',
            'initials' => 'T',
            'surname' => 'Test',
            'sex' => 'M',
            'date_of_birth' => '1990-01-01',
            'date_of_first_appointment' => '2010-01-01',
            'date_of_present_appointment' => '2020-01-01',
            'substantive_rank' => $rank ?? '', // Use empty string instead of null for database
            'salary_grade_level' => 'GL07',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'marital_status' => 'Single',
            'entry_qualification' => 'B.Sc',
            'discipline' => 'Computer Science',
        ]);
    }
}
