<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\Officer;
use App\Models\OfficerQuarter;
use App\Models\Quarter;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuildingUnitFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function building_unit_can_access_dashboard()
    {
        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        $buildingUnitRole = Role::firstOrCreate(
            ['name' => 'Building Unit'],
            ['code' => 'BUILDING_UNIT', 'description' => 'Building Unit - Accommodation Manager', 'access_level' => 'command_level']
        );

        $user = User::create([
            'email' => 'building.unit@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $user->roles()->attach($buildingUnitRole->id, [
            'command_id' => $command->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('building.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function building_unit_can_view_quarters()
    {
        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        $buildingUnitRole = Role::firstOrCreate(
            ['name' => 'Building Unit'],
            ['code' => 'BUILDING_UNIT', 'description' => 'Building Unit - Accommodation Manager', 'access_level' => 'command_level']
        );

        $user = User::create([
            'email' => 'building.unit2@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $user->roles()->attach($buildingUnitRole->id, [
            'command_id' => $command->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('building.quarters'));

        $response->assertStatus(200);
    }

    /** @test */
    public function building_unit_can_allocate_quarter_to_officer_and_officer_becomes_quartered_after_acceptance()
    {
        // Get or create a command
        $command = Command::first();
        if (!$command) {
            $command = Command::create([
                'name' => 'Test Command',
                'code' => 'TEST',
                'is_active' => true,
            ]);
        }

        // Get or create Building Unit role
        $buildingUnitRole = Role::firstOrCreate([
            'name' => 'Building Unit',
        ], [
            'code' => 'BUILDING_UNIT',
            'description' => 'Building Unit - Accommodation Manager',
            'access_level' => 'command_level',
        ]);

        // Create Building Unit user with command assignment
        $buildingUnitUser = User::create([
            'email' => 'test.building.unit@ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Assign Building Unit role to user with command
        $buildingUnitUser->roles()->attach($buildingUnitRole->id, [
            'command_id' => $command->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // Create an officer in the same command
        $officer = Officer::create([
            'service_number' => 'NCS12345',
            'initials' => 'T',
            'surname' => 'Test Officer',
            'sex' => 'M',
            'email' => 'test.officer' . uniqid() . '@ncs.gov.ng',
            'date_of_birth' => Carbon::now()->subYears(30),
            'date_of_first_appointment' => Carbon::now()->subYears(5),
            'date_of_present_appointment' => Carbon::now()->subMonths(3),
            'substantive_rank' => 'CSC',
            'salary_grade_level' => 'GL08',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '123 Test Street',
            'phone_number' => '08012345678',
            'present_station' => $command->id,
            'quartered' => false,
            'is_active' => true,
            'is_deceased' => false,
            'interdicted' => false,
            'suspended' => false,
            'dismissed' => false,
        ]);

        // Create a user for the officer
        $officerUser = User::create([
            'email' => 'test.officer@ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $officer->update(['user_id' => $officerUser->id]);

        // Create a quarter in the command
        $quarter = Quarter::create([
            'command_id' => $command->id,
            'quarter_number' => 'Q-001',
            'quarter_type' => 'Type A',
            'is_occupied' => false,
            'is_active' => true,
        ]);

        // Authenticate as Building Unit user
        Sanctum::actingAs($buildingUnitUser);

        // Step 1: Allocate quarter to officer (creates PENDING allocation)
        $response = $this->postJson('/api/v1/quarters/allocate', [
            'officer_id' => $officer->id,
            'quarter_id' => $quarter->id,
            'allocation_date' => now()->format('Y-m-d'),
        ]);

        // Assert allocation was successful
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'PENDING',
                    'message' => 'Quarter allocation pending officer acceptance',
                ],
            ]);

        // Verify allocation was created with PENDING status
        $allocation = OfficerQuarter::where('officer_id', $officer->id)
            ->where('quarter_id', $quarter->id)
            ->first();

        $this->assertNotNull($allocation, 'Allocation should be created');
        $this->assertEquals('PENDING', $allocation->status, 'Allocation should be PENDING');
        $this->assertTrue($allocation->is_current, 'Allocation should be current');

        // Step 2: Verify officer is NOT quartered yet (still PENDING)
        $officer->refresh();
        $this->assertFalse($officer->quartered, 'Officer should NOT be quartered while allocation is PENDING');

        // Step 3: Verify officer appears as NOT quartered in the API list
        $listResponse = $this->getJson('/api/v1/officers?quartered=false');
        $listResponse->assertStatus(200)
            ->assertJsonPath('success', true);
        
        $officers = $listResponse->json('data');
        $foundOfficer = collect($officers)->firstWhere('id', $officer->id);
        $this->assertNotNull($foundOfficer, 'Officer should appear in the list');
        $this->assertFalse($foundOfficer['quartered'], 'Officer should show as NOT quartered in the list');

        // Step 4: Accept the allocation as the officer
        Sanctum::actingAs($officerUser);

        $acceptResponse = $this->postJson("/api/v1/quarters/allocations/{$allocation->id}/accept");

        // Assert acceptance was successful
        $acceptResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'ACCEPTED',
                ],
            ]);

        // Step 5: Verify allocation is now ACCEPTED
        $allocation->refresh();
        $this->assertEquals('ACCEPTED', $allocation->status, 'Allocation should be ACCEPTED');
        $this->assertNotNull($allocation->accepted_at, 'Accepted at should be set');

        // Step 6: Verify officer IS now quartered
        $officer->refresh();
        $this->assertTrue($officer->quartered, 'Officer SHOULD be quartered after accepting allocation');
        $this->assertSame('Q-001 - Type A', $officer->residential_address, 'Officer residential address should be auto-synced to the accepted quarter');

        // Step 7: Verify quarter is marked as occupied
        $quarter->refresh();
        $this->assertTrue($quarter->is_occupied, 'Quarter should be marked as occupied');

        // Step 8: Verify officer appears as quartered in the API list (as Building Unit)
        Sanctum::actingAs($buildingUnitUser);

        $quarteredListResponse = $this->getJson('/api/v1/officers?quartered=true');
        $quarteredListResponse->assertStatus(200)
            ->assertJsonPath('success', true);
        
        $quarteredOfficers = $quarteredListResponse->json('data');
        $quarteredOfficer = collect($quarteredOfficers)->firstWhere('id', $officer->id);
        $this->assertNotNull($quarteredOfficer, 'Officer should appear in quartered list');
        $this->assertTrue($quarteredOfficer['quartered'], 'Officer should show as quartered in the list');

        // Step 9: Verify officer does NOT appear in non-quartered list
        $nonQuarteredListResponse = $this->getJson('/api/v1/officers?quartered=false');
        $nonQuarteredListResponse->assertStatus(200);
        
        $nonQuarteredOfficers = $nonQuarteredListResponse->json('data');
        $shouldNotBeFound = collect($nonQuarteredOfficers)->firstWhere('id', $officer->id);
        $this->assertNull($shouldNotBeFound, 'Officer should NOT appear in non-quartered list');
    }

    /** @test */
    public function officer_does_not_appear_as_quartered_while_allocation_is_pending()
    {
        // Get or create a command
        $command = Command::first();
        if (!$command) {
            $command = Command::create([
                'name' => 'Test Command 2',
                'code' => 'TEST2',
                'is_active' => true,
            ]);
        }

        // Get or create Building Unit role
        $buildingUnitRole = Role::firstOrCreate(
            ['name' => 'Building Unit'],
            ['code' => 'BUILDING_UNIT', 'description' => 'Building Unit - Accommodation Manager', 'access_level' => 'command_level']
        );

        // Create Building Unit user with command assignment
        $buildingUnitUser = User::create([
            'email' => 'test.building.unit2@ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Assign Building Unit role to user with command
        $buildingUnitUser->roles()->attach($buildingUnitRole->id, [
            'command_id' => $command->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // Create an officer in the same command
        $officer = Officer::create([
            'service_number' => 'NCS54321',
            'initials' => 'P',
            'surname' => 'Pending Officer',
            'sex' => 'M',
            'email' => 'pending.officer' . uniqid() . '@ncs.gov.ng',
            'date_of_birth' => Carbon::now()->subYears(28),
            'date_of_first_appointment' => Carbon::now()->subYears(4),
            'date_of_present_appointment' => Carbon::now()->subMonths(2),
            'substantive_rank' => 'ASC II',
            'salary_grade_level' => 'GL07',
            'state_of_origin' => 'Abuja',
            'lga' => 'Garki',
            'geopolitical_zone' => 'North Central',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => '456 Test Avenue',
            'phone_number' => '08023456789',
            'present_station' => $command->id,
            'quartered' => false,
            'is_active' => true,
            'is_deceased' => false,
            'interdicted' => false,
            'suspended' => false,
            'dismissed' => false,
        ]);

        // Create a quarter in the command
        $quarter = Quarter::create([
            'command_id' => $command->id,
            'quarter_number' => 'Q-002',
            'quarter_type' => 'Type B',
            'is_occupied' => false,
            'is_active' => true,
        ]);

        // Authenticate as Building Unit user
        Sanctum::actingAs($buildingUnitUser);

        // Allocate quarter (creates PENDING allocation)
        $response = $this->postJson('/api/v1/quarters/allocate', [
            'officer_id' => $officer->id,
            'quarter_id' => $quarter->id,
        ]);

        $response->assertStatus(200);

        // Verify officer is still NOT quartered
        $officer->refresh();
        $this->assertFalse($officer->quartered, 'Officer should remain NOT quartered with PENDING allocation');

        // The key assertion: Verify officer remains NOT quartered while allocation is PENDING
        // This is the critical behavior - allocation status is PENDING, officer quartered status should remain false
        $officer->refresh();
        $this->assertFalse($officer->quartered, 'Officer should remain NOT quartered with PENDING allocation');
        
        // Verify the allocation exists and is PENDING
        $allocation = OfficerQuarter::where('officer_id', $officer->id)
            ->where('quarter_id', $quarter->id)
            ->first();
        $this->assertNotNull($allocation, 'Allocation should exist');
        $this->assertEquals('PENDING', $allocation->status, 'Allocation should be PENDING');
        
        // Verify officer does NOT appear in quartered list
        $quarteredListResponse = $this->getJson('/api/v1/officers?quartered=true');
        $quarteredListResponse->assertStatus(200);
        
        $quarteredOfficers = $quarteredListResponse->json('data');
        $shouldNotBeFound = collect($quarteredOfficers)->firstWhere('id', $officer->id);
        $this->assertNull($shouldNotBeFound, 'Officer with PENDING allocation should NOT appear in quartered list');
    }
}
