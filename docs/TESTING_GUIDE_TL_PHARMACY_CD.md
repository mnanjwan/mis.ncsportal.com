# Testing Guide: T&L, Transport, Pharmacy & CD Updates

This guide covers how to test all updates made for Transport officers, CD roster approval, and pharmacy expired drugs.

---

## 1. Display Rank with (T) for Transport Officers

Officers with unit = Transport show their rank with a **(T)** suffix (e.g., "ASC II (T)").

### Setup
- Ensure at least one officer has `unit` = `Transport` (via HRD edit or database).

### Tests
| Location | How to Test |
|----------|-------------|
| Officer profile | Go to officer profile → confirm rank shows "(T)". |
| HRD officer list | HRD → Officers → confirm Transport officers show rank with (T). |
| HRD officer show | Open any officer with unit Transport → rank shows (T). |
| Roster views | View a roster that includes a Transport officer → rank shows (T). |
| API | Call `/api/v1/officers` → response includes `display_rank` for each officer. |
| Onboarding preview | Complete recruit onboarding with unit Transport → preview shows (T). |

---

## 2. Officer Creation – Unit Options & Assign to Transport

### Establishment New Recruits
1. Go to **Establishment → New Recruits → Create**.
2. In **Unit** dropdown, verify options: **General Duty (GD)**, **Support Services (SS)**, **Transport**.
3. Select **Support Services (SS)** → an “Assign to Transport” checkbox should appear.
4. Check “Assign to Transport” and save → officer unit should be stored as **Transport**.
5. Create another recruit with **Transport** selected directly (no checkbox).
6. Confirm both officers appear with rank (T) where applicable.

### HRD Officer Edit
1. Go to **HRD → Officers → [select officer] → Edit**.
2. In **Unit** dropdown, verify same options: GD, SS, Transport.
3. Select **Support Services (SS)** → “Assign to Transport” checkbox appears.
4. Check it → unit becomes Transport; save.
5. Reload page and confirm unit and rank display are correct.

---

## 3. CD (Fleet CD) Roster Approval for Transport Officers

Rosters that include Transport officers must be approved by CD before Area Controller or DC Admin can approve.

### Setup
- User with **CD** role assigned to a command.
- User with **Staff Officer** role for the same command.
- User with **Area Controller** or **DC Admin** role for the same command.
- At least one officer with unit **Transport** in that command.

### Tests

#### 3a. Staff Officer creates and submits roster with Transport officer
1. Log in as Staff Officer.
2. Create a roster that includes at least one Transport officer.
3. Submit the roster.
4. Roster status should be **SUBMITTED**.

#### 3b. CD approves roster
1. Log in as CD.
2. Go to **Fleet** → **Roster Approvals** (or `/fleet/roster/cd`).
3. Confirm the roster appears in the list.
4. Click **Review & Approve**.
5. Approve the roster.
6. Success message: “Roster approved. Area Controller or DC Admin can now give final approval.”

#### 3c. Area Controller / DC Admin – CD required
1. Log in as Area Controller or DC Admin.
2. Go to **Duty Rosters** (pending approval).
3. Open the roster with Transport officers.
4. If CD has **not** approved:
   - Warning: “CD (Fleet CD) approval required”.
   - Approve button is disabled.
5. After CD approval:
   - Approve button is enabled.
6. Click **Approve** → roster should be fully approved.

#### 3d. Roster without Transport officers
1. Create and submit a roster with **no** Transport officers.
2. CD should **not** see it in Roster Approvals.
3. Area Controller / DC Admin can approve it without CD approval.

---

## 4. CD Internal Staff Orders (Transport Officers)

CD handles internal posting for **Transport officers** within the command, mirroring the Staff Officer flow for normal officers. Staff Officer creates internal staff orders for non-Transport officers; CD creates them for Transport officers only.

### Setup
- User with **CD** role assigned to a command.
- At least one officer with unit **Transport** in that command.
- Command has active duty roster(s) with units.

### Tests

#### 4a. CD creates internal staff order for Transport officer
1. Log in as CD.
2. Go to **Fleet** → **Internal Staff Orders (Transport)** (or `/fleet/internal-staff-orders`).
3. Click **Create Internal Staff Order**.
4. Officer dropdown should list **only Transport officers** in the command.
5. Select a Transport officer, target unit, target role.
6. Save as draft → order created.

#### 4b. CD submits for DC Admin approval
1. Open a DRAFT internal staff order.
2. Click **Submit for Approval**.
3. Order status becomes PENDING_APPROVAL.
4. DC Admin can approve or reject (same as Staff Officer orders).

#### 4c. Staff Officer restricted from Transport officers
1. Log in as Staff Officer.
2. Go to **Staff Officer** → **Internal Staff Orders** → **Create**.
3. Officer dropdown should **exclude** Transport officers.
4. If a Transport officer is somehow selected and form submitted, error: "Transport officers are posted by CD. Use Fleet > Internal Staff Orders (Transport)."

#### 4d. DC Admin approval (same flow)
1. DC Admin receives both Staff Officer (non-Transport) and CD (Transport) internal staff orders.
2. Approve a CD-created order → roster assignments updated as usual.

---

## 5. Pharmacy Expired Drug Records

Expired pharmacy stock is moved from `pharmacy_stocks` into `pharmacy_expired_drug_records` by a scheduled command.

### Setup
- Pharmacy data with at least one stock row where `expiry_date` < today.

### Tests

#### 4a. Run command manually
```bash
php artisan pharmacy:move-expired-stock
```
- Expected: “Moved X expired stock record(s) to pharmacy_expired_drug_records.”
- In DB: expired rows removed from `pharmacy_stocks`, present in `pharmacy_expired_drug_records`.

#### 4b. Scheduled run
- Command is scheduled to run daily at 00:05.
- With scheduler running: `php artisan schedule:work` (or cron equivalent).
- After midnight, expired stock should be moved.

---

## 6. Officer Edit Form – Select Dropdowns

The officer edit page uses searchable select dropdowns for Sex, State, Zone, Rank, Unit, etc.

### Tests
1. Go to **HRD → Officers → [select officer] → Edit**.
2. For each select:
   - Click the trigger (e.g. Sex, State of Origin, Substantive Rank, Unit).
   - Dropdown should open and show options.
   - Click an option → value should update and dropdown close.
   - Search (where available) should filter options.
3. If dropdowns do not open or options do not respond to clicks, check the browser console (F12) for JavaScript errors and report them.

---

## Quick Reference: Key Routes

| Feature | Route |
|---------|--------|
| HRD officer edit | `/hrd/officers/{id}/edit` |
| Establishment new recruits | `/establishment/new-recruits` |
| CD roster approvals | `/fleet/roster/cd` |
| CD internal staff orders (Transport) | `/fleet/internal-staff-orders` |
| Area Controller rosters | `/area-controller/roster` |
| DC Admin rosters | `/dc-admin/roster` |
| Fleet CD dashboard | `/fleet/dashboard/cd` |

---

## Troubleshooting

- **Display rank (T) not showing**: Confirm officer `unit` is exactly `Transport` (case-sensitive).
- **CD roster approval not appearing**: Ensure the roster contains at least one officer with `unit` = `Transport`.
- **Pharmacy command fails**: Ensure `pharmacy_stocks` and `pharmacy_expired_drug_records` tables exist; run migrations if needed.
- **Select dropdowns not opening**: Check browser console for errors; verify JavaScript loads; hard refresh (Ctrl+Shift+R).
