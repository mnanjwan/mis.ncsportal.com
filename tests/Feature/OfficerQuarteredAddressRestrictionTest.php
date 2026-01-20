<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerQuarteredAddressRestrictionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function quartered_officer_cannot_change_residential_address_but_can_change_phone_and_permanent_home_address(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $role = Role::where('name', 'Officer')->first();
        $this->assertNotNull($role);

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $user->roles()->attach($role, ['is_active' => true, 'assigned_at' => now()]);

        $officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'NCS00001',
            'email' => $user->email,
            'initials' => 'TS',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(8),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'marital_status' => 'Single',
            'entry_qualification' => 'BSc',
            'discipline' => 'Computer Science',
            'present_station' => $command->id,
            'phone_number' => '08000000000',
            'residential_address' => 'Q-001 - Type A',
            'permanent_home_address' => 'Old Permanent Address',
            'is_active' => true,
            'profile_picture_url' => 'officers/default.png', // satisfies onboarding.complete middleware
            'quartered' => true,
        ]);

        // Attempt to change residential while quartered -> should fail.
        $response = $this->actingAs($user)->post(route('officer.settings.contact-details.update'), [
            'phone_number' => '08000000001',
            'residential_address' => 'New Residential Address',
            'permanent_home_address' => 'New Permanent Address', // should not be applied because request is rejected
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['residential_address']);

        $officer->refresh();
        $this->assertSame('Q-001 - Type A', $officer->residential_address);
        $this->assertSame('Old Permanent Address', $officer->permanent_home_address);
        $this->assertSame('08000000000', $officer->phone_number, 'Phone number should not update when request is rejected.');

        // Try again without changing residential -> phone + permanent home should update.
        $response2 = $this->actingAs($user)->post(route('officer.settings.contact-details.update'), [
            'phone_number' => '08000000002',
            'residential_address' => 'Q-001 - Type A',
            'permanent_home_address' => 'New Permanent Address',
        ]);

        $response2->assertStatus(302);
        $response2->assertSessionHasNoErrors();

        $officer->refresh();
        $this->assertSame('Q-001 - Type A', $officer->residential_address);
        $this->assertSame('New Permanent Address', $officer->permanent_home_address);
        $this->assertSame('08000000002', $officer->phone_number);
    }

    /** @test */
    public function non_quartered_officer_can_change_addresses(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $role = Role::where('name', 'Officer')->first();
        $this->assertNotNull($role);

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $user->roles()->attach($role, ['is_active' => true, 'assigned_at' => now()]);

        $officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'NCS00002',
            'email' => $user->email,
            'initials' => 'TS',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(8),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'marital_status' => 'Single',
            'entry_qualification' => 'BSc',
            'discipline' => 'Computer Science',
            'present_station' => $command->id,
            'phone_number' => '08000000000',
            'residential_address' => 'Old Residential Address',
            'permanent_home_address' => 'Old Permanent Address',
            'is_active' => true,
            'profile_picture_url' => 'officers/default.png', // satisfies onboarding.complete middleware
            'quartered' => false,
        ]);

        $response = $this->actingAs($user)->post(route('officer.settings.contact-details.update'), [
            'phone_number' => '08000000003',
            'residential_address' => 'New Residential Address',
            'permanent_home_address' => 'New Permanent Address',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $officer->refresh();
        $this->assertSame('New Residential Address', $officer->residential_address);
        $this->assertSame('New Permanent Address', $officer->permanent_home_address);
        $this->assertSame('08000000003', $officer->phone_number);
    }
}

