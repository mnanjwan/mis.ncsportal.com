# HRD Actions Requiring Notifications

This document lists all HRD actions that should trigger notifications to users or officers.

## 1. Officer Management (OfficerController)

### Actions:
- ✅ **Officer Profile Update** (`update`)
  - **Notify:** Officer (if they have a user account)
  - **Type:** `officer_profile_updated`
  - **Message:** "Your profile has been updated by HRD. Please review the changes."

- ✅ **Officer Status Change** (activate/deactivate)
  - **Notify:** Officer (if they have a user account)
  - **Type:** `officer_status_changed`
  - **Message:** "Your account status has been changed to [ACTIVE/INACTIVE]."

## 2. Leave Applications (LeaveApplicationController)

### Actions:
- ✅ **Leave Application Approval** (`approve`)
  - **Notify:** Officer who applied
  - **Type:** `leave_application_approved`
  - **Message:** "Your leave application from {start_date} to {end_date} has been approved."

- ✅ **Leave Application Rejection** (`reject`)
  - **Notify:** Officer who applied
  - **Type:** `leave_application_rejected`
  - **Message:** "Your leave application has been rejected. Reason: {rejection_reason}"

- ✅ **Leave Application Submitted** (when officer submits)
  - **Notify:** HRD/Area Controller (for approval)
  - **Type:** `leave_application_submitted`
  - **Message:** "New leave application submitted by {officer_name} for {number_of_days} days."

## 3. Pass Applications (PassApplicationController)

### Actions:
- ✅ **Pass Application Approval** (`approve`)
  - **Notify:** Officer who applied
  - **Type:** `pass_application_approved`
  - **Message:** "Your pass application has been approved."

- ✅ **Pass Application Rejection** (`reject`)
  - **Notify:** Officer who applied
  - **Type:** `pass_application_rejected`
  - **Message:** "Your pass application has been rejected. Reason: {rejection_reason}"

- ✅ **Pass Application Submitted** (when officer submits)
  - **Notify:** Area Controller (for approval)
  - **Type:** `pass_application_submitted`
  - **Message:** "New pass application submitted by {officer_name}."

## 4. Manning Requests (ManningRequestController)

### Actions:
- ✅ **Manning Request Approval** (`areaControllerApprove`)
  - **Notify:** Staff Officer who submitted the request
  - **Type:** `manning_request_approved`
  - **Message:** "Your manning request for {command_name} has been approved."

- ✅ **Manning Request Rejection** (`areaControllerReject`)
  - **Notify:** Staff Officer who submitted the request
  - **Type:** `manning_request_rejected`
  - **Message:** "Your manning request has been rejected. Reason: {rejection_reason}"

- ✅ **Manning Request Submitted** (`store`)
  - **Notify:** Area Controller (for approval)
  - **Type:** `manning_request_submitted`
  - **Message:** "New manning request submitted by {command_name} for {rank} positions."

- ✅ **Staff Order Generated** (`hrdGenerateOrder`)
  - **Notify:** 
    - Staff Officer who requested
    - Officers assigned to the order
  - **Type:** `staff_order_generated`
  - **Message:** "A staff order has been generated based on your manning request. Order Number: {order_number}"

## 5. Staff Orders (StaffOrderController)

### Actions:
- ✅ **Staff Order Created** (`store`)
  - **Notify:** 
    - Officer being posted
    - From Command Staff Officer
    - To Command Staff Officer
  - **Type:** `staff_order_created`
  - **Message:** "A new staff order has been created. You are being posted from {from_command} to {to_command}. Order Number: {order_number}"

- ✅ **Staff Order Updated** (`update`)
  - **Notify:** 
    - Officer being posted
    - From Command Staff Officer
    - To Command Staff Officer
  - **Type:** `staff_order_updated`
  - **Message:** "Staff order {order_number} has been updated. Please review the changes."

- ✅ **Staff Order Status Changed** (when status changes to PUBLISHED, CANCELLED, etc.)
  - **Notify:** 
    - Officer being posted
    - From Command Staff Officer
    - To Command Staff Officer
  - **Type:** `staff_order_status_changed`
  - **Message:** "Staff order {order_number} status has been changed to {status}."

## 6. Movement Orders (MovementOrderController)

### Actions:
- ✅ **Movement Order Created** (`store`)
  - **Notify:** 
    - Officers affected by the movement order
    - Staff Officers of affected commands
  - **Type:** `movement_order_created`
  - **Message:** "A new movement order has been published. Order Number: {order_number}. Criteria: {criteria_months_at_station} months at station."

- ✅ **Movement Order Published** (when status changes to PUBLISHED)
  - **Notify:** 
    - Officers affected
    - Staff Officers
  - **Type:** `movement_order_published`
  - **Message:** "Movement order {order_number} has been published and is now active."

## 7. Promotions (PromotionController)

### Actions:
- ✅ **Promotion Eligibility List Generated** (`storeEligibilityList`)
  - **Notify:** Officers included in the eligibility list
  - **Type:** `promotion_eligibility_list_generated`
  - **Message:** "You have been included in the {year} promotion eligibility list."

- ✅ **Promotion Approved** (`approve`)
  - **Notify:** Officer being promoted
  - **Type:** `promotion_approved`
  - **Message:** "Your promotion to {new_rank} has been approved. Effective date: {effective_date}"

- ✅ **Promotion Criteria Updated** (`updateCriteria`)
  - **Notify:** All officers (or affected officers)
  - **Type:** `promotion_criteria_updated`
  - **Message:** "Promotion criteria have been updated. Please review the new requirements."

## 8. Onboarding (OnboardingController)

### Actions:
- ✅ **Onboarding Initiated** (`initiate`, `bulkInitiate`, `csvUpload`)
  - **Notify:** Officer (via email - already implemented)
  - **Type:** `onboarding_initiated`
  - **Message:** "Your onboarding process has been initiated. Please check your email for the onboarding link."

- ✅ **Onboarding Link Resent** (`resendLink`)
  - **Notify:** Officer (via email)
  - **Type:** `onboarding_link_resent`
  - **Message:** "Your onboarding link has been resent. Please check your email."

## 9. Role Assignments (RoleAssignmentController)

### Actions:
- ✅ **Role Assigned** (`store`)
  - **Notify:** User being assigned the role
  - **Type:** `role_assigned`
  - **Message:** "You have been assigned the role of {role_name} for {command_name}."

- ✅ **Role Updated** (`update`)
  - **Notify:** User whose role was updated
  - **Type:** `role_updated`
  - **Message:** "Your role assignment has been updated."

- ✅ **Role Deactivated** (when is_active set to false)
  - **Notify:** User whose role was deactivated
  - **Type:** `role_deactivated`
  - **Message:** "Your role assignment for {role_name} at {command_name} has been deactivated."

## 10. Retirement (RetirementController)

### Actions:
- ✅ **Retirement List Generated** (`generateList`, `store`)
  - **Notify:** Officers included in the retirement list
  - **Type:** `retirement_list_generated`
  - **Message:** "You have been included in the retirement list for {year}. Your retirement date is {retirement_date}."

- ✅ **Retirement List Updated** (when list is modified)
  - **Notify:** Affected officers
  - **Type:** `retirement_list_updated`
  - **Message:** "The retirement list has been updated. Please review your retirement information."

## 11. Courses/Training (CourseController)

### Actions:
- ✅ **Course Created** (`store`)
  - **Notify:** Officers eligible for the course (if applicable)
  - **Type:** `course_created`
  - **Message:** "A new course '{course_name}' has been created. Registration may be available."

- ✅ **Course Assigned to Officer** (when officer is enrolled)
  - **Notify:** Officer
  - **Type:** `course_assigned`
  - **Message:** "You have been assigned to the course '{course_name}'. Start date: {start_date}"

- ✅ **Course Completed** (`markComplete`)
  - **Notify:** Officer
  - **Type:** `course_completed`
  - **Message:** "You have successfully completed the course '{course_name}'."

- ✅ **Course Updated** (`update`)
  - **Notify:** Enrolled officers
  - **Type:** `course_updated`
  - **Message:** "The course '{course_name}' has been updated. Please review the changes."

## 12. Emolument Timeline (EmolumentTimelineController)

### Actions:
- ✅ **Emolument Timeline Created** (`store`)
  - **Notify:** Officers affected by the timeline
  - **Type:** `emolument_timeline_created`
  - **Message:** "A new emolument timeline has been created. Assessment period: {start_date} to {end_date}"

- ✅ **Emolument Timeline Extended** (`extendStore`)
  - **Notify:** Officers affected
  - **Type:** `emolument_timeline_extended`
  - **Message:** "The emolument timeline has been extended. New end date: {new_end_date}"

## 13. Duty Roster (DutyRosterController)

### Actions:
- ✅ **Duty Roster Created/Updated**
  - **Notify:** Officers assigned to duties
  - **Type:** `duty_roster_updated`
  - **Message:** "Your duty roster has been updated. Please review your assigned duties."

## 14. Next of Kin Change Requests (NextOfKinChangeRequestController)

### Actions:
- ✅ **Next of Kin Change Request Approved**
  - **Notify:** Officer who requested
  - **Type:** `next_of_kin_change_approved`
  - **Message:** "Your next of kin change request has been approved."

- ✅ **Next of Kin Change Request Rejected**
  - **Notify:** Officer who requested
  - **Type:** `next_of_kin_change_rejected`
  - **Message:** "Your next of kin change request has been rejected. Reason: {rejection_reason}"

## 15. System Settings (SystemSettingController)

### Actions:
- ✅ **System Settings Updated** (`update`)
  - **Notify:** All users (or specific roles if setting affects them)
  - **Type:** `system_settings_updated`
  - **Message:** "System settings have been updated. Please review any changes that may affect you."

## Summary by Notification Type

### Notifications to Officers:
1. Profile updates
2. Leave/Pass application approvals/rejections
3. Staff order assignments
4. Movement order notifications
5. Promotion notifications
6. Retirement list inclusion
7. Course assignments/completions
8. Role assignments
9. Onboarding initiation
10. Next of kin change request status

### Notifications to Staff Officers:
1. Manning request approvals/rejections
2. Staff order generation
3. Movement order publications

### Notifications to Area Controllers:
1. Leave/Pass application submissions
2. Manning request submissions

### Notifications to HRD:
1. Leave/Pass application submissions (for monitoring)
2. System-wide changes

## Implementation Priority

### High Priority (Critical Workflows):
1. Leave/Pass application approvals/rejections
2. Staff order creation/updates
3. Manning request approvals
4. Onboarding initiation
5. Role assignments

### Medium Priority (Important Updates):
1. Movement order publications
2. Promotion notifications
3. Course assignments
4. Retirement list generation

### Low Priority (Informational):
1. System settings updates
2. Profile updates
3. Course updates

## Notes

- All notifications should be sent both as in-app notifications and emails (via background jobs)
- Notifications should include relevant entity links when possible
- Use the NotificationService for consistent notification handling
- Consider batching notifications for bulk operations to avoid spam











