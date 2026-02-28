# Document and Image Upload Points

This document lists all locations in the application where documents or images can be uploaded.

## 1. Leave Applications

### View File
- `resources/views/forms/leave/apply.blade.php`

### Upload Details
- **Field Name**: `medical_certificate`
- **File Types**: <span style="color: red;">JPEG, PDF</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Storage Location**: `storage/app/public/certificates/`
- **Controller**: `app/Http/Controllers/LeaveApplicationController.php` (line 108-111)
- **Route**: `leave.store`
- **Form Enctype**: `multipart/form-data`

### Description
Medical certificates or supporting documents for leave applications.

---

## 2. Pass Applications

### View File
- `resources/views/forms/pass/apply.blade.php`

### Upload Details
- **Field Name**: `supporting_documents`
- **File Types**: <span style="color: red;">JPEG, PDF (multiple files allowed)</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB per file)</span>
- **Route**: `pass.store`
- **Form Enctype**: `multipart/form-data`

### Description
Supporting documents for pass applications.

---

## 3. Officer Profile Picture (Onboarding - Step 4)

### View File
- `resources/views/forms/onboarding/step4.blade.php`

### Upload Details
- **Field Name**: `profile_picture_data` (base64 encoded)
- **Input ID**: `onboarding-profile-picture-upload`
- **File Types**: <span style="color: red;">Images (all types)</span>
- **Max File Size**: <span style="color: red;">2MB (final), 5MB (for cropping)</span>
- **Storage**: Base64 encoded in hidden field, processed on backend
- **Route**: `onboarding.submit`
- **Form Enctype**: `multipart/form-data`
- **Special Features**: Image cropper with passport photo aspect ratio (4:5)

### Description
Official passport photo for officer onboarding. Uses CropperJS for image cropping.

---

## 4. Officer Documents (Onboarding - Step 4)

### View File
- `resources/views/forms/onboarding/step4.blade.php`

### Upload Details
- **Field Name**: `documents[]` (multiple files)
- **Input ID**: `documents-input`
- **File Types**: <span style="color: red;">JPEG, JPG, PNG</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB per file)</span>
- **Storage Location**: 
  - Temporary: `storage/app/public/officer-documents-temp/` (DashboardController line 1671)
  - Final: `storage/app/public/officer-documents/` (DashboardController line 1891)
- **Controller**: `app/Http/Controllers/DashboardController.php`
- **Route**: `onboarding.submit`
- **Form Enctype**: `multipart/form-data`

### Description
Multiple documents uploaded during onboarding process (Step 4).

---

## 5. Recruit Profile Picture (Recruit Onboarding - Step 4)

### View File
- `resources/views/forms/establishment/recruit-step4.blade.php`

### Upload Details
- **Field Name**: `profile_picture_data` (base64 encoded)
- **Input ID**: `onboarding-profile-picture-upload`
- **File Types**: <span style="color: red;">Images (all types)</span>
- **Max File Size**: <span style="color: red;">2MB (final), 5MB (for cropping)</span>
- **Storage**: Base64 encoded in hidden field
- **Route**: `recruit.onboarding.step4.save`
- **Form Enctype**: `multipart/form-data`
- **Special Features**: Image cropper with passport photo aspect ratio (4:5)

### Description
Official passport photo for recruit onboarding.

---

## 6. Recruit Documents (Recruit Onboarding - Step 2)

### View File
- `resources/views/forms/establishment/recruit-step2.blade.php`

### Upload Details
- **Field Name**: `documents[]` (multiple files)
- **Input ID**: `documents-input`
- **File Types**: <span style="color: red;">JPEG, JPG, PNG</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB per file)</span>
- **Storage Location**: `storage/app/local/temp/recruit-documents/` (RecruitOnboardingController line 353)
- **Controller**: `app/Http/Controllers/RecruitOnboardingController.php`
- **Route**: `recruit.onboarding.step2.save`
- **Form Enctype**: `multipart/form-data`

### Description
Multiple documents uploaded during recruit onboarding (Step 2).

---

## 7. Officer Profile Picture Update (Edit Officer)

### View File
- `resources/views/forms/officer/edit.blade.php`

### Upload Details
- **Field Name**: `profile_picture`
- **File Types**: <span style="color: red;">Images (all types)</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Storage Location**: `storage/app/public/profiles/`
- **Controller**: `app/Http/Controllers/OfficerController.php` (line 1086)
- **Route**: `hrd.officers.update`
- **Form Enctype**: `multipart/form-data`

### Description
Profile picture update for existing officers.

---

## 8. Officer Profile Picture (Profile Page)

### View File
- `resources/views/dashboards/officer/profile.blade.php`

### Upload Details
- **Input ID**: `profile-picture-upload`
- **File Types**: <span style="color: red;">Images (all types)</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Storage Location**: `storage/app/public/profiles/`
- **Controller**: `app/Http/Controllers/OfficerController.php` (line 1086)
- **API Endpoint**: Likely via AJAX
- **Form Enctype**: `multipart/form-data`

### Description
Profile picture upload from officer's profile page.

---

## 9. Deceased Officer Death Certificate

### View File
- `resources/views/forms/deceased-officer/create.blade.php`

### Upload Details
- **Field Name**: `death_certificate`
- **File Types**: <span style="color: red;">JPEG, JPG, PNG, PDF</span>
- **Max File Size**: <span style="color: red;">5MB</span>
- **Storage Location**: `storage/app/public/death-certificates/`
- **Controller**: `app/Http/Controllers/DeceasedOfficerController.php` (line 121)
- **Route**: `area-controller.deceased-officers.store` or `staff-officer.deceased-officers.store`
- **Form Enctype**: `multipart/form-data`

### Description
Death certificate upload when reporting a deceased officer.

---

## 10. TRADOC Training Results CSV Upload

### View File
- `resources/views/forms/tradoc/upload.blade.php`

### Upload Details
- **Field Name**: `csv_file`
- **File Types**: <span style="color: red;">CSV, TXT</span>
- **Max File Size**: <span style="color: red;">5MB</span>
- **Controller**: `app/Http/Controllers/TRADOCController.php` (line 112)
- **Route**: `tradoc.upload.store`
- **Form Enctype**: `multipart/form-data`

### Description
CSV file upload for training results by rank.

---

## 11. Establishment CSV Upload (New Recruits)

### View File
- `resources/views/dashboards/establishment/new-recruits.blade.php`

### Upload Details
- **Field Name**: `csv_file`
- **File Types**: <span style="color: red;">CSV, TXT</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Controller**: `app/Http/Controllers/EstablishmentController.php` (line 730)
- **Route**: `establishment.onboarding.csv-upload`
- **Form Enctype**: `multipart/form-data`
- **Max Entries**: 10 per upload

### Description
CSV file upload for bulk recruit onboarding.

---

## 12. Establishment CSV Upload (New Recruit Form)

### View File
- `resources/views/forms/establishment/new-recruit.blade.php`

### Upload Details
- **Field Name**: `csv_file`
- **File Types**: <span style="color: red;">CSV, TXT</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Controller**: `app/Http/Controllers/EstablishmentController.php` (line 1803)
- **Route**: `establishment.new-recruits.store`
- **Form Enctype**: `multipart/form-data`
- **Max Entries**: 50 per upload

### Description
CSV file upload for creating new recruits in bulk.

---

## 13. HRD CSV Upload (Onboarding)

### View File
- `resources/views/dashboards/hrd/onboarding.blade.php`

### Upload Details
- **Field Name**: `csv_file`
- **File Types**: <span style="color: red;">CSV, TXT</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Controller**: `app/Http/Controllers/OnboardingController.php` (line 383)
- **Route**: `hrd.onboarding.csv-upload`
- **Form Enctype**: `multipart/form-data`
- **Max Entries**: 10 per upload

### Description
CSV file upload for HRD onboarding process.

---

## 14. API Endpoint - Officer Profile Picture

### Controller File
- `app/Http/Controllers/Api/V1/OfficerController.php`

### Upload Details
- **Field Name**: `profile_picture`
- **File Types**: <span style="color: red;">Images (all types)</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Storage Location**: `storage/app/public/profiles/`
- **Line**: 240-242
- **Method**: API endpoint

### Description
API endpoint for uploading officer profile pictures.

---

## 15. API Endpoint - Leave Application Medical Certificate

### Controller File
- `app/Http/Controllers/Api/V1/LeaveApplicationController.php`

### Upload Details
- **Field Name**: `medical_certificate`
- **File Types**: <span style="color: red;">JPEG, PDF</span>
- **Max File Size**: <span style="color: red;">Not specified (typically 5MB)</span>
- **Storage Location**: `storage/app/public/certificates/`
- **Line**: 131-132
- **Method**: API endpoint

### Description
API endpoint for uploading medical certificates with leave applications.

---

## Storage Locations Summary

### Public Storage (accessible via web)
- `storage/app/public/profiles/` - Profile pictures
- `storage/app/public/certificates/` - Medical certificates
- `storage/app/public/death-certificates/` - Death certificates
- `storage/app/public/officer-documents/` - Officer documents (final)
- `storage/app/public/officer-documents-temp/` - Officer documents (temporary)

### Local Storage (not web-accessible)
- `storage/app/local/temp/recruit-documents/` - Recruit documents (temporary)

---

## Common Patterns

1. **Image Uploads**: Most use `enctype="multipart/form-data"` in forms
2. **Profile Pictures**: Often use base64 encoding or direct file upload
3. **CSV Uploads**: Used for bulk operations (onboarding, training results)
4. **Document Uploads**: Support multiple file types (JPEG, PNG, PDF)
5. **Temporary Storage**: Some documents stored temporarily before final processing

---

## Notes

- All file uploads should validate file types and sizes on both client and server side
- Profile pictures often include image cropping functionality
- CSV uploads have specific column requirements documented in the views
- Some uploads support multiple files (using `[]` in field names)
