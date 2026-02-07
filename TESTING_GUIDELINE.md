# Testing Guideline - T&L System Upgrade

This document provides comprehensive testing guidelines for the T&L (Transport & Logistics) System Upgrade. The system has been seeded with 12 test requests across various scenarios.

## Test Users (Roles)

The following test users have been created with their assigned roles. **All passwords are set to `password`**.

| User Email | Assigned Role | Primary Activity |
|------------|---------------|------------------|
| tl_user_1@example.com | CD | View/Create requests at Command level |
| tl_user_2@example.com | Area Controller | Approve/Forward Command requests |
| tl_user_3@example.com | OC Workshop | Create Requisitions (Maintenance) |
| tl_user_4@example.com | Staff Officer T&L | Create Repair/OPE requests |
| tl_user_5@example.com | CC T&L | Inventory Check (Propose) & Vehicle Release |
| tl_user_6@example.com | ACG TS | Approve Requisitions < 300k |
| tl_user_7@example.com | DCG FATS | Approve Requisitions < 500k |
| tl_user_8@example.com | CGC | Approve Requisitions > 500k |

## Verification Scenarios

### 1. New Vehicle Request (The Full Chain)

1. **Login as `tl_user_2@example.com`** (Area Controller).
2. Go to **Fleet > Requests**. You should see a New Vehicle request in **DRAFT** status.
3. **Submit** the request.
4. **Login as `tl_user_5@example.com`** (CC T&L).
5. You should see the request in your **Inbox**.
6. Click **Open**. Use the **CC T&L Action Panel** to select vehicles and click **Propose Selection**.
7. Observe the status changes to **IN_REVIEW (Proposal)**.
8. Follow the chain up (**CGC → DCG FATS → ACG TS**) to approve the proposal.
9. Finally, return to **CC T&L** to **Release** the vehicles.

### 2. Maintenance Requisition (Amount Thresholds)

1. **Login as `tl_user_3@example.com`** (OC Workshop).
2. View existing requisitions.
3. Compare three requests:
   - **₦150,000**: Should finish at **ACG TS**.
   - **₦450,000**: Should pass through **ACG TS** and finish at **DCG FATS**.
   - **₦1,200,000**: Should go all the way to **CGC**.
4. Test the **KIV (Keep In View)** decision as an approver. The request should stay at the current step but status should update to **KIV**.

### 3. Re-allocation

1. **Login as `tl_user_2@example.com`** (Area Controller).
2. Create or find a **Re-allocation** request.
3. **Submit** it.
4. **Login as `tl_user_5@example.com`** (CC T&L).
5. You should be able to directly **Approve & Release** the vehicle selected in the request.

### 4. UI/UX Elements

- Verify that **Amount** and **Vehicle** fields are visible in the request show page.
- Verify that the **Workflow Progress** table correctly highlights the "Current Step".
- Check that **Notes** and **Attachments** (if any) are displayed properly.

## Important Notes

### Email Configuration

The system has been configured with `MAIL_MAILER=log` in your `.env` file to prevent the system from hanging during email attempts. You can check `storage/logs/laravel.log` to see if notification emails are being "sent".

### Running the Seeder

If you need to recreate the test data, run:

```bash
php artisan db:seed --class=FleetTestDataSeeder
```

This will:
- Create all 8 test users with their assigned roles
- Create 10 test vehicles
- Create 6 test requests across different scenarios:
  - New Vehicle Request (Draft)
  - Re-allocation Request (Submitted)
  - Requisition (₦150,000 - Low Amount)
  - Requisition (₦450,000 - Mid Amount)
  - Requisition (₦1,200,000 - High Amount)
  - Repair Request (Submitted)

## Troubleshooting

### Cannot Login

If you cannot login with the test users:

1. Verify the users exist:
   ```bash
   php artisan tinker
   >>> \App\Models\User::where('email', 'like', 'tl_user_%')->get(['email', 'is_active']);
   ```

2. Reset passwords (if needed):
   ```bash
   php artisan tinker
   >>> $user = \App\Models\User::where('email', 'tl_user_1@example.com')->first();
   >>> $user->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
   ```

3. Ensure users are active:
   ```bash
   php artisan tinker
   >>> \App\Models\User::where('email', 'like', 'tl_user_%')->update(['is_active' => true]);
   ```

### Roles Not Assigned

If roles are not showing up:

1. Verify roles exist:
   ```bash
   php artisan tinker
   >>> \App\Models\Role::whereIn('name', ['CD', 'Area Controller', 'OC Workshop', 'Staff Officer T&L', 'CC T&L', 'ACG TS', 'DCG FATS', 'CGC'])->get(['name']);
   ```

2. Re-run the seeder to assign roles:
   ```bash
   php artisan db:seed --class=FleetTestDataSeeder
   ```

## Test Data Summary

- **8 Test Users** with assigned roles
- **10 Test Vehicles** (Toyota Hilux SUVs)
- **6 Test Requests**:
  - 1 New Vehicle Request (Draft)
  - 1 Re-allocation Request (Submitted)
  - 3 Requisitions (₦150k, ₦450k, ₦1.2M)
  - 1 Repair Request (Submitted)

---

**Last Updated**: February 7, 2026
