# Role Seeding Summary

## Total Roles Seeded: **18 Roles**

All 18 roles are created by `RoleSeeder`, but not all have users assigned.

## Roles WITH Users Assigned (16 roles):

### System-Wide Roles (9):
1. **HRD** - `hrd@ncs.gov.ng`
2. **CGC** - `cgc@ncs.gov.ng`
3. **ESTABLISHMENT** - `establishment@ncs.gov.ng`
4. **ACCOUNTS** - `accounts@ncs.gov.ng`
5. **BOARD** - `board@ncs.gov.ng`
6. **WELFARE** - `welfare@ncs.gov.ng`
7. **TRADOC** - `tradoc@ncs.gov.ng`
8. **ICT** - `ict@ncs.gov.ng`
9. **INVESTIGATION_UNIT** - `investigation_unit@ncs.gov.ng`

### Command-Level Roles (APAPA) (7):
10. **ADMIN** - `admin.apapa@ncs.gov.ng`
11. **STAFF_OFFICER** - `staff.apapa@ncs.gov.ng`
12. **BUILDING_UNIT** - `building.apapa@ncs.gov.ng`
13. **ASSESSOR** - `assessor.apapa@ncs.gov.ng`
14. **VALIDATOR** - `validator.apapa@ncs.gov.ng`
15. **AREA_CONTROLLER** - `areacontroller.apapa@ncs.gov.ng`
16. **DC_ADMIN** - `dcadmin.apapa@ncs.gov.ng`

## Roles WITHOUT Dedicated Users (2 roles):

17. **OFFICER** - Assigned to individual officers (not a single admin user)
18. **ZONE_COORDINATOR** - Assigned to zone coordinators (created separately)

## Issue Found:
- `TestDataSeeder` was using `User::create()` which fails if users already exist
- This causes only 5-6 roles to show up instead of all 16
- Fixed by changing to `firstOrCreate()`

## Expected Result:
After seeding, you should see **16 role assignments** in the Role Assignments Management interface (all roles except OFFICER and ZONE_COORDINATOR which are assigned individually).

