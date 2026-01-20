<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Models\User;
use App\Models\Officer;
use App\Models\Command;
use App\Models\Role;
use App\Models\DutyRoster;

class DutyRosterUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $staffOfficer;
    protected $command;
    protected $staffOfficerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Queue::fake();
        
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        // Create command
        $this->command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        // Create Staff Officer user
        $this->staffOfficerUser = User::create([
            'email' => 'staffofficer@ncs.gov.ng',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
        ]);

        // Assign Staff Officer role
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        $this->staffOfficerUser->roles()->attach($staffOfficerRole->id, [
            'command_id' => $this->command->id,
            'is_active' => true
        ]);

        // Create Staff Officer
        $this->staffOfficer = Officer::create([
            'user_id' => $this->staffOfficerUser->id,
            'service_number' => 'NCS00001',
            'email' => 'staffofficer@ncs.gov.ng',
            'initials' => 'SO',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => \Carbon\Carbon::now()->subYears(35),
            'date_of_first_appointment' => \Carbon\Carbon::now()->subYears(10),
            'date_of_present_appointment' => \Carbon\Carbon::now()->subMonths(6),
            'substantive_rank' => 'SC',
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
    }

    /**
     * Test 1: Staff Officer can create roster with predefined unit
     */
    public function test_staff_officer_can_create_roster_with_predefined_unit(): void
    {
        $this->actingAs($this->staffOfficerUser);

        $response = $this->post(route('staff-officer.roster.store'), [
            'unit' => 'Revenue',
            'command_id' => $this->command->id,
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
        ]);

        $roster = DutyRoster::latest('id')->first();
        $response->assertRedirect(route('staff-officer.roster.show', $roster->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('duty_rosters', [
            'command_id' => $this->command->id,
            'unit' => 'Revenue',
            'status' => 'DRAFT',
        ]);
    }

    /**
     * Test 2: Staff Officer can create roster with custom unit
     */
    public function test_staff_officer_can_create_roster_with_custom_unit(): void
    {
        $this->actingAs($this->staffOfficerUser);

        $customUnit = 'Custom Unit Test';

        $response = $this->post(route('staff-officer.roster.store'), [
            'unit' => '__NEW__',
            'unit_custom' => $customUnit,
            'command_id' => $this->command->id,
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
        ]);

        $roster = DutyRoster::latest('id')->first();
        $response->assertRedirect(route('staff-officer.roster.show', $roster->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('duty_rosters', [
            'command_id' => $this->command->id,
            'unit' => $customUnit,
            'status' => 'DRAFT',
        ]);
    }

    /**
     * Test 3: Custom unit appears in dropdown after creation
     */
    public function test_custom_unit_appears_in_dropdown_after_creation(): void
    {
        $this->actingAs($this->staffOfficerUser);

        $customUnit = 'New Custom Unit';

        // Create a roster with custom unit
        DutyRoster::create([
            'command_id' => $this->command->id,
            'unit' => $customUnit,
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
            'prepared_by' => $this->staffOfficerUser->id,
            'status' => 'DRAFT',
        ]);

        // Access create form
        $response = $this->get(route('staff-officer.roster.create'));

        $response->assertStatus(200);
        $response->assertViewIs('forms.roster.create');
        
        // Custom units are merged into the overall unit list
        $viewData = $response->original->getData();
        $this->assertArrayHasKey('allUnits', $viewData);
        $this->assertContains($customUnit, $viewData['allUnits']);
    }

    /**
     * Test 4: Staff Officer can edit roster and change unit to predefined unit
     */
    public function test_staff_officer_can_edit_roster_change_unit_to_predefined(): void
    {
        $this->actingAs($this->staffOfficerUser);

        // Create a roster with initial unit
        $roster = DutyRoster::create([
            'command_id' => $this->command->id,
            'unit' => 'Revenue',
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
            'prepared_by' => $this->staffOfficerUser->id,
            'status' => 'DRAFT',
        ]);

        // Update the roster with a different predefined unit
        $response = $this->put(route('staff-officer.roster.update', $roster->id), [
            'unit' => 'Enforcement',
            'oic_officer_id' => null,
            'second_in_command_officer_id' => null,
        ]);

        $response->assertRedirect();
        
        // Verify unit was updated
        $roster->refresh();
        $this->assertEquals('Enforcement', $roster->unit);
        
        $this->assertDatabaseHas('duty_rosters', [
            'id' => $roster->id,
            'unit' => 'Enforcement',
        ]);
    }

    /**
     * Test 5: Staff Officer can edit roster and change unit to custom unit
     */
    public function test_staff_officer_can_edit_roster_change_unit_to_custom(): void
    {
        $this->actingAs($this->staffOfficerUser);

        // Create a roster with initial unit
        $roster = DutyRoster::create([
            'command_id' => $this->command->id,
            'unit' => 'Revenue',
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
            'prepared_by' => $this->staffOfficerUser->id,
            'status' => 'DRAFT',
        ]);

        $newCustomUnit = 'Updated Custom Unit';

        // Update the roster with a custom unit
        $response = $this->put(route('staff-officer.roster.update', $roster->id), [
            'unit' => '__NEW__',
            'unit_custom' => $newCustomUnit,
            'oic_officer_id' => null,
            'second_in_command_officer_id' => null,
        ]);

        $response->assertRedirect();
        
        // Verify unit was updated
        $roster->refresh();
        $this->assertEquals($newCustomUnit, $roster->unit);
        
        $this->assertDatabaseHas('duty_rosters', [
            'id' => $roster->id,
            'unit' => $newCustomUnit,
        ]);
    }

    /**
     * Test 6: Staff Officer can edit roster and change from custom to predefined unit
     */
    public function test_staff_officer_can_edit_roster_change_from_custom_to_predefined(): void
    {
        $this->actingAs($this->staffOfficerUser);

        // Create a roster with custom unit
        $roster = DutyRoster::create([
            'command_id' => $this->command->id,
            'unit' => 'Old Custom Unit',
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
            'prepared_by' => $this->staffOfficerUser->id,
            'status' => 'DRAFT',
        ]);

        // Update the roster with a predefined unit
        $response = $this->put(route('staff-officer.roster.update', $roster->id), [
            'unit' => 'ICT',
            'oic_officer_id' => null,
            'second_in_command_officer_id' => null,
        ]);

        $response->assertRedirect();
        
        // Verify unit was updated
        $roster->refresh();
        $this->assertEquals('ICT', $roster->unit);
        
        $this->assertDatabaseHas('duty_rosters', [
            'id' => $roster->id,
            'unit' => 'ICT',
        ]);
    }

    /**
     * Test 7: Custom unit created in edit appears in dropdown for next create
     */
    public function test_custom_unit_from_edit_appears_in_create_dropdown(): void
    {
        $this->actingAs($this->staffOfficerUser);

        // Create a roster
        $roster = DutyRoster::create([
            'command_id' => $this->command->id,
            'unit' => 'Revenue',
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
            'prepared_by' => $this->staffOfficerUser->id,
            'status' => 'DRAFT',
        ]);

        $newCustomUnit = 'Edit Created Unit';

        // Edit roster and create a new custom unit
        $this->put(route('staff-officer.roster.update', $roster->id), [
            'unit' => '__NEW__',
            'unit_custom' => $newCustomUnit,
            'oic_officer_id' => null,
            'second_in_command_officer_id' => null,
        ]);

        // Access create form
        $response = $this->get(route('staff-officer.roster.create'));

        $response->assertStatus(200);
        
        // Check that the new custom unit appears in the dropdown
        $viewData = $response->original->getData();
        $this->assertArrayHasKey('allUnits', $viewData);
        $this->assertContains($newCustomUnit, $viewData['allUnits']);
    }

    /**
     * Test 8: Unit field is required when creating roster
     */
    public function test_unit_is_required_when_creating_roster(): void
    {
        $this->actingAs($this->staffOfficerUser);

        $response = $this->post(route('staff-officer.roster.store'), [
            'command_id' => $this->command->id,
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
        ]);

        $response->assertSessionHasErrors(['unit']);
    }

    /**
     * Test 9: Custom unit name is required when __NEW__ is selected
     */
    public function test_custom_unit_name_required_when_new_selected(): void
    {
        $this->actingAs($this->staffOfficerUser);

        $response = $this->post(route('staff-officer.roster.store'), [
            'unit' => '__NEW__',
            'unit_custom' => '', // Empty custom unit
            'command_id' => $this->command->id,
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
        ]);

        // The validation should fail either at validation level or in the controller logic
        // Check for either validation errors or a redirect with error message
        if ($response->isRedirect()) {
            $response->assertSessionHas('error');
        } else {
            $response->assertSessionHasErrors(['unit_custom']);
        }
    }

    /**
     * Test 10: Edit form shows current unit in dropdown
     */
    public function test_edit_form_shows_current_unit(): void
    {
        $this->actingAs($this->staffOfficerUser);

        $roster = DutyRoster::create([
            'command_id' => $this->command->id,
            'unit' => 'Medical',
            'roster_period_start' => date('Y-m-01'),
            'roster_period_end' => date('Y-m-t'),
            'prepared_by' => $this->staffOfficerUser->id,
            'status' => 'DRAFT',
        ]);

        $response = $this->get(route('staff-officer.roster.edit', $roster->id));

        $response->assertStatus(200);
        $response->assertViewIs('forms.roster.edit');
        
        // Verify roster data is passed
        $viewData = $response->original->getData();
        $this->assertArrayHasKey('roster', $viewData);
        $this->assertEquals('Medical', $viewData['roster']->unit);
    }
}

