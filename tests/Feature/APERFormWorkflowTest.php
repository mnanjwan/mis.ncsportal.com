<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Officer;
use App\Models\Command;
use App\Models\DutyRoster;
use App\Models\APERForm;
use App\Models\APERTimeline;
use App\Models\Role;
use App\Services\DutyRosterService;
use App\Services\RankComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class APERFormWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $command;
    protected $oicOfficer;
    protected $secondInCommandOfficer;
    protected $assessedOfficer;
    protected $countersigningOfficer;
    protected $staffOfficer;
    protected $hrdUser;
    protected $activeTimeline;
    protected $approvedRoster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Queue::fake();
        
        $this->setupTestData();
    }

    protected function setupTestData()
    {
        // Create command
        $this->command = Command::create([
            'name' => 'Test Command',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        // Create HRD user
        $hrdRole = Role::where('name', 'HRD')->first();
        $this->hrdUser = User::create([
            'email' => 'hrd@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $this->hrdUser->roles()->attach($hrdRole->id, ['is_active' => true]);

        // Create OIC Officer (higher rank - SC)
        $oicUser = User::create([
            'email' => 'oic@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        // Assign Staff Officer role to OIC so they can access APER forms routes
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        $oicUser->roles()->attach($staffOfficerRole->id, [
            'command_id' => $this->command->id,
            'is_active' => true
        ]);
        $this->oicOfficer = Officer::create([
            'user_id' => $oicUser->id,
            'service_number' => 'NCS00001',
            'email' => 'oic@ncs.gov.ng',
            'initials' => 'OIC',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(35),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(10),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(6),
            'substantive_rank' => 'SC', // Superintendent of Customs
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '123 Test Street',
            'phone_number' => '08012345678',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        // Create 2IC Officer (higher rank - DSC)
        $secondInCommandUser = User::create([
            'email' => 'secondic@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $this->secondInCommandOfficer = Officer::create([
            'user_id' => $secondInCommandUser->id,
            'service_number' => 'NCS00002',
            'email' => 'secondic@ncs.gov.ng',
            'initials' => '2IC',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(33),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(8),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(4),
            'substantive_rank' => 'DSC', // Deputy Superintendent of Customs
            'salary_grade_level' => 'GL10',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '456 Test Street',
            'phone_number' => '08012345679',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        // Create Assessed Officer (lower rank - IC)
        $assessedUser = User::create([
            'email' => 'assessed@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $this->assessedOfficer = Officer::create([
            'user_id' => $assessedUser->id,
            'service_number' => 'NCS00003',
            'email' => 'assessed@ncs.gov.ng',
            'initials' => 'ASS',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(30),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(5),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(2),
            'substantive_rank' => 'IC', // Inspector of Customs
            'salary_grade_level' => 'GL07',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '789 Test Street',
            'phone_number' => '08012345680',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        // Create Counter Signing Officer (higher rank than Reporting Officer - CSC)
        $countersigningUser = User::create([
            'email' => 'countersigning@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $this->countersigningOfficer = Officer::create([
            'user_id' => $countersigningUser->id,
            'service_number' => 'NCS00004',
            'email' => 'countersigning@ncs.gov.ng',
            'initials' => 'CS',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(40),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(15),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subYears(1),
            'substantive_rank' => 'CSC', // Chief Superintendent of Customs
            'salary_grade_level' => 'GL12',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '321 Test Street',
            'phone_number' => '08012345681',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        // Create Staff Officer
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        $staffOfficerUser = User::create([
            'email' => 'staffofficer@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        // Store reference to user for email assertions
        $this->staffOfficerUser = $staffOfficerUser;
        $staffOfficerUser->roles()->attach($staffOfficerRole->id, [
            'command_id' => $this->command->id,
            'is_active' => true
        ]);
        $this->staffOfficer = Officer::create([
            'user_id' => $staffOfficerUser->id,
            'service_number' => 'NCS00005',
            'email' => 'staffofficer@ncs.gov.ng',
            'initials' => 'SO',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(38),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(12),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(8),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '654 Test Street',
            'phone_number' => '08012345682',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);
        // Store reference to user for email assertions
        $this->staffOfficerUser = $staffOfficerUser;

        // Create active APER timeline
        $this->activeTimeline = APERTimeline::create([
            'year' => date('Y'),
            'start_date' => \Carbon\Carbon::now()->subDays(30),
            'end_date' => \Carbon\Carbon::now()->addDays(30),
            'is_active' => true,
            'is_extended' => false,
            'description' => 'Test APER Timeline',
            'created_by' => $this->hrdUser->id,
        ]);

        // Create approved duty roster with OIC and 2IC
        $this->approvedRoster = DutyRoster::create([
            'command_id' => $this->command->id,
            'roster_period_start' => \Carbon\Carbon::now()->startOfYear(),
            'roster_period_end' => \Carbon\Carbon::now()->endOfYear(),
            'status' => 'APPROVED',
            'oic_officer_id' => $this->oicOfficer->id,
            'second_in_command_officer_id' => $this->secondInCommandOfficer->id,
            'prepared_by' => $this->staffOfficer->user_id,
        ]);
    }

    /**
     * Test 1: HRD can create APER timeline
     */
    public function test_hrd_can_create_aper_timeline(): void
    {
        $this->actingAs($this->hrdUser);

        $response = $this->post(route('hrd.aper-timeline.store'), [
            'year' => date('Y') + 1,
            'start_date' => \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d'),
            'end_date' => \Carbon\Carbon::now()->addMonths(9)->format('Y-m-d'),
            'description' => 'Next Year APER Timeline',
        ]);

        $response->assertRedirect();
        // Check that timeline was created (may not be active if dates are in future)
        $this->assertDatabaseHas('aper_timelines', [
            'year' => date('Y') + 1,
        ]);
    }

    /**
     * Test 2: Reporting Officer can search for officers in their command
     */
    public function test_reporting_officer_can_search_officers(): void
    {
        $this->actingAs($this->oicOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.reporting-officer.search'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.reporting-officer.aper-search');
    }

    /**
     * Test 3: Reporting Officer must be OIC or 2IC to create form
     */
    public function test_reporting_officer_must_be_oic_or_2ic(): void
    {
        // Create a non-OIC/2IC officer
        $nonOicUser = User::create([
            'email' => 'nonoic@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $nonOicOfficer = Officer::create([
            'user_id' => $nonOicUser->id,
            'service_number' => 'NCS00006',
            'email' => 'nonoic@ncs.gov.ng',
            'initials' => 'NO',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(28),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(3),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(1),
            'substantive_rank' => 'IC',
            'salary_grade_level' => 'GL07',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '999 Test Street',
            'phone_number' => '08012345683',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        $this->actingAs($nonOicUser);

        $response = $this->get(route('staff-officer.aper-forms.access', $this->assessedOfficer->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test 4: Reporting Officer must be same or higher rank than assessed officer
     */
    public function test_reporting_officer_rank_validation(): void
    {
        // Create lower rank officer as OIC (should fail)
        $lowerRankUser = User::create([
            'email' => 'lowerrank@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $lowerRankOfficer = Officer::create([
            'user_id' => $lowerRankUser->id,
            'service_number' => 'NCS00007',
            'email' => 'lowerrank@ncs.gov.ng',
            'initials' => 'LR',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(25),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(2),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(1),
            'substantive_rank' => 'AIC', // Lower than IC
            'salary_grade_level' => 'GL06',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '888 Test Street',
            'phone_number' => '08012345684',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        // Create roster with lower rank as OIC
        $lowerRankRoster = DutyRoster::create([
            'command_id' => $this->command->id,
            'roster_period_start' => \Carbon\Carbon::now()->startOfYear(),
            'roster_period_end' => \Carbon\Carbon::now()->endOfYear(),
            'status' => 'APPROVED',
            'oic_officer_id' => $lowerRankOfficer->id,
            'prepared_by' => $this->staffOfficer->user_id,
        ]);

        $this->actingAs($lowerRankUser);

        $response = $this->get(route('staff-officer.aper-forms.access', $this->assessedOfficer->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test 5: Reporting Officer can create APER form for officer
     */
    public function test_reporting_officer_can_create_aper_form(): void
    {
        $this->actingAs($this->oicOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.access', $this->assessedOfficer->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('aper_forms', [
            'officer_id' => $this->assessedOfficer->id,
            'reporting_officer_id' => $this->oicOfficer->user_id,
            'status' => 'REPORTING_OFFICER',
            'year' => date('Y'),
        ]);

        // Assert email job was dispatched to Reporting Officer
        Queue::assertPushed(\App\Jobs\SendAPERReportingOfficerAssignedMailJob::class, function ($job) {
            return $job->form->officer_id === $this->assessedOfficer->id
                && $job->reportingOfficer->id === $this->oicOfficer->user_id;
        });
    }

    /**
     * Test 6: Reporting Officer cannot create duplicate form for same year
     */
    public function test_cannot_create_duplicate_form_same_year(): void
    {
        // Create existing form
        APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'REPORTING_OFFICER',
            'reporting_officer_id' => $this->oicOfficer->user_id,
        ]);

        $this->actingAs($this->oicOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.access', $this->assessedOfficer->id));

        // Should redirect to existing form
        $response->assertRedirect();
    }

    /**
     * Test 7: Reporting Officer can complete form and forward to Counter Signing Officer
     */
    public function test_reporting_officer_can_complete_and_forward_form(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'REPORTING_OFFICER',
            'reporting_officer_id' => $this->oicOfficer->user_id,
        ]);

        $this->actingAs($this->oicOfficer->user);

        $response = $this->post(route('staff-officer.aper-forms.complete-reporting-officer', $form->id), [
            'service_number' => $this->assessedOfficer->service_number,
            'surname' => $this->assessedOfficer->surname,
            'final_evaluation' => 'Good performance',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'status' => 'COUNTERSIGNING_OFFICER',
        ]);
    }

    /**
     * Test 8: Counter Signing Officer must be same or higher rank than Reporting Officer
     */
    public function test_countersigning_officer_rank_validation(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'COUNTERSIGNING_OFFICER',
            'reporting_officer_id' => $this->oicOfficer->user_id,
        ]);

        // Create lower rank counter signing officer
        $lowerRankUser = User::create([
            'email' => 'lowercs@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);
        $lowerRankOfficer = Officer::create([
            'user_id' => $lowerRankUser->id,
            'service_number' => 'NCS00008',
            'email' => 'lowercs@ncs.gov.ng',
            'initials' => 'LC',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(25),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(2),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(1),
            'substantive_rank' => 'IC', // Lower than SC (Reporting Officer)
            'salary_grade_level' => 'GL07',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '777 Test Street',
            'phone_number' => '08012345685',
            'present_station' => $this->command->id,
            'is_active' => true,
        ]);

        $this->actingAs($lowerRankUser);

        $response = $this->get(route('staff-officer.aper-forms.countersigning', $form->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test 9: Counter Signing Officer can countersign and forward to Officer
     */
    public function test_countersigning_officer_can_countersign(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'COUNTERSIGNING_OFFICER',
            'reporting_officer_id' => $this->oicOfficer->user_id,
        ]);

        $this->actingAs($this->countersigningOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.countersigning', $form->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'countersigning_officer_id' => $this->countersigningOfficer->user_id,
        ]);

        // Complete countersigning
        $response = $this->post(route('staff-officer.aper-forms.complete-countersigning-officer', $form->id), [
            'countersigning_declaration' => 'I agree with the assessment',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'status' => 'OFFICER_REVIEW',
        ]);
    }

    /**
     * Test 10: Officer can accept APER form
     */
    public function test_officer_can_accept_aper_form(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'OFFICER_REVIEW',
            'reporting_officer_id' => $this->oicOfficer->user_id,
            'countersigning_officer_id' => $this->countersigningOfficer->user_id,
        ]);

        $this->actingAs($this->assessedOfficer->user);

        $response = $this->post(route('officer.aper-forms.accept', $form->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'status' => 'ACCEPTED',
            'is_rejected' => false,
        ]);

        // Assert acceptance email job was dispatched to officer
        Queue::assertPushed(\App\Jobs\SendAPERFormAcceptedMailJob::class, function ($job) use ($form) {
            return $job->form->id === $form->id;
        });
    }

    /**
     * Test 11: Officer can reject APER form with reason
     */
    public function test_officer_can_reject_aper_form(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'OFFICER_REVIEW',
            'reporting_officer_id' => $this->oicOfficer->user_id,
            'countersigning_officer_id' => $this->countersigningOfficer->user_id,
        ]);

        $this->actingAs($this->assessedOfficer->user);

        $response = $this->post(route('officer.aper-forms.reject', $form->id), [
            'rejection_reason' => 'Inaccurate assessment of my performance',
        ]);

        $response->assertRedirect();
        // Refresh form to get updated data
        $form->refresh();
        
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'status' => 'STAFF_OFFICER_REVIEW',
            'is_rejected' => true,
            'rejection_reason' => 'Inaccurate assessment of my performance',
        ]);
        
        // Verify staff officer was assigned (may be different user ID depending on query)
        $this->assertNotNull($form->staff_officer_id);

        // Assert rejection email jobs were dispatched
        Queue::assertPushed(\App\Jobs\SendAPERFormRejectedMailJob::class, function ($job) use ($form) {
            return $job->form->id === $form->id;
        });

        // Refresh form to get the actual staff officer ID that was assigned
        $form->refresh();
        
        // Assert Staff Officer email job was dispatched (check by form ID since staff officer ID may vary)
        Queue::assertPushed(\App\Jobs\SendAPERFormRejectedToStaffOfficerMailJob::class, function ($job) use ($form) {
            return $job->form->id === $form->id;
        });
    }

    /**
     * Test 12: Staff Officer can view rejected forms
     */
    public function test_staff_officer_can_view_rejected_forms(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'STAFF_OFFICER_REVIEW',
            'reporting_officer_id' => $this->oicOfficer->user_id,
            'countersigning_officer_id' => $this->countersigningOfficer->user_id,
            'staff_officer_id' => $this->staffOfficer->user_id,
            'is_rejected' => true,
            'rejection_reason' => 'Test rejection reason',
        ]);

        $this->actingAs($this->staffOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.review'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.staff-officer.aper-review');
    }

    /**
     * Test 13: Staff Officer can reassign form to different Reporting Officer
     */
    public function test_staff_officer_can_reassign_form(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'STAFF_OFFICER_REVIEW',
            'reporting_officer_id' => $this->oicOfficer->user_id,
            'countersigning_officer_id' => $this->countersigningOfficer->user_id,
            'staff_officer_id' => $this->staffOfficer->user_id,
            'is_rejected' => true,
            'rejection_reason' => 'Test rejection reason',
        ]);

        $this->actingAs($this->staffOfficer->user);

        $response = $this->post(route('staff-officer.aper-forms.reassign-reporting-officer', $form->id), [
            'reporting_officer_id' => $this->secondInCommandOfficer->user_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'reporting_officer_id' => $this->secondInCommandOfficer->user_id,
            'status' => 'REPORTING_OFFICER',
            'is_rejected' => false,
            'rejection_reason' => null,
        ]);

        // Assert reassignment email job was dispatched to new Reporting Officer
        Queue::assertPushed(\App\Jobs\SendAPERReportingOfficerAssignedMailJob::class, function ($job) use ($form) {
            return $job->form->id === $form->id
                && $job->reportingOfficer->id === $this->secondInCommandOfficer->user_id;
        });
    }

    /**
     * Test 14: Staff Officer can finalize rejected form
     */
    public function test_staff_officer_can_finalize_rejected_form(): void
    {
        $form = APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'STAFF_OFFICER_REVIEW',
            'reporting_officer_id' => $this->oicOfficer->user_id,
            'countersigning_officer_id' => $this->countersigningOfficer->user_id,
            'staff_officer_id' => $this->staffOfficer->user_id,
            'is_rejected' => true,
            'rejection_reason' => 'Test rejection reason',
        ]);

        $this->actingAs($this->staffOfficer->user);

        $response = $this->post(route('staff-officer.aper-forms.staff-officer-reject', $form->id), [
            'staff_officer_rejection_reason' => 'Form is accurate, finalizing rejection',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aper_forms', [
            'id' => $form->id,
            'status' => 'FINALIZED',
            'is_rejected' => true,
            'staff_officer_rejection_reason' => 'Form is accurate, finalizing rejection',
        ]);
        $this->assertNotNull(APERForm::find($form->id)->finalized_at);

        // Assert finalization email job was dispatched to officer
        Queue::assertPushed(\App\Jobs\SendAPERFormFinalizedMailJob::class, function ($job) use ($form) {
            return $job->form->id === $form->id;
        });
    }

    /**
     * Test 15: Officer cannot create multiple forms for same year
     */
    public function test_officer_cannot_have_multiple_forms_same_year(): void
    {
        // Create accepted form
        APERForm::create([
            'officer_id' => $this->assessedOfficer->id,
            'timeline_id' => $this->activeTimeline->id,
            'year' => date('Y'),
            'status' => 'ACCEPTED',
            'reporting_officer_id' => $this->oicOfficer->user_id,
        ]);

        $this->actingAs($this->oicOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.access', $this->assessedOfficer->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test 16: Form must be in same command
     */
    public function test_form_must_be_same_command(): void
    {
        // Create officer in different command
        $otherCommand = Command::create([
            'name' => 'Other Command',
            'code' => 'OTHER',
            'is_active' => true,
        ]);

        $otherOfficer = Officer::create([
            'service_number' => 'NCS00009',
            'email' => 'other@ncs.gov.ng',
            'initials' => 'OT',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(30),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(5),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(2),
            'substantive_rank' => 'IC',
            'salary_grade_level' => 'GL07',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '111 Test Street',
            'phone_number' => '08012345686',
            'present_station' => $otherCommand->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->oicOfficer->user);

        $response = $this->get(route('staff-officer.aper-forms.reporting-officer.access-form', $otherOfficer->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
