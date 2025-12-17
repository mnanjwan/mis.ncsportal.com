# Role Assignment Guide

## Overview

This guide explains which roles require command assignments and which are independent (system-wide) roles in the NCS Employee Portal.

## Command-Based Roles

These roles **MUST** be assigned to a specific command. Users with these roles can only access data and perform actions within their assigned command.

### 1. **Assessor**
- **Purpose**: First-level review of emolument submissions
- **Command Required**: ✅ Yes
- **Access Level**: Subordinate officers only within assigned command
- **Functions**: Review and assess emoluments from officers in their command

### 2. **Validator**
- **Purpose**: Final verification before payment processing
- **Command Required**: ✅ Yes
- **Access Level**: Command-level validation
- **Functions**: Validate emoluments that have been assessed by Assessors

### 3. **Staff Officer**
- **Purpose**: Command-level administration and coordination
- **Command Required**: ✅ Yes
- **Access Level**: Command-level operations
- **Functions**: 
  - Prepare duty rosters
  - Process leave and pass applications
  - Manage manning level requests
  - Maintain command nominal roll

### 4. **Area Controller (Comptroller)**
- **Purpose**: Command oversight and final validation authority
- **Command Required**: ✅ Yes
- **Access Level**: Area command with validation authority
- **Functions**:
  - Approve duty rosters
  - Approve manning level requests
  - Validate emoluments
  - Indicate deceased officers

### 5. **DC Admin (Deputy Comptroller Administration)**
- **Purpose**: Day-to-day approval of routine administrative requests
- **Command Required**: ✅ Yes
- **Access Level**: Command-level approval authority
- **Functions**:
  - Approve leave applications
  - Approve pass requests
  - Process urgent administrative requests

### 6. **Building Unit**
- **Purpose**: Officer quarters allocation and management
- **Command Required**: ✅ Yes
- **Access Level**: Quarters and accommodation records at command level
- **Functions**:
  - Allocate quarters to officers
  - Update quartered status
  - Maintain quarters occupancy database

---

## Independent Roles (No Command Required)

These roles have system-wide access and do **NOT** require a command assignment.

### 1. **HRD (Human Resources Department)**
- **Purpose**: Overall system management and strategic HR operations
- **Command Required**: ❌ No
- **Access Level**: Full system access across all commands
- **Functions**: System administration, onboarding serving officers, generating orders, managing timelines, etc.

### 2. **Establishment**
- **Purpose**: New officer registration and service number management
- **Command Required**: ❌ No
- **Access Level**: New recruitment and service numbers (system-wide)
- **Functions**: Onboard new officers, allocate service numbers, process new recruit documentation

### 3. **Accounts**
- **Purpose**: Payment processing and financial management
- **Command Required**: ❌ No
- **Access Level**: Financial records system-wide
- **Functions**: Process payments, generate payment lists, handle financial transactions

### 4. **Board (Promotion Board)**
- **Purpose**: Management of promotions and rank changes
- **Command Required**: ❌ No
- **Access Level**: Promotion and career records (system-wide)
- **Functions**: Review promotion eligibility, conduct promotion exercises, update ranks

### 5. **Welfare**
- **Purpose**: Deceased officer management and welfare support
- **Command Required**: ❌ No
- **Access Level**: Welfare and benefits records (system-wide)
- **Functions**: Validate deceased officers, generate deceased officer data, process welfare claims

### 6. **Officer**
- **Purpose**: Personal record management and service applications
- **Command Required**: ❌ No
- **Access Level**: Personal records only
- **Functions**: Complete onboarding, apply for leave/pass, raise emoluments, update personal information

---

## How HRD Assigns Roles

### Accessing Role Assignment Management

1. Log in as HRD user
2. Navigate to **"Role Assignments"** in the sidebar menu
3. Click **"Assign Role"** button to assign a new role

### Assigning a Role

1. **Select Officer**: Choose the officer from the dropdown list
2. **Select Role**: Choose the role to assign
   - If the role requires a command, the command field will become required
   - If the role is independent, the command field will be optional/hidden
3. **Select Command** (if required): Choose the command for command-based roles
4. **Submit**: The system will:
   - Create a user account if the officer doesn't have one
   - Assign the role with the command (if applicable)
   - Link the user to the officer record

### Managing Existing Assignments

- **View All Assignments**: See all users with their roles and command assignments
- **Edit Assignment**: Update command assignment or activate/deactivate a role
- **Remove Role**: Deactivate a role assignment (soft delete)

### Important Notes

- A user can have multiple roles
- Command-based roles cannot be assigned without a command
- If an officer doesn't have a user account, one will be created automatically
- Role assignments can be activated or deactivated without deleting them
- The system tracks who assigned the role and when (`assigned_by`, `assigned_at`)

---

## Error Messages

### "No command assigned. Please contact HRD to assign you to a command"

This error appears when:
- A user with a command-based role (Assessor, Validator, Staff Officer, etc.) tries to access their dashboard
- The role assignment doesn't have a `command_id` in the `user_roles` pivot table

**Solution**: HRD must assign a command to the user's role through the Role Assignments management page.

---

## Database Structure

Role assignments are stored in the `user_roles` pivot table with the following structure:

- `user_id`: Foreign key to users table
- `role_id`: Foreign key to roles table
- `command_id`: Foreign key to commands table (nullable for independent roles)
- `assigned_by`: User ID who assigned the role
- `assigned_at`: Timestamp when role was assigned
- `is_active`: Boolean flag for active/inactive status
- `created_at`, `updated_at`: Timestamps

---

## Best Practices

1. **Always assign commands** for command-based roles during initial assignment
2. **Verify command assignments** before users start using the system
3. **Use deactivation** instead of deletion when removing roles (preserves audit trail)
4. **Document role changes** for audit purposes
5. **Regularly review** role assignments to ensure they're still valid

