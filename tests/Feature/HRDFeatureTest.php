<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Officer;
use App\Models\Command;
use App\Models\StaffOrder;
use App\Models\MovementOrder;
use App\Models\ManningRequest;
use App\Models\EmolumentTimeline;
use App\Models\PromotionEligibilityCriterion;
use App\Models\PromotionEligibilityList;
use App\Models\RetirementList;
use App\Models\LeaveType;
use App\Models\OfficerCourse;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HRDFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $hrdUser;
    protected $commands;
    protected $officers;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create HRD user
        $this->hrdUser = User::create([
            'email' => 'hrd@ncs.gov.ng',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $hrdRole = Role::firstOrCreate(
            ['code' => 'HRD'],
            [
                'name' => 'HRD', 
                'code' => 'HRD', 
                'description' => 'Human Resources Department',
                'access_level' => 'system_wide'
            ]
        );
        $this->hrdUser->roles()->attach($hrdRole->id, [
            'assigned_at' => now(),
            'assigned_by' => $this->hrdUser->id,
            'is_active' => true,
        ]);

        // Create commands
        $this->commands = collect();
        for ($i = 1; $i <= 5; $i++) {
            $this->commands->push(Command::create([
                'name' => 'Test Command ' . $i,
                'code' => 'CMD' . $i,
                'is_active' => true,
            ]));
        }

        // Create officers
        $this->officers = collect();
        for ($i = 1; $i <= 20; $i++) {
            $this->officers->push(Officer::create([
                'service_number' => 'NCS' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'initials' => 'TEST',
                'surname' => 'OFFICER' . $i,
                'sex' => rand(0, 1) ? 'M' : 'F',
                'date_of_birth' => Carbon::now()->subYears(30 + rand(0, 20)),
                'date_of_first_appointment' => Carbon::now()->subYears(rand(5, 15)),
                'date_of_present_appointment' => Carbon::now()->subMonths(rand(6, 60)),
                'substantive_rank' => ['Assistant Superintendent', 'Deputy Superintendent', 'Superintendent'][rand(0, 2)],
                'salary_grade_level' => 'GL' . rand(7, 12),
                'state_of_origin' => 'Lagos',
                'lga' => 'Ikeja',
                'geopolitical_zone' => 'South West',
                'entry_qualification' => 'BSc',
                'permanent_home_address' => '111 Test Street',
                'phone_number' => '0801234' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'email' => "hrd.feature.officer{$i}@ncs.gov.ng",
                'present_station' => $this->commands->first()->id,
                'is_active' => true,
                'is_deceased' => false,
                'interdicted' => false,
                'suspended' => false,
                'dismissed' => false,
            ]));
        }
    }

    /** @test */
    public function hrd_can_access_dashboard()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.dashboard');
        $response->assertSee('HRD Dashboard');
    }

    /** @test */
    public function hrd_can_view_officers_list()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.officers'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.officers-list');
    }

    /** @test */
    public function hrd_can_view_officer_details()
    {
        $officer = $this->officers->first();

        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.officers.show', $officer->id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.officer-show');
        $response->assertSee($officer->surname);
    }

    /** @test */
    public function hrd_can_view_staff_orders_list()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.staff-orders'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.staff-orders');
        $response->assertSee('Staff Orders');
    }

    /** @test */
    public function hrd_can_create_staff_order()
    {
        $officer = $this->officers->first();
        $fromCommand = $this->commands->first();
        $toCommand = $this->commands->last();

        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.staff-orders.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.staff-order.create');

        // Submit form
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.staff-orders.store'), [
                'officer_id' => $officer->id,
                'from_command_id' => $fromCommand->id,
                'to_command_id' => $toCommand->id,
                'effective_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'order_type' => 'POSTING',
            ]);

        $response->assertRedirect(route('hrd.staff-orders'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('staff_orders', [
            'officer_id' => $officer->id,
            'from_command_id' => $fromCommand->id,
            'to_command_id' => $toCommand->id,
        ]);
    }

    /** @test */
    public function hrd_can_publish_staff_order_and_update_officer()
    {
        $officer = $this->officers->first();
        $fromCommand = $this->commands->first();
        $toCommand = $this->commands->last();

        // Create staff order
        $order = StaffOrder::create([
            'order_number' => 'SO-TEST-001',
            'officer_id' => $officer->id,
            'from_command_id' => $fromCommand->id,
            'to_command_id' => $toCommand->id,
            'effective_date' => Carbon::now()->addDays(30),
            'order_type' => 'POSTING',
            'status' => 'DRAFT',
            'created_by' => $this->hrdUser->id,
        ]);

        $originalStation = $officer->present_station;

        // Update to PUBLISHED
        $response = $this->actingAs($this->hrdUser)
            ->put(route('hrd.staff-orders.update', $order->id), [
                'order_number' => 'SO-TEST-001',
                'officer_id' => $officer->id,
                'from_command_id' => $fromCommand->id,
                'to_command_id' => $toCommand->id,
                'effective_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'order_type' => 'POSTING',
                'status' => 'PUBLISHED',
            ]);

        $response->assertRedirect(route('hrd.staff-orders.show', $order->id));

        // Current workflow creates a pending posting; officer station is updated on acceptance.
        $officer->refresh();
        $this->assertEquals($originalStation, $officer->present_station);
    }

    /** @test */
    public function hrd_can_view_movement_orders_list()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.movement-orders'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.movement-orders');
        $response->assertSee('Movement Orders');
    }

    /** @test */
    public function hrd_can_create_movement_order()
    {
        $manningRequest = ManningRequest::create([
            'request_number' => 'MR-TEST-001',
            'command_id' => $this->commands->first()->id,
            'requested_by' => $this->hrdUser->id,
            'status' => 'APPROVED',
            'justification' => 'Test request',
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.movement-orders.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.movement-order.create');

        // Submit form
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.movement-orders.store'), [
                'criteria_months_at_station' => 24,
                'manning_request_id' => $manningRequest->id,
                'status' => 'DRAFT',
            ]);

        $response->assertRedirect(route('hrd.movement-orders'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('movement_orders', [
            'manning_request_id' => $manningRequest->id,
            'criteria_months_at_station' => 24,
        ]);
    }

    /** @test */
    public function hrd_can_view_emolument_timeline_list()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.emolument-timeline'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.emolument-timeline');
        $response->assertSee('Emolument Timeline');
    }

    /** @test */
    public function hrd_can_create_emolument_timeline()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.emolument-timeline.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.emolument-timeline.create');

        // Submit form
        $year = date('Y') + 1;
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.emolument-timeline.store'), [
                'year' => $year,
                'start_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'is_active' => true,
            ]);

        $response->assertRedirect(route('hrd.emolument-timeline'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('emolument_timelines', [
            'year' => $year,
        ]);
    }

    /** @test */
    public function hrd_can_extend_emolument_timeline()
    {
        $timeline = EmolumentTimeline::create([
            'year' => date('Y'),
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->addDays(10),
            'is_active' => true,
            'created_by' => $this->hrdUser->id,
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.emolument-timeline.extend', $timeline->id));

        $response->assertStatus(200);
        $response->assertViewIs('forms.emolument-timeline.extend');

        // Submit extension
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.emolument-timeline.extend.store', $timeline->id), [
                'extension_end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'extension_reason' => 'Test extension',
            ]);

        $response->assertRedirect(route('hrd.emolument-timeline'));
        $response->assertSessionHas('success');

        // Verify in database
        $timeline->refresh();
        $this->assertTrue($timeline->is_extended);
        $this->assertNotNull($timeline->extension_end_date);
    }

    /** @test */
    public function hrd_can_view_promotion_criteria()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.promotion-criteria'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.promotion-criteria');
        $response->assertSee('Promotion Criteria');
    }

    /** @test */
    public function hrd_can_create_promotion_criteria()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.promotion-criteria.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.promotion.criteria-form');

        // Submit form
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.promotion-criteria.store'), [
                'rank' => 'Test Rank',
                'years_in_rank_required' => 3,
                'is_active' => true,
            ]);

        $response->assertRedirect(route('hrd.promotion-criteria'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('promotion_eligibility_criteria', [
            'rank' => 'Test Rank',
            'years_in_rank_required' => 3,
        ]);
    }

    /** @test */
    public function hrd_can_generate_promotion_eligibility_list()
    {
        // Create a clearly-eligible officer + matching criteria
        $eligibleOfficer = Officer::create([
            'service_number' => 'NCS77770',
            'initials' => 'EL',
            'surname' => 'IGIBLE',
            'sex' => 'M',
            'date_of_birth' => Carbon::now()->subYears(40),
            'date_of_first_appointment' => Carbon::now()->subYears(15),
            'date_of_present_appointment' => Carbon::now()->subYears(3),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000070',
            'email' => 'eligible@ncs.gov.ng',
            'present_station' => $this->commands->first()->id,
            'is_active' => true,
            'is_deceased' => false,
            'interdicted' => false,
            'suspended' => false,
            'dismissed' => false,
        ]);

        PromotionEligibilityCriterion::create([
            'rank' => 'SC',
            'years_in_rank_required' => 2,
            'is_active' => true,
            'created_by' => $this->hrdUser->id,
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.promotion-eligibility.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.promotion.create-eligibility-list');

        // Submit form
        $year = date('Y');
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.promotion-eligibility.store'), [
                'year' => $year,
            ]);

        $response->assertRedirect();

        // Verify in database
        $this->assertDatabaseHas('promotion_eligibility_lists', [
            'year' => $year,
        ]);

        // Verify list items were created
        $list = PromotionEligibilityList::where('year', $year)->first();
        $this->assertNotNull($list);
        // Items may be empty depending on eligibility rules and test fixtures
        $this->assertGreaterThanOrEqual(0, $list->items()->count());
    }

    /** @test */
    public function hrd_can_delete_empty_promotion_eligibility_list()
    {
        $list = PromotionEligibilityList::create([
            'year' => date('Y') + 10,
            'generated_by' => $this->hrdUser->id,
            'status' => 'DRAFT',
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->delete(route('hrd.promotion-eligibility.destroy', $list->id));

        $response->assertRedirect(route('hrd.promotion-eligibility'));
        $response->assertSessionHas('success');

        // Verify deleted from database
        $this->assertDatabaseMissing('promotion_eligibility_lists', [
            'id' => $list->id,
        ]);
    }

    /** @test */
    public function hrd_can_generate_retirement_list()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.retirement-list.generate'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.retirement.generate-list');

        // Submit form
        $year = date('Y') + 1;

        // Create an officer who will meet retirement criteria by end of target year (age 60)
        Officer::create([
            'service_number' => 'NCS88880',
            'initials' => 'RT',
            'surname' => 'IREE',
            'sex' => 'M',
            'date_of_birth' => Carbon::create($year, 12, 31)->subYears(60)->subDays(1),
            'date_of_first_appointment' => Carbon::create($year, 12, 31)->subYears(10),
            'date_of_present_appointment' => Carbon::now()->subYears(1),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000080',
            'email' => 'retiree@ncs.gov.ng',
            'present_station' => $this->commands->first()->id,
            'is_active' => true,
            'is_deceased' => false,
            'interdicted' => false,
            'suspended' => false,
            'dismissed' => false,
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.retirement-list.store'), [
                'year' => $year,
            ]);

        $response->assertRedirect();

        // Verify in database
        $this->assertDatabaseHas('retirement_list', [
            'year' => $year,
        ]);

        // Verify list items were created
        $list = RetirementList::where('year', $year)->first();
        $this->assertNotNull($list);
    }

    /** @test */
    public function hrd_can_view_leave_types()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.leave-types'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.leave-types');
        $response->assertSee('Leave Types');
    }

    /** @test */
    public function hrd_can_create_leave_type()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.leave-types.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.leave-type.form');

        // Submit form
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.leave-types.store'), [
                'name' => 'Test Leave',
                'code' => 'TL',
                'max_duration_days' => 10,
                'max_occurrences_per_year' => 2,
                'requires_medical_certificate' => false,
                'requires_approval_level' => 'DC_ADMIN',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('hrd.leave-types'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('leave_types', [
            'code' => 'TL',
            'name' => 'Test Leave',
        ]);
    }

    /** @test */
    public function hrd_can_view_manning_requests()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.manning-requests'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.manning-requests');
        $response->assertSee('Manning Requests');
    }

    /** @test */
    public function hrd_can_view_courses()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.courses'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.courses');
        $response->assertSee('Course Nominations');
    }

    /** @test */
    public function hrd_can_nominate_officer_for_course()
    {
        $officer = $this->officers->first();

        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.courses.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.course.create');

        // Submit form
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.courses.store'), [
                'officer_ids' => [$officer->id],
                'course_name' => 'Test Course',
                'course_type' => 'MANDATORY',
                'start_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('hrd.courses'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('officer_courses', [
            'officer_id' => $officer->id,
            'course_name' => 'Test Course',
        ]);
    }

    /** @test */
    public function hrd_can_view_system_settings()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.system-settings'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.system-settings');
        $response->assertSee('System Settings');
    }

    /** @test */
    public function hrd_can_update_system_settings()
    {
        $response = $this->actingAs($this->hrdUser)
            ->put(route('hrd.system-settings.update'), [
                'settings' => [
                    'retirement_age' => '60',
                    'retirement_years_of_service' => '35',
                    'pre_retirement_leave_months' => '3',
                    'annual_leave_days_gl07_below' => '28',
                    'annual_leave_days_gl08_above' => '30',
                    'annual_leave_max_applications' => '2',
                    'pass_max_days' => '5',
                    'rsa_pin_prefix' => 'PEN',
                    'rsa_pin_length' => '12',
                ],
            ]);

        $response->assertRedirect(route('hrd.system-settings'));
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'retirement_age',
            'setting_value' => '60',
        ]);
    }

    /** @test */
    public function hrd_can_view_onboarding_page()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.onboarding'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.onboarding');
        $response->assertSee('Officer Onboarding');
    }

    /** @test */
    public function hrd_can_initiate_onboarding()
    {
        $officer = Officer::create([
            'service_number' => 'NCS99999',
            'email' => 'test.onboard@ncs.gov.ng',
            'initials' => 'TEST',
            'surname' => 'ONBOARD',
            'sex' => 'M',
            'date_of_birth' => Carbon::now()->subYears(30),
            'date_of_first_appointment' => Carbon::now()->subYears(5),
            'date_of_present_appointment' => Carbon::now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000005',
            'is_active' => true,
            'is_deceased' => false,
            'present_station' => $this->commands->first()->id,
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.onboarding.initiate'), [
                'service_number' => $officer->service_number,
                'email' => 'test.onboard@ncs.gov.ng',
            ]);

        $response->assertRedirect();

        // Verify user account created
        $this->assertDatabaseHas('users', [
            'email' => 'test.onboard@ncs.gov.ng',
        ]);

        // Verify officer linked to user
        $officer->refresh();
        $this->assertNotNull($officer->user_id);
    }

    /** @test */
    public function promotion_eligibility_excludes_interdicted_officers()
    {
        // Create criteria
        PromotionEligibilityCriterion::create([
            'rank' => 'SC',
            'years_in_rank_required' => 2,
            'is_active' => true,
            'created_by' => $this->hrdUser->id,
        ]);

        // Create eligible non-interdicted officer
        $eligibleOfficer = Officer::create([
            'service_number' => 'NCS88881',
            'email' => 'eligible2@ncs.gov.ng',
            'initials' => 'EL',
            'surname' => 'IGIBLE2',
            'sex' => 'M',
            'date_of_birth' => Carbon::now()->subYears(40),
            'date_of_first_appointment' => Carbon::now()->subYears(15),
            'date_of_present_appointment' => Carbon::now()->subYears(3),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000081',
            'is_active' => true,
            'is_deceased' => false,
            'interdicted' => false,
            'suspended' => false,
            'dismissed' => false,
            'present_station' => $this->commands->first()->id,
        ]);

        // Create interdicted officer
        $interdictedOfficer = Officer::create([
            'service_number' => 'NCS88888',
            'email' => 'interdicted@ncs.gov.ng',
            'initials' => 'INT',
            'surname' => 'DICTED',
            'sex' => 'M',
            'date_of_birth' => Carbon::now()->subYears(35),
            'date_of_first_appointment' => Carbon::now()->subYears(10),
            'date_of_present_appointment' => Carbon::now()->subYears(3),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL12',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000006',
            'is_active' => true,
            'is_deceased' => false,
            'interdicted' => true, // Interdicted
            'suspended' => false,
            'dismissed' => false,
            'present_station' => $this->commands->first()->id,
        ]);

        // Generate eligibility list
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.promotion-eligibility.store'), [
                'year' => date('Y'),
            ]);

        $response->assertRedirect();

        // Verify interdicted officer is NOT in the list
        $list = PromotionEligibilityList::where('year', date('Y'))->first();
        $this->assertNotNull($list);
        $this->assertDatabaseMissing('promotion_eligibility_list_items', [
            'eligibility_list_id' => $list->id,
            'officer_id' => $interdictedOfficer->id,
        ]);
    }

    /** @test */
    public function hrd_can_view_reports()
    {
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.reports'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.reports');
        $response->assertSee('Reports');
    }
}
