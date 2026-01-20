<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\EmolumentTimeline;
use App\Models\NextOfKin;
use App\Models\Officer;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePictureAfterPromotionGateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function promotion_approval_requires_profile_picture_update_and_blocks_raise_emolument(): void
    {
        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $officerRole = Role::firstOrCreate(
            ['name' => 'Officer'],
            ['code' => 'OFFICER', 'description' => 'Officer', 'access_level' => 'personal']
        );

        $user = User::create([
            'email' => 'officer+promo@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'NCS99911',
            'email' => $user->email,
            'initials' => 'OF',
            'surname' => 'FICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(8),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000000',
            'present_station' => $command->id,
            'is_active' => true,
            // mark onboarding complete for onboarding.complete middleware
            'profile_picture_url' => 'profiles/old.jpg',
            'profile_picture_updated_at' => now()->subDays(30),
        ]);

        Promotion::create([
            'officer_id' => $officer->id,
            'from_rank' => 'SC',
            'to_rank' => 'CSC',
            'promotion_date' => now()->toDateString(),
            'approved_by_board' => false,
        ])->update(['approved_by_board' => true]);

        $officer->refresh();
        $this->assertNotNull($officer->profile_picture_required_after_promotion_at);
        $this->assertTrue($officer->needsProfilePictureUpdateAfterPromotion());

        $response = $this->actingAs($user)->get(route('emolument.raise'));
        $response->assertRedirect(route('officer.profile'));
        $response->assertSessionHas('error', 'Change Profile Picture hasn’t been done yet');
    }

    /** @test */
    public function officer_can_open_raise_emolument_after_updating_profile_picture_post_promotion(): void
    {
        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC002',
            'is_active' => true,
        ]);

        $officerRole = Role::firstOrCreate(
            ['name' => 'Officer'],
            ['code' => 'OFFICER', 'description' => 'Officer', 'access_level' => 'personal']
        );

        $user = User::create([
            'email' => 'officer+promo2@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'NCS99912',
            'email' => $user->email,
            'initials' => 'OF',
            'surname' => 'FICER2',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(8),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000001',
            'present_station' => $command->id,
            'is_active' => true,
            'profile_picture_url' => 'profiles/old.jpg',
            'profile_picture_updated_at' => now()->subDays(30),
        ]);

        $promotion = Promotion::create([
            'officer_id' => $officer->id,
            'from_rank' => 'SC',
            'to_rank' => 'CSC',
            'promotion_date' => now()->toDateString(),
            'approved_by_board' => false,
        ]);
        $promotion->update(['approved_by_board' => true]);

        $officer->refresh();

        // Officer complies by updating their profile picture after promotion
        $officer->update(['profile_picture_updated_at' => now()->addSecond()]);

        EmolumentTimeline::create([
            'year' => (int) date('Y'),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('emolument.raise'));
        $response->assertStatus(200);
        $response->assertSee('Raise Emolument');
    }

    /** @test */
    public function updating_profile_picture_sets_profile_picture_updated_at(): void
    {
        Storage::fake('public');

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC003',
            'is_active' => true,
        ]);

        $officerRole = Role::firstOrCreate(
            ['name' => 'Officer'],
            ['code' => 'OFFICER', 'description' => 'Officer', 'access_level' => 'personal']
        );

        $user = User::create([
            'email' => 'officer+photo@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'NCS99913',
            'email' => $user->email,
            'initials' => 'OF',
            'surname' => 'PHOTO',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(8),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000002',
            'present_station' => $command->id,
            'is_active' => true,
            // needed for onboarding.complete middleware protecting officer routes
            'profile_picture_url' => 'profiles/old.jpg',
        ]);

        NextOfKin::create([
            'officer_id' => $officer->id,
            'name' => 'NOK',
            'relationship' => 'Sibling',
            'phone_number' => '08000000003',
            'address' => 'NOK Address',
            'is_primary' => true,
        ]);

        $this->assertNull($officer->fresh()->profile_picture_updated_at);

        $response = $this->actingAs($user)->post(route('officer.profile.update-picture'), [
            'profile_picture' => UploadedFile::fake()->image('avatar.jpg', 600, 600),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'profile_picture_url',
        ]);

        $officer->refresh();
        $this->assertNotNull($officer->profile_picture_updated_at);
        $this->assertNotEmpty($officer->profile_picture_url);

        Storage::disk('public')->assertExists($officer->profile_picture_url);
    }
}

