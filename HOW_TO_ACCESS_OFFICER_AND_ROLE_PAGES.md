# How to Access Officer and Role Pages

## Overview

If you are an officer with a role (e.g., Establishment role), you can access **BOTH**:
1. **Officer Pages** - All standard officer features (profile, emoluments, leave applications, etc.)
2. **Role Pages** - Role-specific features (e.g., Establishment dashboard, new recruits, service numbers)

## How It Works

### When You Login

When you log in with the Establishment role (or any other role), the system:

1. **Redirects you to your role dashboard** (e.g., Establishment Dashboard)
   - This is your primary dashboard based on your role priority
   - URL: `/establishment/dashboard`

2. **Shows you TWO menu sections in the sidebar:**
   - **Establishment Menu** (or your role menu) - Your role-specific features
   - **Officer Menu** - Your officer features

### Accessing Officer Pages

You can access officer pages in **three ways**:

#### Method 1: Via Sidebar Menu
- Look for the **"Officer Menu"** section in the sidebar
- Click on any officer menu item (e.g., "Officer Dashboard", "My Profile", "Emoluments", etc.)

#### Method 2: Direct URL Access
You can directly access officer routes:
- Officer Dashboard: `/officer/dashboard`
- My Profile: `/officer/profile`
- My Emoluments: `/officer/emoluments`
- Leave Applications: `/officer/leave-applications`
- Pass Applications: `/officer/pass-applications`
- APER Forms: `/officer/aper-forms`
- And all other officer routes...

#### Method 3: Via Search Modal
- Click the search icon in the sidebar
- Search for officer features (e.g., "My Profile", "Emoluments")

### Accessing Role Pages

You can access role pages in **three ways**:

#### Method 1: Via Sidebar Menu
- Look for the **"Establishment Menu"** (or your role menu) section in the sidebar
- Click on any role menu item (e.g., "Dashboard", "Service Numbers", "New Intakes", etc.)

#### Method 2: Direct URL Access
You can directly access role routes:
- Establishment Dashboard: `/establishment/dashboard`
- Service Numbers: `/establishment/service-numbers`
- New Intakes: `/establishment/new-recruits`
- Training Results: `/establishment/training-results`
- And all other establishment routes...

#### Method 3: Via Search Modal
- Click the search icon in the sidebar
- Search for role features (e.g., "Service Numbers", "New Intakes")

## Example: Officer with Establishment Role

### What You Can Do

**As an Officer:**
- ✅ View and update your profile
- ✅ Raise and view emoluments
- ✅ Apply for leave and pass
- ✅ Submit APER forms
- ✅ View course nominations
- ✅ Manage account changes
- ✅ Manage next of kin
- ✅ Access all officer features

**As Establishment Role:**
- ✅ Manage new recruits
- ✅ Allocate service numbers
- ✅ View training results
- ✅ Initiate onboarding
- ✅ Access all establishment features

**Both Simultaneously:**
- ✅ You can switch between officer and role features seamlessly
- ✅ No need to log out or change accounts
- ✅ Both sets of features are always accessible

### Navigation Flow

1. **Login** → Redirected to `/establishment/dashboard`
2. **Click "Officer Dashboard"** in Officer Menu → Go to `/officer/dashboard`
3. **Click "Dashboard"** in Establishment Menu → Go back to `/establishment/dashboard`
4. **Click "My Profile"** in Officer Menu → Go to `/officer/profile`
5. **Click "Service Numbers"** in Establishment Menu → Go to `/establishment/service-numbers`

## Sidebar Structure

When you have both officer status and a role, your sidebar will show:

```
┌─────────────────────────┐
│   ESTABLISHMENT MENU    │  ← Your role menu
├─────────────────────────┤
│ • Dashboard             │
│ • Service Numbers       │
│ • New Intakes           │
│ • Training Results      │
└─────────────────────────┘

┌─────────────────────────┐
│     OFFICER MENU        │  ← Your officer menu
├─────────────────────────┤
│ • Officer Dashboard     │
│ • Emoluments            │
│ • Applications          │
│ • Course Nominations    │
│ • My Profile            │
│ • APER Forms            │
│ • Settings              │
└─────────────────────────┘
```

## Important Notes

1. **No Conflicts**: The system is designed so there are no conflicts between officer and role access
2. **Always Accessible**: Both sets of features are always accessible - you don't need to switch accounts
3. **Unified Authorization**: The system checks both your officer status AND your role when needed
4. **Dashboard Priority**: Your primary dashboard is based on your highest priority role, but you can always access your officer dashboard

## Troubleshooting

### I can't see the Officer Menu
- Make sure you have completed onboarding (profile picture uploaded)
- Check that you have an officer record associated with your user account
- Contact HRD if you believe you should have officer access

### I can't access officer routes
- Try accessing directly via URL: `/officer/dashboard`
- Check that you're logged in
- Contact HRD if you believe you should have officer access

### I can't access role routes
- Check that your role is active (contact HRD if needed)
- Try accessing directly via URL: `/establishment/dashboard` (replace with your role route)
- Make sure you're logged in with the correct account

## Quick Reference: Common Routes

### Officer Routes
- Dashboard: `/officer/dashboard`
- Profile: `/officer/profile`
- Emoluments: `/officer/emoluments`
- Leave Applications: `/officer/leave-applications`
- Pass Applications: `/officer/pass-applications`
- APER Forms: `/officer/aper-forms`
- Settings: `/officer/settings`

### Establishment Routes (Example)
- Dashboard: `/establishment/dashboard`
- Service Numbers: `/establishment/service-numbers`
- New Intakes: `/establishment/new-recruits`
- Training Results: `/establishment/training-results`

Replace "establishment" with your role name for other roles.





