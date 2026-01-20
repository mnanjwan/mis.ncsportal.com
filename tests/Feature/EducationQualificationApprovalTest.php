<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\EducationChangeRequest;
use App\Models\EducationChangeRequestDocument;
use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('local');

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
        $pdf = UploadedFile::fake()->create('certificate.pdf', 120, 'application/pdf');
        $png = UploadedFile::fake()->image('photo.png')->size(300);

        $response = $this->actingAs($this->officerUser)->post(route('officer.education-requests.store'), [
            'university' => 'University of Test',
            'qualification' => 'MSc',
            'discipline' => 'Computer Science',
            'year_obtained' => 2023,
            'documents' => [$pdf, $png],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('officer.education-requests.index'));

        $this->assertSame(1, EducationChangeRequest::count());
        $req = EducationChangeRequest::first();
        $this->assertSame('PENDING', $req->status);
        $this->assertSame($this->officer->id, $req->officer_id);

        $this->assertSame(2, EducationChangeRequestDocument::where('education_change_request_id', $req->id)->count());
        $docs = EducationChangeRequestDocument::where('education_change_request_id', $req->id)->get();
        foreach ($docs as $doc) {
            $this->assertTrue(
                Storage::disk('local')->exists($doc->file_path),
                "Expected uploaded file to exist at path: {$doc->file_path}"
            );
        }
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

    /** @test */
    public function other_officers_cannot_download_someone_elses_education_request_documents(): void
    {
        $otherOfficerUser = User::create([
            'email' => 'other.officer@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $officerRole = Role::where('name', 'Officer')->first();
        if ($officerRole) {
            $otherOfficerUser->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);
        }

        // Create minimal officer record for auth checks
        Officer::create([
            'user_id' => $otherOfficerUser->id,
            'service_number' => 'NCS99999',
            'email' => 'other.officer.record@test.ncs.gov.ng',
            'initials' => 'OO',
            'surname' => 'OTHER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(5),
            'date_of_present_appointment' => now()->subYears(1),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL10',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Other Address',
            'phone_number' => '08000000009',
            'present_station' => Command::first()->id,
            'is_active' => true,
            'is_deceased' => false,
            'profile_picture_url' => 'test.jpg',
        ]);

        $req = EducationChangeRequest::create([
            'officer_id' => $this->officer->id,
            'university' => 'University of Test',
            'qualification' => 'MSc',
            'discipline' => 'Computer Science',
            'year_obtained' => 2023,
            'status' => 'PENDING',
        ]);

        $path = "education_request_docs/{$req->id}/certificate.pdf";
        Storage::disk('local')->put($path, 'dummy');

        $doc = EducationChangeRequestDocument::create([
            'education_change_request_id' => $req->id,
            'file_name' => 'certificate.pdf',
            'file_path' => $path,
            'file_size' => 5,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->officerUser->id,
        ]);

        $response = $this->actingAs($otherOfficerUser)->get(route('education-requests.documents.download', [
            'requestId' => $req->id,
            'documentId' => $doc->id,
        ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function requesting_officer_can_download_their_uploaded_document(): void
    {
        $req = EducationChangeRequest::create([
            'officer_id' => $this->officer->id,
            'university' => 'University of Test',
            'qualification' => 'MSc',
            'discipline' => 'Computer Science',
            'year_obtained' => 2023,
            'status' => 'PENDING',
        ]);

        $path = "education_request_docs/{$req->id}/certificate.pdf";
        Storage::disk('local')->put($path, 'dummy');

        $doc = EducationChangeRequestDocument::create([
            'education_change_request_id' => $req->id,
            'file_name' => 'certificate.pdf',
            'file_path' => $path,
            'file_size' => 5,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->officerUser->id,
        ]);

        $response = $this->actingAs($this->officerUser)->get(route('education-requests.documents.download', [
            'requestId' => $req->id,
            'documentId' => $doc->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}

