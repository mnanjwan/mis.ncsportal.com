<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Officer;
use App\Models\Command;
use App\Models\Zone;
use App\Models\ManningDeployment;
use App\Models\ManningDeploymentAssignment;
use App\Models\OfficerPosting;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CommandDurationAddToDraftTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrdUser;
    protected Zone $zone;
    protected Command $command;
    protected Command $otherCommand;
    protected $officers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = Zone::create([
            'name' => 'Test Zone',
            'code' => 'TZ',
            'is_active' => true,
        ]);

        $this->command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC',
            'zone_id' => $this->zone->id,
            'is_active' => true,
        ]);

        $this->otherCommand = Command::create([
            'name' => 'Other Command',
            'code' => 'OC',
            'zone_id' => $this->zone->id,
            'is_active' => true,
        ]);

        $this->hrdUser = User::create([
            'email' => 'hrd-cmd-duration@test.ncs.gov.ng',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $hrdRole = Role::firstOrCreate(
            ['code' => 'HRD'],
            [
                'name' => 'HRD',
                'code' => 'HRD',
                'description' => 'Human Resources Department',
                'access_level' => 'system_wide',
            ]
        );
        $this->hrdUser->roles()->attach($hrdRole->id, [
            'assigned_at' => now(),
            'assigned_by' => $this->hrdUser->id,
            'is_active' => true,
        ]);

        $this->officers = collect([
            Officer::create([
                'service_number' => 'NCS10001',
                'initials' => 'A.B',
                'surname' => 'OfficerOne',
                'sex' => 'M',
                'date_of_birth' => Carbon::now()->subYears(35),
                'date_of_first_appointment' => Carbon::now()->subYears(10),
                'date_of_present_appointment' => Carbon::now()->subYears(2),
                'substantive_rank' => 'DC',
                'salary_grade_level' => 'GL10',
                'state_of_origin' => 'Lagos',
                'lga' => 'Ikeja',
                'geopolitical_zone' => 'South West',
                'entry_qualification' => 'B.Sc',
                'permanent_home_address' => 'Address 1',
                'phone_number' => '08011111111',
                'email' => 'officer1@test.ncs.gov.ng',
                'present_station' => $this->command->id,
                'is_active' => true,
                'interdicted' => false,
                'suspended' => false,
                'dismissed' => false,
                'ongoing_investigation' => false,
            ]),
            Officer::create([
                'service_number' => 'NCS10002',
                'initials' => 'C.D',
                'surname' => 'OfficerTwo',
                'sex' => 'F',
                'date_of_birth' => Carbon::now()->subYears(32),
                'date_of_first_appointment' => Carbon::now()->subYears(8),
                'date_of_present_appointment' => Carbon::now()->subYears(1),
                'substantive_rank' => 'SC',
                'salary_grade_level' => 'GL12',
                'state_of_origin' => 'Abuja',
                'lga' => 'Gwagwalada',
                'geopolitical_zone' => 'North Central',
                'entry_qualification' => 'B.Sc',
                'permanent_home_address' => 'Address 2',
                'phone_number' => '08022222222',
                'email' => 'officer2@test.ncs.gov.ng',
                'present_station' => $this->command->id,
                'is_active' => true,
                'interdicted' => false,
                'suspended' => false,
                'dismissed' => false,
                'ongoing_investigation' => false,
            ]),
        ]);
    }

    #[Test]
    public function add_to_draft_from_command_duration_adds_officers_to_hrd_draft()
    {
        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'zone_id' => $this->zone->id,
            'officer_ids' => json_encode($this->officers->pluck('id')->toArray()),
        ]);

        $response->assertRedirect(route('hrd.manning-deployments.draft'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('manning_deployment_assignments', 2);

        $assignments = ManningDeploymentAssignment::whereIn('officer_id', $this->officers->pluck('id'))->get();
        $this->assertCount(2, $assignments);

        $deploymentId = $assignments->first()->manning_deployment_id;
        $deployment = ManningDeployment::find($deploymentId);
        $this->assertNotNull($deployment);
        $this->assertSame('DRAFT', $deployment->status);
        $this->assertEquals($this->hrdUser->id, $deployment->created_by);

        foreach ($assignments as $assignment) {
            $this->assertEquals($this->command->id, $assignment->from_command_id);
            $this->assertEquals($this->command->id, $assignment->to_command_id);
        }
    }

    #[Test]
    public function add_to_draft_with_zone_only_no_command_succeeds()
    {
        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'zone_id' => $this->zone->id,
            'command_id' => '', // optional, not sent or empty
            'officer_ids' => json_encode([$this->officers->first()->id]),
        ]);

        $response->assertRedirect(route('hrd.manning-deployments.draft'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('manning_deployment_assignments', [
            'officer_id' => $this->officers->first()->id,
        ]);
    }

    #[Test]
    public function add_to_draft_with_command_id_adds_to_same_hrd_draft()
    {
        $existingDraft = ManningDeployment::create([
            'deployment_number' => 'DEP-2026-0228-001',
            'status' => 'DRAFT',
            'created_by' => $this->hrdUser->id,
        ]);

        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'zone_id' => $this->zone->id,
            'command_id' => $this->command->id,
            'officer_ids' => json_encode([$this->officers->first()->id]),
        ]);

        $response->assertRedirect(route('hrd.manning-deployments.draft'));
        $response->assertSessionHas('success');

        $assignment = ManningDeploymentAssignment::where('officer_id', $this->officers->first()->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($existingDraft->id, $assignment->manning_deployment_id);
    }

    #[Test]
    public function add_to_draft_requires_zone_id()
    {
        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'officer_ids' => json_encode($this->officers->pluck('id')->toArray()),
        ]);

        $response->assertSessionHasErrors(['zone_id']);
        $this->assertDatabaseCount('manning_deployment_assignments', 0);
    }

    #[Test]
    public function add_to_draft_requires_officer_ids()
    {
        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'zone_id' => $this->zone->id,
        ]);

        $response->assertSessionHasErrors(['officer_ids']);
    }

    #[Test]
    public function awaiting_release_officer_added_from_command_duration_shows_in_draft()
    {
        $officer = $this->officers->first();

        // Current posting (so they have present_station and a posting to this command)
        OfficerPosting::create([
            'officer_id' => $officer->id,
            'command_id' => $this->command->id,
            'posting_date' => now()->subYears(2),
            'is_current' => true,
            'release_letter_printed' => false,
            'accepted_by_new_command' => false,
        ]);

        // Pending "out" posting (Awaiting Release) - not yet released to other command
        OfficerPosting::create([
            'officer_id' => $officer->id,
            'command_id' => $this->otherCommand->id,
            'posting_date' => now(),
            'is_current' => false,
            'release_letter_printed' => false,
            'accepted_by_new_command' => false,
        ]);

        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'zone_id' => $this->zone->id,
            'officer_ids' => json_encode([$officer->id]),
        ]);

        $response->assertRedirect(route('hrd.manning-deployments.draft'));
        $response->assertSessionHas('success');

        $assignment = ManningDeploymentAssignment::where('officer_id', $officer->id)->first();
        $this->assertNotNull($assignment, 'Officer (Awaiting Release) should be added to draft');

        $draftResponse = $this->actingAs($this->hrdUser)->get(route('hrd.manning-deployments.draft'));
        $draftResponse->assertOk();
        $draftResponse->assertSee($officer->surname, false);
        $draftResponse->assertSee($officer->service_number, false);
    }

    #[Test]
    public function awaiting_documentation_officer_added_from_command_duration_shows_in_draft()
    {
        $officer = $this->officers->last();

        OfficerPosting::create([
            'officer_id' => $officer->id,
            'command_id' => $this->command->id,
            'posting_date' => now()->subYear(),
            'is_current' => true,
            'release_letter_printed' => false,
            'accepted_by_new_command' => false,
        ]);

        OfficerPosting::create([
            'officer_id' => $officer->id,
            'command_id' => $this->otherCommand->id,
            'posting_date' => now(),
            'is_current' => false,
            'documented_at' => null,
            'release_letter_printed' => true,
            'accepted_by_new_command' => false,
        ]);

        $response = $this->actingAs($this->hrdUser)->post(route('hrd.command-duration.add-to-draft'), [
            'zone_id' => $this->zone->id,
            'officer_ids' => json_encode([$officer->id]),
        ]);

        $response->assertRedirect(route('hrd.manning-deployments.draft'));
        $response->assertSessionHas('success');

        $assignment = ManningDeploymentAssignment::where('officer_id', $officer->id)->first();
        $this->assertNotNull($assignment, 'Officer (Awaiting Documentation) should be added to draft');

        $draftResponse = $this->actingAs($this->hrdUser)->get(route('hrd.manning-deployments.draft'));
        $draftResponse->assertOk();
        $draftResponse->assertSee($officer->surname, false);
    }
}
