# Fixing Incorrectly Inactive Officers (Production Runbook)

## What happened

Before the form fix (March 2026), saving the **Edit Officer** form did not include an "Officer active" checkbox. The backend treated the missing value as inactive, so **officers were set to `is_active = false`** whenever HRD edited and saved their record. That made them show as **Inactive** on the Officers list even though they were not deceased, dismissed, or retired.

## How to fix affected officers in production

### 1. List affected officers (dry run)

```bash
# All officers who are inactive but not deceased/dismissed
php artisan officers:fix-inactive-status --dry-run

# Only officers who have a linked user account (more conservative)
php artisan officers:fix-inactive-status --dry-run --with-user-only
```

Review the table. These are the officers that will be set back to **Active** if you run with `--fix`.

### 2. Apply the fix

```bash
# Fix all listed officers (you will be asked to confirm)
php artisan officers:fix-inactive-status --fix

# Or only those with a user account
php artisan officers:fix-inactive-status --fix --with-user-only
```

The command sets `officers.is_active = true` only for officers who are **not** deceased and **not** dismissed. It does not touch user accounts (`users.is_active`).

### 3. Verify

- Open **HRD → Officers** and confirm the previously inactive officers now show **Active**.
- Optionally run `--dry-run` again; it should report "No affected officers found."

---

## How this won’t happen again

1. **Code fix (already in place)**  
   - The **Edit Officer** form now has:
     - An **Officer active** checkbox (controls the Status column on the Officers list).
     - A hidden input so the value is always sent (checked = 1, unchecked = 0).
   - The controller uses `$request->boolean('is_active')`, so the saved status always matches what HRD selects.

2. **Deploy the form/controller changes**  
   - Ensure production is running the version that includes:
     - `resources/views/forms/officer/edit.blade.php` (Officer active + hidden input, and User account active + hidden input).
     - `app/Http/Controllers/OfficerController.php` (validation and `$validated['is_active'] = $request->boolean('is_active');` and user sync).

3. **Process**  
   - HRD should use **Edit Officer → Status Information** to change officer status. The list will only show someone as Inactive if "Officer active" is explicitly unchecked and the form is saved.

---

## Optional: one-off SQL (if you prefer not to use the command)

```sql
-- Preview
SELECT id, service_number, initials, surname, email, user_id, updated_at
FROM officers
WHERE is_active = 0 AND is_deceased = 0 AND dismissed = 0;

-- Fix (run after reviewing the list)
UPDATE officers
SET is_active = 1, updated_at = NOW()
WHERE is_active = 0 AND is_deceased = 0 AND dismissed = 0;
```

Use the same filters as the Artisan command; the command is safer because it logs and supports `--dry-run` and `--with-user-only`.
