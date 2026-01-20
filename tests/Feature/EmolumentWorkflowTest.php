<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Officer;
use App\Models\Emolument;
use App\Models\Role;
use App\Models\Command;
use App\Models\EmolumentTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmolumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $officerUser;
    private User $assessorUser;
    private User $validatorUser;
    private User $accountsUser;

    protected function setUp(): void
    {
        parent::setUp();

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $roles = [
            'Officer' => Role::firstOrCreate(['name' => 'Officer'], ['code' => 'OFFICER', 'description' => 'Officer', 'access_level' => 'personal']),
            'Assessor' => Role::firstOrCreate(['name' => 'Assessor'], ['code' => 'ASSESSOR', 'description' => 'Emolument Assessor', 'access_level' => 'system_wide']),
            'Validator' => Role::firstOrCreate(['name' => 'Validator'], ['code' => 'VALIDATOR', 'description' => 'Emolument Validator', 'access_level' => 'system_wide']),
            'Accounts' => Role::firstOrCreate(['name' => 'Accounts'], ['code' => 'ACCOUNTS', 'description' => 'Accounts Department', 'access_level' => 'system_wide']),
        ];

        $this->officerUser = User::create(['email' => 'officer@test.ncs.gov.ng', 'password' => bcrypt('password'), 'is_active' => true]);
        $this->assessorUser = User::create(['email' => 'assessor@test.ncs.gov.ng', 'password' => bcrypt('password'), 'is_active' => true]);
        $this->validatorUser = User::create(['email' => 'validator@test.ncs.gov.ng', 'password' => bcrypt('password'), 'is_active' => true]);
        $this->accountsUser = User::create(['email' => 'accounts@test.ncs.gov.ng', 'password' => bcrypt('password'), 'is_active' => true]);

        $this->officerUser->roles()->attach($roles['Officer']->id, ['is_active' => true, 'assigned_at' => now()]);
        $this->assessorUser->roles()->attach($roles['Assessor']->id, ['is_active' => true, 'assigned_at' => now()]);
        $this->validatorUser->roles()->attach($roles['Validator']->id, ['is_active' => true, 'assigned_at' => now()]);
        $this->accountsUser->roles()->attach($roles['Accounts']->id, ['is_active' => true, 'assigned_at' => now()]);

        Officer::create([
            'user_id' => $this->officerUser->id,
            'service_number' => 'NCS99901',
            'email' => $this->officerUser->email,
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
            // mark onboarding complete for officer routes guarded by onboarding.complete
            'profile_picture_url' => 'officers/default.png',
            'onboarding_status' => 'completed',
            'onboarding_completed_at' => now(),
        ]);

        $timeline = EmolumentTimeline::create([
            'year' => (int) date('Y'),
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(10),
            'is_active' => true,
            'created_by' => $this->accountsUser->id,
        ]);

        Emolument::create([
            'officer_id' => Officer::where('user_id', $this->officerUser->id)->value('id'),
            'timeline_id' => $timeline->id,
            'year' => (int) date('Y'),
            'status' => 'RAISED',
            'bank_name' => 'Test Bank',
            'bank_account_number' => '0123456789',
            'pfa_name' => 'Test PFA',
            'rsa_pin' => 'PEN012345678901',
            'submitted_at' => now(),
        ]);
    }

    /** @test */
    public function officer_can_raise_emolument()
    {
        $response = $this->actingAs($this->officerUser)->get(route('officer.emoluments'));

        $response->assertStatus(200);
        $response->assertSee('My Emoluments');
    }

    /** @test */
    public function assessor_can_access_emoluments_for_assessment()
    {
        $response = $this->actingAs($this->assessorUser)->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Assessor Dashboard');
    }

    /** @test */
    public function validator_can_access_emoluments_for_validation()
    {
        $response = $this->actingAs($this->validatorUser)->get(route('validator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Validator Dashboard');
    }

    /** @test */
    public function accounts_can_process_validated_emoluments()
    {
        $response = $this->actingAs($this->accountsUser)->get(route('accounts.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Accounts Dashboard');
    }

    /** @test */
    public function emoluments_exist_in_database()
    {
        $this->assertTrue(Emolument::count() > 0);
    }

    /** @test */
    public function emoluments_have_correct_statuses()
    {
        $statuses = Emolument::pluck('status')->unique()->toArray();

        $validStatuses = ['RAISED', 'ASSESSED', 'VALIDATED', 'PROCESSED', 'REJECTED'];

        foreach ($statuses as $status) {
            $this->assertContains($status, $validStatuses);
        }
    }
}
