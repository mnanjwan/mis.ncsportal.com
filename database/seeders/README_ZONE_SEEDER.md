# Zone and Command Seeder Documentation

## Overview
The `ZoneAndCommandSeeder` creates a complete organizational structure with all zones, commands, and test users for the NCS Employee Portal.

## What Gets Created

### Zones (5 Total)
1. **HEADQUARTERS** - Nigeria Customs Service Headquarters
2. **Zone A HQ** - Zone A Headquarters
3. **Zone B HQTRS** - Zone B Headquarters
4. **Zone C** - Zone C
5. **Zone D** - Zone D

### Commands (64 Total)

#### HEADQUARTERS (9 commands)
- CGC OFFICE
- FATS-HQTRS
- SR&P-HQTRS
- HRD-HQTRS
- E I & I-HQTRS
- EXCISE & FTZ-HQTRS
- TRADOC
- LEGAL UNIT
- ICT-MOD-HQTRS

#### Zone A (18 commands)
- APAPA
- TCIP
- MMIA
- MMAC
- KLTC
- LAGOS INDUSTRIAL
- SEME
- OGUN I
- OGUN II
- OYO - OSUN
- ONDO EKITI
- PTML
- PCA Zone A
- LEKKI FREE TRADE ZONE
- LILYPOND EXPORT COMMAND
- WMC
- IKORODU
- FOU A

#### Zone B (11 commands)
- KADUNA
- KANO JIGAWA
- SOKOTO ZAMFARA
- NIGER KOGI
- FCT
- KWARA
- KEBBI
- NWM
- PT NA BE
- PCA Zone B
- FOU B

#### Zone C (9 commands)
- AN EB EN
- IMO ABIA
- PH I BAYELSA
- PH II ONNE
- EDO DELTA
- CR AK
- EMC
- PCA Zone C
- FOU C

#### Zone D (5 commands)
- BA GM
- AD TR
- BN YB
- FOU D
- PCA Zone D

### Test Users Created

#### HRD Admin
- **Email:** `hrd@ncs.gov.ng`
- **Password:** `password123`
- **Role:** HRD
- **Access:** Full system access

#### Zone Coordinators (4 users)
1. **Zone A Coordinator**
   - **Email:** `zonecoord.a@ncs.gov.ng`
   - **Password:** `password123`
   - **Role:** Zone Coordinator
   - **Assigned Command:** APAPA
   - **Can post:** Officers GL 07 and below within Zone A

2. **Zone B Coordinator**
   - **Email:** `zonecoord.b@ncs.gov.ng`
   - **Password:** `password123`
   - **Role:** Zone Coordinator
   - **Assigned Command:** KADUNA
   - **Can post:** Officers GL 07 and below within Zone B

3. **Zone C Coordinator**
   - **Email:** `zonecoord.c@ncs.gov.ng`
   - **Password:** `password123`
   - **Role:** Zone Coordinator
   - **Assigned Command:** PH I BAYELSA
   - **Can post:** Officers GL 07 and below within Zone C

4. **Zone D Coordinator**
   - **Email:** `zonecoord.d@ncs.gov.ng`
   - **Password:** `password123`
   - **Role:** Zone Coordinator
   - **Assigned Command:** BA GM
   - **Can post:** Officers GL 07 and below within Zone D

#### Staff Officer
- **Email:** `staff.apapa@ncs.gov.ng`
- **Password:** `password123`
- **Role:** Staff Officer
- **Assigned Command:** APAPA

#### Test Officers (4 users)
1. **Officer GL07** (Can be posted by Zone Coordinator)
   - **Email:** `officer.gl07@ncs.gov.ng`
   - **Password:** `password123`
   - **Service Number:** 50001
   - **Grade Level:** GL07
   - **Command:** APAPA

2. **Officer GL06** (Can be posted by Zone Coordinator)
   - **Email:** `officer.gl06@ncs.gov.ng`
   - **Password:** `password123`
   - **Service Number:** 50002
   - **Grade Level:** GL06
   - **Command:** APAPA

3. **Officer GL08** (Cannot be posted by Zone Coordinator - requires HRD)
   - **Email:** `officer.gl08@ncs.gov.ng`
   - **Password:** `password123`
   - **Service Number:** 50003
   - **Grade Level:** GL08
   - **Command:** KADUNA

4. **Officer GL05** (Can be posted by Zone Coordinator)
   - **Email:** `officer.gl05@ncs.gov.ng`
   - **Password:** `password123`
   - **Service Number:** 50004
   - **Grade Level:** GL05
   - **Command:** PH I BAYELSA

## Usage

### Run the Seeder
```bash
php artisan db:seed --class=ZoneAndCommandSeeder
```

### Fresh Migration with Seeder
```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Run all migrations
3. Seed roles, leave types, zones, commands, and test users

### Reset Database Completely
```bash
php artisan migrate:fresh
php artisan db:seed
```

## Testing Scenarios

### Test Zone Coordinator Posting Restrictions

1. **Login as Zone A Coordinator** (`zonecoord.a@ncs.gov.ng`)
   - ✅ Should be able to post Officer GL07 (GL07, same zone)
   - ✅ Should be able to post Officer GL06 (GL06, same zone)
   - ❌ Should NOT be able to post Officer GL08 (GL08 - above GL07)
   - ❌ Should NOT be able to post to Zone B commands (different zone)

2. **Login as HRD** (`hrd@ncs.gov.ng`)
   - ✅ Should be able to post any officer to any command
   - ✅ Should be able to post across zones

### Test Zone and Command Management

1. **Login as HRD** (`hrd@ncs.gov.ng`)
   - Navigate to Settings → Zones
   - View all 5 zones
   - Create/edit zones
   - Navigate to Settings → Commands
   - View all 64 commands
   - Create/edit commands (must assign to zone)

## Notes

- All commands are automatically assigned to their respective zones
- Zone coordinators are assigned to a command within their zone
- Test officers have different grade levels for testing posting restrictions
- All passwords are set to `password123` for easy testing
- The seeder uses `firstOrCreate` to avoid duplicates on re-runs

