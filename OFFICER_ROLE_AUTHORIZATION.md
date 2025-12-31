# Officer and Role Authorization System

## Overview

This system allows officers to have roles while ensuring they can perform both officer duties and role-specific duties without conflicts.

## Key Principles

1. **Officer Access**: All officers can access officer-specific features regardless of any roles they may have
2. **Role Access**: Users with specific roles can access role-specific features
3. **Dual Access**: Officers with roles can access BOTH officer features AND role features simultaneously

## Architecture

### User Model Methods

- `isOfficer()`: Check if user is an officer
- `hasRole($roleName)`: Check if user has a specific role
- `hasAnyRole(array $roleNames)`: Check if user has any of the given roles
- `hasRoleOrIsOfficer($roleName)`: Check if user has role OR is an officer
- `hasAnyRoleOrIsOfficer(array $roleNames)`: Check if user has any role OR is an officer
- `canAccessOfficerFeatures()`: Returns true if user is an officer
- `canAccessRoleFeatures($roleName)`: Returns true if user has the role

### Controller Helper Methods

All controllers extend `App\Http\Controllers\Controller` which provides:

- `isOfficer()`: Check if authenticated user is an officer
- `hasRole($roleName)`: Check if authenticated user has a role
- `hasAnyRole(array $roleNames)`: Check if authenticated user has any roles
- `hasRoleOrIsOfficer($roleName)`: Check if user has role OR is an officer
- `hasAnyRoleOrIsOfficer(array $roleNames)`: Check if user has any role OR is an officer
- `authorizeOfficerAccess()`: Throws 403 if user is not an officer
- `authorizeRole($roleName)`: Throws 403 if user doesn't have the role
- `authorizeRoleOrOfficer($roleName)`: Throws 403 if user doesn't have role and is not an officer

## Usage Patterns

### Pattern 1: Officer-Only Features
Use when only officers should access a feature (regardless of roles):

```php
public function myProfile()
{
    $this->authorizeOfficerAccess(); // All officers can access
    
    // ... rest of the method
}
```

### Pattern 2: Role-Only Features
Use when only users with a specific role should access:

```php
public function manageOfficers()
{
    $this->authorizeRole('HRD'); // Only HRD role can access
    
    // ... rest of the method
}
```

### Pattern 3: Officer OR Role Features
Use when both officers and role holders should access:

```php
public function viewAPERForms()
{
    $this->authorizeRoleOrOfficer('Reporting Officer'); 
    // Officers can access, OR users with Reporting Officer role
    
    // ... rest of the method
}
```

### Pattern 4: Conditional Logic
Use when you need different behavior based on officer/role status:

```php
public function listOfficers()
{
    $user = auth()->user();
    
    if ($user->hasRole('Staff Officer')) {
        // Staff Officer logic - filter by command
        $query->where('present_station', $commandId);
    } elseif ($user->isOfficer()) {
        // Regular officer logic
        // Officers can see their own data
    } else {
        // HRD or other role logic
        // Full access
    }
}
```

## Route Protection

### Officer Routes
Officer routes are protected by `auth` middleware only. They don't check for roles because ALL officers should have access:

```php
Route::prefix('officer')->name('officer.')->middleware('onboarding.complete')->group(function () {
    // All officers can access these routes
});
```

### Role Routes
Role routes are protected by `role:RoleName` middleware. Only users with that role can access:

```php
Route::prefix('hrd')->name('hrd.')->middleware('role:HRD')->group(function () {
    // Only users with HRD role can access
});
```

## Important Notes

1. **Officers with roles can access both**: An officer with the "HRD" role can access:
   - All officer routes (because they're an officer)
   - All HRD routes (because they have the HRD role)

2. **No conflicts**: The system is designed so there are no conflicts between officer and role access

3. **Controller checks**: Always use the helper methods from the base Controller class for consistency

4. **Middleware**: Use `EnsureOfficerAccess` middleware if you need to explicitly ensure officer access on a route

## Examples

### Example 1: Officer viewing their own APER form
```php
public function showAPERForm($id)
{
    $user = auth()->user();
    $form = APERForm::findOrFail($id);
    
    // Officer can view their own form
    if ($user->isOfficer() && $form->officer_id === $user->officer->id) {
        return view('aper.show', compact('form'));
    }
    
    // Reporting Officer can view forms they're assigned to
    if ($user->hasRole('Reporting Officer') && $form->reporting_officer_id === $user->id) {
        return view('aper.show', compact('form'));
    }
    
    abort(403);
}
```

### Example 2: Staff Officer managing officers in their command
```php
public function documentOfficer($id)
{
    $user = auth()->user();
    
    // Only Staff Officers can document officers
    if (!$user->hasRole('Staff Officer')) {
        abort(403);
    }
    
    // Get Staff Officer's command
    $commandId = $user->roles()
        ->where('name', 'Staff Officer')
        ->wherePivot('is_active', true)
        ->first()
        ->pivot
        ->command_id;
    
    $officer = Officer::findOrFail($id);
    
    // Verify officer is in Staff Officer's command
    if ($officer->present_station != $commandId) {
        return redirect()->back()->with('error', 'You can only document officers in your command.');
    }
    
    // ... rest of the logic
}
```

## Testing

When testing, ensure:
1. Officers without roles can access officer features
2. Officers with roles can access both officer and role features
3. Users with roles but not officers can access role features
4. Users without roles and not officers cannot access protected features





