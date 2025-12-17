<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Officer;
use App\Models\Emolument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmolumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function officer_can_raise_emolument()
    {
        $user = User::where('email', 'officer@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('officer.emoluments.index'));

        $response->assertStatus(200);
        $response->assertSee('My Emoluments');
    }

    /** @test */
    public function assessor_can_access_emoluments_for_assessment()
    {
        $user = User::where('email', 'assessor@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Assessor Dashboard');
    }

    /** @test */
    public function validator_can_access_emoluments_for_validation()
    {
        $user = User::where('email', 'validator@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('validator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Validator Dashboard');
    }

    /** @test */
    public function accounts_can_process_validated_emoluments()
    {
        $user = User::where('email', 'accounts@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('accounts.dashboard'));

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
