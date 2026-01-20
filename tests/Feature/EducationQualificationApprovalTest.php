<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\EducationChangeRequest;
use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EducationQualificationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $hrdUser;
    private User $officerUser;
    private Officer $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Mail::fake();

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $hrdRole = Role::firstOrCreate(
            ['code' => 'HRD'],
            ['name' => 'HRD', 'description' => 'Human Resources Department', 'access_level' => 'system_wide']
        );
        $officerRole = Role::firstOrCreate(
            ['code' => 'OFFICER'],
            ['name' => 'Officer', 'description' => 'Officer', 'access_level' => 'personal']
        );

        $this->hrdUser = User::create([
            'email' => 'hrd@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->hrdUser->roles()->attach($hrdRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $this->officerUser = User::create([
            'email' => 'officer@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->officerUser->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $this->officer = Officer::create([
            'user_id' => $this->officerUser->id,
            'service_number' => 'NCS12345',
            'email' => 'officer.record@test.ncs.gov.ng',
            'initials' => 'TO',
            'surname' => 'TESTOFFICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(35),
            'date_of_first_appointment' => now()->subYears(10),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL10',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000000',
            'present_station' => $command->id,
            'is_active' => true,
            'is_deceased' => false,
            'profile_picture_url' => 'test.jpg', // satisfies onboarding.complete middleware for Officer role
        ]);
    }

    /** @test */
    public function officer_can_submit_education_qualification_request(): void
    {
        $response = $this->actingAs($this->officerUser)->post(route('officer.education-requests.store'), [
            'university' => 'University of Test',
            'qualification' => 'MSc',
            'discipline' => 'Computer Science',
            'year_obtained' => 2023,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('officer.education-requests.index'));

        $this->assertSame(1, EducationChangeRequest::count());
        $req = EducationChangeRequest::first();
        $this->assertSame('PENDING', $req->status);
        $this->assertSame($this->officer->id, $req->officer_id);
    }

    /** @test */
    public function hrd_can_approve_and_it_is_appended_into_officer_educational_history(): void
    {
        $req = EducationChangeRequest::create([
            'officer_id' => $this->officer->id,
            'university' => 'University of Test',
            'qualification' => 'MSc',
            'discipline' => 'Computer Science',
            'year_obtained' => 2023,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->hrdUser)->post(route('hrd.education-requests.approve', $req->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('hrd.education-requests.pending'));

        $req->refresh();
        $this->assertSame('APPROVED', $req->status);

        $this->officer->refresh();
        $education = json_decode($this->officer->additional_qualification ?? '[]', true);
        $this->assertIsArray($education);

        $found = false;
        foreach ($education as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            if (
                ($entry['university'] ?? null) === 'University of Test' &&
                ($entry['qualification'] ?? null) === 'MSc' &&
                (string) ($entry['year_obtained'] ?? null) === '2023'
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Approved education entry was not appended into officer additional_qualification JSON.');
    }

    /** @test */
    public function hrd_rejection_requires_reason_and_does_not_mutate_officer_record(): void
    {
        $req = EducationChangeRequest::create([
            'officer_id' => $this->officer->id,
            'university' => 'University of Test',
            'qualification' => 'PhD',
            'discipline' => 'Economics',
            'year_obtained' => 2022,
            'status' => 'PENDING',
        ]);

        $this->assertNull($this->officer->additional_qualification);

        // Missing reason should fail validation and keep request pending
        $response = $this->actingAs($this->hrdUser)->post(route('hrd.education-requests.reject', $req->id), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['rejection_reason']);

        $req->refresh();
        $this->assertSame('PENDING', $req->status);

        // Reject properly
        $response2 = $this->actingAs($this->hrdUser)->post(route('hrd.education-requests.reject', $req->id), [
            'rejection_reason' => 'Insufficient documentation',
        ]);
        $response2->assertStatus(302);
        $response2->assertRedirect(route('hrd.education-requests.pending'));

        $req->refresh();
        $this->assertSame('REJECTED', $req->status);

        $this->officer->refresh();
        $this->assertNull($this->officer->additional_qualification, 'Officer record should not change on rejection.');
    }
}

