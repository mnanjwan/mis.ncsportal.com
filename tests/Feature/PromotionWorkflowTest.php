<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\Officer;
use App\Models\Promotion;
use App\Models\PromotionEligibilityList;
use App\Models\PromotionEligibilityListItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromotionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createRole(string $name, string $code, string $accessLevel = 'system_wide'): Role
    {
        return Role::firstOrCreate(
            ['name' => $name],
            ['code' => $code, 'description' => $name, 'access_level' => $accessLevel]
        );
    }

    private function attachRole(User $user, Role $role): void
    {
        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
            'assigned_by' => $user->id,
            'is_active' => true,
        ]);
    }

    private function createOfficer(array $overrides = []): Officer
    {
        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $defaults = [
            'service_number' => 'NCS' . str_pad((string) rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'initials' => 'TS',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(35),
            'date_of_first_appointment' => now()->subYears(10),
            'date_of_present_appointment' => now()->subYears(3),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000000',
            'email' => 'officer' . uniqid() . '@ncs.gov.ng',
            'present_station' => $command->id,
            'is_active' => true,
        ];

        return Officer::create(array_merge($defaults, $overrides));
    }

    /** @test */
    public function hrd_can_finalize_and_submit_promotion_eligibility_list_to_board(): void
    {
        $hrdRole = $this->createRole('HRD', 'HRD');
        $hrdUser = User::create([
            'email' => 'hrd@test.ncs.gov.ng',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->attachRole($hrdUser, $hrdRole);

        $officer = $this->createOfficer();

        $list = PromotionEligibilityList::create([
            'year' => (int) date('Y'),
            'generated_by' => $hrdUser->id,
            'status' => 'DRAFT',
        ]);

        PromotionEligibilityListItem::create([
            'eligibility_list_id' => $list->id,
            'officer_id' => $officer->id,
            'serial_number' => 1,
            'current_rank' => $officer->substantive_rank,
            'years_in_rank' => 3,
            'date_of_first_appointment' => $officer->date_of_first_appointment,
            'date_of_present_appointment' => $officer->date_of_present_appointment,
            'state' => $officer->state_of_origin,
            'date_of_birth' => $officer->date_of_birth,
        ]);

        $this->actingAs($hrdUser)
            ->post(route('hrd.promotion-eligibility.finalize', $list->id))
            ->assertSessionHas('success');

        $this->assertSame('FINALIZED', $list->fresh()->status);

        $this->actingAs($hrdUser)
            ->post(route('hrd.promotion-eligibility.submit-to-board', $list->id))
            ->assertSessionHas('success');

        $this->assertSame('SUBMITTED_TO_BOARD', $list->fresh()->status);
    }

    /** @test */
    public function board_can_bulk_approve_submitted_list_and_officers_are_promoted(): void
    {
        $boardRole = $this->createRole('Board', 'BOARD');
        $boardUser = User::create([
            'email' => 'board@test.ncs.gov.ng',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->attachRole($boardUser, $boardRole);

        $officer = $this->createOfficer([
            'substantive_rank' => 'SC',
            'date_of_present_appointment' => now()->subYears(4),
        ]);

        $list = PromotionEligibilityList::create([
            'year' => (int) date('Y'),
            'generated_by' => $boardUser->id,
            'status' => 'SUBMITTED_TO_BOARD',
        ]);

        $item = PromotionEligibilityListItem::create([
            'eligibility_list_id' => $list->id,
            'officer_id' => $officer->id,
            'serial_number' => 1,
            'current_rank' => $officer->substantive_rank,
            'years_in_rank' => 4,
            'date_of_first_appointment' => $officer->date_of_first_appointment,
            'date_of_present_appointment' => $officer->date_of_present_appointment,
            'state' => $officer->state_of_origin,
            'date_of_birth' => $officer->date_of_birth,
        ]);

        $promotionDate = now()->toDateString();

        $this->actingAs($boardUser)
            ->post(route('board.promotions.bulk-approve', $list->id), [
                'promotion_date' => $promotionDate,
                'board_meeting_date' => $promotionDate,
                'notes' => 'Batch approval',
                'selected_items' => [$item->id],
            ])
            ->assertSessionHas('success');

        $list->refresh();
        $this->assertSame('FINALIZED', $list->status);

        $promotion = Promotion::where('eligibility_list_item_id', $item->id)->first();
        $this->assertNotNull($promotion);
        $this->assertTrue((bool) $promotion->approved_by_board);
        $this->assertSame('SC', $promotion->from_rank);
        $this->assertSame('CSC', $promotion->to_rank); // SC -> CSC

        $officer->refresh();
        $this->assertSame('CSC', $officer->substantive_rank);
        $this->assertSame($promotionDate, $officer->date_of_present_appointment?->format('Y-m-d'));
    }

    /** @test */
    public function api_promotion_approve_updates_promotion_and_officer_rank_using_db_schema(): void
    {
        $boardRole = $this->createRole('Board', 'BOARD');
        $boardUser = User::create([
            'email' => 'board.api@test.ncs.gov.ng',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->attachRole($boardUser, $boardRole);

        $officer = $this->createOfficer(['substantive_rank' => 'SC']);

        $promotion = Promotion::create([
            'officer_id' => $officer->id,
            'from_rank' => 'SC',
            'to_rank' => 'CSC',
            'promotion_date' => now()->toDateString(),
            'approved_by_board' => false,
        ]);

        $payload = [
            'to_rank' => 'CSC',
            'promotion_date' => now()->toDateString(),
            'board_meeting_date' => now()->toDateString(),
            'notes' => 'API approval',
        ];

        $res = $this->actingAs($boardUser, 'sanctum')
            ->postJson("/api/v1/promotions/{$promotion->id}/approve", $payload);

        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $res->assertJsonPath('data.approved_by_board', true);
        $res->assertJsonPath('data.to_rank', 'CSC');

        $promotion->refresh();
        $this->assertTrue((bool) $promotion->approved_by_board);

        $officer->refresh();
        $this->assertSame('CSC', $officer->substantive_rank);
    }
}

