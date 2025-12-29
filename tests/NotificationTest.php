<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase;
use App\Models\User;
use App\Models\Officer;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\ManningRequest;
use App\Models\StaffOrder;
use App\Models\Notification;
use App\Models\Command;
use App\Models\Role;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test leave application approval notification
     */
    public function test_leave_application_approval_notification()
    {
        Queue::fake();
        
        // Create test data
        $officer = Officer::factory()->create();
        $user = User::factory()->create(['email' => $officer->email]);
        $officer->update(['user_id' => $user->id]);
        
        $leaveApplication = LeaveApplication::factory()->create([
            'officer_id' => $officer->id,
            'status' => 'PENDING',
        ]);
        
        // Test notification service
        $notificationService = app(NotificationService::class);
        $notification = $notificationService->notifyLeaveApplicationApproved($leaveApplication);
        
        // Assert notification was created
        $this->assertNotNull($notification);
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('leave_application_approved', $notification->notification_type);
        $this->assertFalse($notification->is_read);
        
        // Assert email job was queued
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class);
    }

    /**
     * Test leave application rejection notification
     */
    public function test_leave_application_rejection_notification()
    {
        Queue::fake();
        
        $officer = Officer::factory()->create();
        $user = User::factory()->create(['email' => $officer->email]);
        $officer->update(['user_id' => $user->id]);
        
        $leaveApplication = LeaveApplication::factory()->create([
            'officer_id' => $officer->id,
            'status' => 'PENDING',
        ]);
        
        $notificationService = app(NotificationService::class);
        $notification = $notificationService->notifyLeaveApplicationRejected($leaveApplication, 'Insufficient leave balance');
        
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Insufficient leave balance', $notification->message);
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class);
    }

    /**
     * Test pass application approval notification
     */
    public function test_pass_application_approval_notification()
    {
        Queue::fake();
        
        $officer = Officer::factory()->create();
        $user = User::factory()->create(['email' => $officer->email]);
        $officer->update(['user_id' => $user->id]);
        
        $passApplication = PassApplication::factory()->create([
            'officer_id' => $officer->id,
            'status' => 'PENDING',
        ]);
        
        $notificationService = app(NotificationService::class);
        $notification = $notificationService->notifyPassApplicationApproved($passApplication);
        
        $this->assertNotNull($notification);
        $this->assertEquals('pass_application_approved', $notification->notification_type);
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class);
    }

    /**
     * Test manning request approval notification
     */
    public function test_manning_request_approval_notification()
    {
        Queue::fake();
        
        $staffOfficer = User::factory()->create();
        $command = Command::factory()->create();
        
        $manningRequest = ManningRequest::factory()->create([
            'command_id' => $command->id,
            'requested_by' => $staffOfficer->id,
            'status' => 'SUBMITTED',
        ]);
        
        $notificationService = app(NotificationService::class);
        $notification = $notificationService->notifyManningRequestApproved($manningRequest);
        
        $this->assertNotNull($notification);
        $this->assertEquals($staffOfficer->id, $notification->user_id);
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class);
    }

    /**
     * Test staff order creation notification
     */
    public function test_staff_order_creation_notification()
    {
        Queue::fake();
        
        $officer = Officer::factory()->create();
        $user = User::factory()->create(['email' => $officer->email]);
        $officer->update(['user_id' => $user->id]);
        
        $fromCommand = Command::factory()->create();
        $toCommand = Command::factory()->create();
        
        $staffOrder = StaffOrder::factory()->create([
            'officer_id' => $officer->id,
            'from_command_id' => $fromCommand->id,
            'to_command_id' => $toCommand->id,
            'order_number' => 'SO-2024-1219-001',
        ]);
        
        $notificationService = app(NotificationService::class);
        $notifications = $notificationService->notifyStaffOrderCreated($staffOrder);
        
        $this->assertNotEmpty($notifications);
        $this->assertCount(1, $notifications);
        $this->assertEquals($user->id, $notifications[0]->user_id);
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class);
    }

    /**
     * Test role assignment notification
     */
    public function test_role_assignment_notification()
    {
        Queue::fake();
        
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Staff Officer']);
        $command = Command::factory()->create();
        
        $notificationService = app(NotificationService::class);
        $notification = $notificationService->notifyRoleAssigned($user, $role->name, $command->name);
        
        $this->assertNotNull($notification);
        $this->assertEquals('role_assigned', $notification->notification_type);
        $this->assertStringContainsString('Staff Officer', $notification->message);
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class);
    }

    /**
     * Test notification appears in database
     */
    public function test_notification_stored_in_database()
    {
        Queue::fake();
        
        $user = User::factory()->create();
        $notificationService = app(NotificationService::class);
        
        $notification = $notificationService->notify(
            $user,
            'test_notification',
            'Test Title',
            'Test message content',
            'test_entity',
            123
        );
        
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notification_type' => 'test_notification',
            'title' => 'Test Title',
            'entity_type' => 'test_entity',
            'entity_id' => 123,
        ]);
    }

    /**
     * Test notification by role
     */
    public function test_notification_by_role()
    {
        Queue::fake();
        
        $role = Role::factory()->create(['name' => 'HRD']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $user1->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
        $user2->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
        
        $notificationService = app(NotificationService::class);
        $notifications = $notificationService->notifyByRole(
            'HRD',
            'test_type',
            'Test Title',
            'Test message'
        );
        
        $this->assertCount(2, $notifications);
        Queue::assertPushed(\App\Jobs\SendNotificationEmailJob::class, 2);
    }
}






