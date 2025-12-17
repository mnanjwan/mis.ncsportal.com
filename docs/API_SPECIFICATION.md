# NCS Employee Portal - API Specification

## Table of Contents
1. [Authentication](#authentication)
2. [Base URL & Conventions](#base-url--conventions)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [Authentication Endpoints](#authentication-endpoints)
6. [User & Role Management](#user--role-management)
7. [Officer Management](#officer-management)
8. [Emolument Endpoints](#emolument-endpoints)
9. [Leave & Pass Endpoints](#leave--pass-endpoints)
10. [Posting & Movement Endpoints](#posting--movement-endpoints)
11. [Manning Level Endpoints](#manning-level-endpoints)
12. [Promotion Endpoints](#promotion-endpoints)
13. [Retirement Endpoints](#retirement-endpoints)
14. [Roaster Management](#roaster-management)
15. [Quarters Management](#quarters-management)
16. [NCS Employee App (Chat)](#ncs-employee-app-chat)
17. [Document Management](#document-management)
18. [Notifications](#notifications)
19. [Reports & Lists](#reports--lists)

---

## Authentication

### Laravel Sanctum
- All API endpoints (except login/register) require authentication
- Token-based authentication using Laravel Sanctum
- Token passed in `Authorization` header: `Bearer {token}`
- Token expiration: 24 hours (configurable)

### Role-Based Access Control
- Each endpoint validates user role permissions
- Command-level restrictions enforced
- Officers can only access their own data (except for authorized roles)

---

## Base URL & Conventions

### Base URL
```
Production: https://api.ncsportal.gov.ng/v1
Development: http://localhost:8000/api/v1
```

### HTTP Methods
- `GET` - Retrieve resources
- `POST` - Create resources
- `PUT` - Update entire resource
- `PATCH` - Partial update
- `DELETE` - Delete resource

### URL Conventions
- Use plural nouns: `/api/v1/officers`, `/api/v1/emoluments`
- Nested resources: `/api/v1/officers/{id}/emoluments`
- Filtering: `/api/v1/officers?rank=DC&command_id=1`
- Pagination: `/api/v1/officers?page=1&per_page=20`
- Sorting: `/api/v1/officers?sort=service_number&order=asc`

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [
    // Array of items
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8,
    "from": 1,
    "to": 20
  },
  "links": {
    "first": "/api/v1/officers?page=1",
    "last": "/api/v1/officers?page=8",
    "prev": null,
    "next": "/api/v1/officers?page=2"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z",
    "code": "VALIDATION_ERROR"
  }
}
```

---

## Error Handling

### HTTP Status Codes
- `200 OK` - Successful GET, PUT, PATCH
- `201 Created` - Successful POST
- `204 No Content` - Successful DELETE
- `400 Bad Request` - Invalid request
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `500 Internal Server Error` - Server error

### Error Codes
- `AUTHENTICATION_REQUIRED` - Token missing/invalid
- `PERMISSION_DENIED` - Insufficient role permissions
- `VALIDATION_ERROR` - Request validation failed
- `RESOURCE_NOT_FOUND` - Resource doesn't exist
- `WORKFLOW_ERROR` - Invalid workflow state transition
- `DUPLICATE_ENTRY` - Resource already exists

---

## Authentication Endpoints

### POST /api/v1/auth/login
**Description:** Officer login (can use email or service number)

**Request Body (Option 1 - Email):**
```json
{
  "email": "officer@example.com",
  "password": "password123"
}
```

**Request Body (Option 2 - Service Number):**
```json
{
  "service_number": "57616",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "officer@example.com",
      "roles": ["Officer"],
      "officer": {
        "id": 1,
        "service_number": "57616",
        "name": "John Doe",
        "rank": "DC",
        "command": {
          "id": 1,
          "name": "Lagos Command"
        }
      }
    },
    "token": "1|abcdef123456...",
    "token_type": "Bearer",
    "expires_at": "2024-01-16T10:30:00Z"
  }
}
```

**Validation Rules:**
- Either `email` OR `service_number` is required (not both)
- `email`: required_without:service_number, email format
- `service_number`: required_without:email, string, exists:officers,service_number
- `password`: required, min:8

**Notes:**
- System will authenticate using email if provided, otherwise uses service_number
- Service number lookup is case-insensitive
- Password must match the user account associated with the email or service number

---

### POST /api/v1/auth/logout
**Description:** Logout current user

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### POST /api/v1/auth/refresh
**Description:** Refresh authentication token

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "2|newtoken123...",
    "expires_at": "2024-01-16T10:30:00Z"
  }
}
```

---

### GET /api/v1/auth/me
**Description:** Get current authenticated user

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "officer@example.com",
      "roles": ["Officer"],
      "officer": {
        "id": 1,
        "service_number": "57616",
        "initials": "J.",
        "surname": "Doe",
        "rank": "DC",
        "command": {
          "id": 1,
          "name": "Lagos Command"
        }
      }
    }
  }
}
```

---

## User & Role Management

### GET /api/v1/users
**Description:** List all users (HRD only)

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `role` - Filter by role
- `command_id` - Filter by command
- `is_active` - Filter by active status
- `page` - Page number
- `per_page` - Items per page

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "officer@example.com",
      "roles": ["Officer"],
      "is_active": true,
      "last_login": "2024-01-15T08:00:00Z"
    }
  ],
  "meta": { /* pagination */ }
}
```

---

### POST /api/v1/users
**Description:** Create new user (HRD only)

**Request Body:**
```json
{
  "email": "newuser@example.com",
  "password": "password123",
  "role_ids": [2],
  "command_id": 1
}
```

**Validation Rules:**
- `email`: required, email, unique:users
- `password`: required, min:8, confirmed
- `role_ids`: required, array, exists:roles,id
- `command_id`: required_if:role,command_level, exists:commands,id

---

### GET /api/v1/roles
**Description:** List all roles

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "HRD",
      "code": "HRD",
      "description": "Human Resources Department",
      "access_level": "system_wide"
    }
  ]
}
```

---

## Officer Management

### GET /api/v1/officers
**Description:** List officers (with role-based filtering)

**Query Parameters:**
- `command_id` - Filter by command
- `rank` - Filter by rank
- `service_number` - Search by service number
- `is_active` - Filter by active status
- `is_deceased` - Filter deceased officers
- `search` - Search by name/service number
- `page`, `per_page`, `sort`, `order`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_number": "57616",
      "initials": "J.",
      "surname": "Doe",
      "full_name": "J. Doe",
      "rank": "DC",
      "command": {
        "id": 1,
        "name": "Lagos Command"
      },
      "is_active": true,
      "is_deceased": false
    }
  ],
  "meta": { /* pagination */ }
}
```

**Access Control:**
- Officers: Only see themselves
- Staff Officer: See officers in their command
- HRD/Area Controller: See all officers

---

### GET /api/v1/officers/{id}
**Description:** Get officer details

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_number": "57616",
    "initials": "J.",
    "surname": "Doe",
    "sex": "M",
    "date_of_birth": "1980-01-15",
    "date_of_first_appointment": "2005-03-01",
    "date_of_present_appointment": "2020-06-15",
    "substantive_rank": "DC",
    "salary_grade_level": "GL 08",
    "state_of_origin": "Lagos",
    "lga": "Ikeja",
    "geopolitical_zone": "South West",
    "marital_status": "Married",
    "entry_qualification": "B.Sc",
    "discipline": "Computer Science",
    "additional_qualification": "M.Sc",
    "present_station": {
      "id": 1,
      "name": "Lagos Command"
    },
    "date_posted_to_station": "2023-01-10",
    "residential_address": "123 Main St, Lagos",
    "permanent_home_address": "456 Home St, Lagos",
    "phone_number": "+2348012345678",
    "email": "officer@example.com",
    "bank_name": "First Bank",
    "bank_account_number": "1234567890",
    "sort_code": "123456",
    "pfa_name": "Tangerine",
    "rsa_number": "PEN123456789012",
    "unit": "IT Unit",
    "interdicted": false,
    "suspended": false,
    "dismissed": false,
    "quartered": true,
    "is_deceased": false,
    "profile_picture_url": "/storage/profiles/1.jpg",
    "next_of_kin": [
      {
        "id": 1,
        "name": "Jane Doe",
        "relationship": "Spouse",
        "phone_number": "+2348012345679"
      }
    ],
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
}
```

---

### POST /api/v1/officers/onboarding
**Description:** Submit onboarding form (Officer)

**Request Body:**
```json
{
  "service_number": "57617",
  "initials": "J.",
  "surname": "Doe",
  "sex": "M",
  "date_of_first_appointment": "2024-01-01",
  "date_of_present_appointment": "2024-01-01",
  "substantive_rank": "AC",
  "salary_grade_level": "GL 07",
  "date_of_birth": "1990-01-15",
  "state_of_origin": "Lagos",
  "lga": "Ikeja",
  "geopolitical_zone": "South West",
  "marital_status": "Single",
  "entry_qualification": "B.Sc",
  "discipline": "Computer Science",
  "additional_qualification": null,
  "present_station": 1,
  "date_posted_to_station": "2024-01-01",
  "residential_address": "123 Main St",
  "permanent_home_address": "456 Home St",
  "phone_number": "+2348012345678",
  "email": "newofficer@example.com",
  "bank_name": "First Bank",
  "bank_account_number": "1234567890",
  "sort_code": "123456",
  "pfa_name": "Tangerine",
  "rsa_number": "PEN123456789012",
  "unit": "IT Unit",
  "next_of_kin": [
    {
      "name": "Jane Doe",
      "relationship": "Spouse",
      "phone_number": "+2348012345679"
    }
  ],
  "interdicted": false,
  "suspended": false,
  "quartered": false,
  "documents": [
    {
      "type": "certificate",
      "file": "base64_encoded_file_or_file_id"
    }
  ]
}
```

**Validation Rules:**
- All fields as per onboarding requirements
- `rsa_number`: required, regex:/^PEN\d{12}$/
- `email`: required, email, unique
- `service_number`: required, unique (if new recruit)
- Documents: max 5MB, jpeg/jpg/png preferred

**Response (201):**
```json
{
  "success": true,
  "message": "Onboarding submitted successfully",
  "data": {
    "officer_id": 123,
    "status": "pending_review"
  }
}
```

---

### PATCH /api/v1/officers/{id}
**Description:** Update officer information (restricted fields)

**Access Control:**
- Officers: Can only update limited fields (profile picture, phone)
- HRD/Staff Officer: Can update most fields
- Board: Can update rank only

**Request Body (Officer):**
```json
{
  "phone_number": "+2348012345678",
  "profile_picture": "base64_encoded_file"
}
```

**Request Body (HRD):**
```json
{
  "present_station": 2,
  "date_posted_to_station": "2024-01-20",
  "unit": "New Unit"
}
```

---

### POST /api/v1/officers/{id}/documents
**Description:** Upload document for officer

**Request Body (multipart/form-data):**
```
document_type: "certificate"
file: [binary file]
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "document_type": "certificate",
    "file_name": "certificate.jpg",
    "file_url": "/storage/documents/1/certificate.jpg",
    "uploaded_at": "2024-01-15T10:30:00Z"
  }
}
```

---

## Emolument Endpoints

### GET /api/v1/emolument-timelines
**Description:** Get active emolument timeline (HRD creates, Officers view)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "year": 2024,
    "start_date": "2024-01-01",
    "end_date": "2024-03-31",
    "is_extended": false,
    "extension_end_date": null,
    "is_active": true,
    "can_submit": true,
    "days_remaining": 45
  }
}
```

---

### POST /api/v1/emolument-timelines
**Description:** Create emolument timeline (HRD only)

**Request Body:**
```json
{
  "year": 2024,
  "start_date": "2024-01-01",
  "end_date": "2024-03-31"
}
```

**Validation Rules:**
- `year`: required, integer, unique:emolument_timelines,year
- `start_date`: required, date, before:end_date
- `end_date`: required, date

---

### PATCH /api/v1/emolument-timelines/{id}/extend
**Description:** Extend emolument timeline (HRD only)

**Request Body:**
```json
{
  "extension_end_date": "2024-04-30"
}
```

---

### GET /api/v1/officers/{id}/emoluments
**Description:** Get officer's emolument history

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "year": 2024,
      "bank_name": "First Bank",
      "bank_account_number": "1234567890",
      "pfa_name": "Tangerine",
      "rsa_pin": "PEN123456789012",
      "status": "VALIDATED",
      "submitted_at": "2024-01-15T10:00:00Z",
      "assessed_at": "2024-01-16T10:00:00Z",
      "validated_at": "2024-01-17T10:00:00Z"
    }
  ]
}
```

---

### POST /api/v1/officers/{id}/emoluments
**Description:** Raise emolument (Officer)

**Request Body:**
```json
{
  "bank_name": "First Bank",
  "bank_account_number": "1234567890",
  "pfa_name": "Tangerine",
  "rsa_pin": "PEN123456789012",
  "next_of_kin": [
    {
      "name": "Jane Doe",
      "relationship": "Spouse",
      "phone_number": "+2348012345679"
    }
  ]
}
```

**Validation Rules:**
- `bank_name`: required, string
- `bank_account_number`: required, string
- `pfa_name`: required, string
- `rsa_pin`: required, regex:/^PEN\d{12}$/
- `next_of_kin`: required, array, min:1
- Timeline must be active
- Only one emolument per year per officer

**Response (201):**
```json
{
  "success": true,
  "message": "Emolument raised successfully",
  "data": {
    "id": 1,
    "status": "RAISED",
    "submitted_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### GET /api/v1/emoluments
**Description:** List emoluments (Assessor/Validator/HRD)

**Query Parameters:**
- `status` - Filter by status (RAISED, ASSESSED, VALIDATED, PROCESSED)
- `command_id` - Filter by command
- `year` - Filter by year
- `officer_id` - Filter by officer

**Access Control:**
- Assessor: Only sees subordinate officers' emoluments
- Validator: Sees assessed emoluments
- HRD: Sees all emoluments

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "officer": {
        "id": 1,
        "service_number": "57616",
        "name": "J. Doe",
        "rank": "DC"
      },
      "year": 2024,
      "status": "RAISED",
      "submitted_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

---

### GET /api/v1/emoluments/{id}
**Description:** Get emolument details

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "officer": {
      "id": 1,
      "service_number": "57616",
      "name": "J. Doe",
      "rank": "DC"
    },
    "year": 2024,
    "bank_name": "First Bank",
    "bank_account_number": "1234567890",
    "pfa_name": "Tangerine",
    "rsa_pin": "PEN123456789012",
    "next_of_kin": [
      {
        "name": "Jane Doe",
        "relationship": "Spouse"
      }
    ],
    "status": "RAISED",
    "submitted_at": "2024-01-15T10:00:00Z",
    "assessment": null,
    "validation": null
  }
}
```

---

### POST /api/v1/emoluments/{id}/assess
**Description:** Assess emolument (Assessor)

**Request Body:**
```json
{
  "assessment_status": "APPROVED",
  "comments": "All information verified"
}
```

**Validation Rules:**
- `assessment_status`: required, in:APPROVED,REJECTED
- `comments`: nullable, string

**Response (200):**
```json
{
  "success": true,
  "message": "Emolument assessed successfully",
  "data": {
    "id": 1,
    "status": "ASSESSED",
    "assessed_at": "2024-01-16T10:00:00Z"
  }
}
```

---

### POST /api/v1/emoluments/{id}/validate
**Description:** Validate emolument (Validator/Area Controller)

**Request Body:**
```json
{
  "validation_status": "APPROVED",
  "comments": "Validated for payment"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Emolument validated successfully",
  "data": {
    "id": 1,
    "status": "VALIDATED",
    "validated_at": "2024-01-17T10:00:00Z"
  }
}
```

---

### GET /api/v1/emoluments/validated
**Description:** Get validated emoluments for payment (Accounts)

**Query Parameters:**
- `year` - Filter by year
- `command_id` - Filter by command

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "officer": {
        "service_number": "57616",
        "name": "J. Doe"
      },
      "bank_name": "First Bank",
      "bank_account_number": "1234567890",
      "pfa_name": "Tangerine",
      "rsa_number": "PEN123456789012"
    }
  ]
}
```

---

## Leave & Pass Endpoints

### GET /api/v1/leave-types
**Description:** List all leave types

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Annual Leave",
      "code": "ANNUAL_LEAVE",
      "max_duration_days": 30,
      "max_occurrences_per_year": 2,
      "requires_medical_certificate": false,
      "description": "Can be applied in parts but maximum of 2 times in a year"
    }
  ]
}
```

---

### POST /api/v1/leave-types
**Description:** Create custom leave type (HRD only)

**Request Body:**
```json
{
  "name": "Custom Leave",
  "code": "CUSTOM_LEAVE",
  "max_duration_days": 10,
  "max_occurrences_per_year": 1,
  "requires_medical_certificate": false,
  "description": "Custom leave description"
}
```

---

### GET /api/v1/officers/{id}/leave-applications
**Description:** Get officer's leave applications

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-15",
      "number_of_days": 15,
      "status": "APPROVED",
      "submitted_at": "2024-01-15T10:00:00Z",
      "approved_at": "2024-01-16T10:00:00Z"
    }
  ]
}
```

---

### POST /api/v1/officers/{id}/leave-applications
**Description:** Apply for leave (Officer)

**Request Body:**
```json
{
  "leave_type_id": 1,
  "start_date": "2024-02-01",
  "end_date": "2024-02-15",
  "reason": "Family vacation",
  "expected_date_of_delivery": null,
  "medical_certificate": "base64_encoded_file_or_null"
}
```

**Validation Rules:**
- `leave_type_id`: required, exists:leave_types,id
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after:start_date
- `reason`: nullable, string
- `expected_date_of_delivery`: required_if:leave_type_id,maternity_leave, date
- `medical_certificate`: required_if:leave_type_id,requires_medical, file
- Validate leave eligibility (annual leave max 2 times, etc.)
- Validate leave balance

**Response (201):**
```json
{
  "success": true,
  "message": "Leave application submitted successfully",
  "data": {
    "id": 1,
    "status": "PENDING",
    "submitted_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### GET /api/v1/leave-applications
**Description:** List leave applications (Staff Officer/DC Admin)

**Query Parameters:**
- `status` - Filter by status
- `command_id` - Filter by command
- `officer_id` - Filter by officer

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "officer": {
        "id": 1,
        "service_number": "57616",
        "name": "J. Doe"
      },
      "leave_type": {
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-15",
      "number_of_days": 15,
      "status": "MINUTED",
      "submitted_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

---

### POST /api/v1/leave-applications/{id}/minute
**Description:** Minute leave application to DC Admin (Staff Officer)

**Response (200):**
```json
{
  "success": true,
  "message": "Leave application minuted successfully",
  "data": {
    "id": 1,
    "status": "MINUTED",
    "minuted_at": "2024-01-16T10:00:00Z"
  }
}
```

---

### POST /api/v1/leave-applications/{id}/approve
**Description:** Approve/reject leave application (DC Admin)

**Request Body:**
```json
{
  "action": "approve",
  "comments": "Approved for leave"
}
```

**Validation Rules:**
- `action`: required, in:approve,reject
- `comments`: required_if:action,reject, string

**Response (200):**
```json
{
  "success": true,
  "message": "Leave application approved",
  "data": {
    "id": 1,
    "status": "APPROVED",
    "approved_at": "2024-01-17T10:00:00Z",
    "area_controller": {
      "id": 5,
      "name": "Area Controller Name"
    }
  }
}
```

---

### POST /api/v1/leave-applications/{id}/print
**Description:** Mark leave document as printed (Staff Officer)

**Response (200):**
```json
{
  "success": true,
  "message": "Leave document marked as printed",
  "data": {
    "id": 1,
    "printed_at": "2024-01-18T10:00:00Z"
  }
}
```

---

### GET /api/v1/leave-applications/{id}/document
**Description:** Download leave document PDF

**Response:** PDF file download

---

### Pass Application Endpoints (Similar to Leave)

### POST /api/v1/officers/{id}/pass-applications
**Description:** Apply for pass (Officer)

**Request Body:**
```json
{
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "reason": "Personal matters"
}
```

**Validation Rules:**
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after:start_date
- Maximum 5 days (end_date - start_date <= 5)
- Annual leave must be exhausted
- Maximum 2 passes per year

---

### POST /api/v1/pass-applications/{id}/approve
**Description:** Approve/reject pass (DC Admin)

**Similar to leave approval endpoint**

---

## Posting & Movement Endpoints

### GET /api/v1/staff-orders
**Description:** List staff orders (HRD)

**Query Parameters:**
- `status` - Filter by status
- `command_id` - Filter by command
- `officer_id` - Filter by officer

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "SO/2024/001",
      "officer": {
        "id": 1,
        "service_number": "57616",
        "name": "J. Doe"
      },
      "from_command": {
        "id": 1,
        "name": "Lagos Command"
      },
      "to_command": {
        "id": 2,
        "name": "Abuja Command"
      },
      "effective_date": "2024-02-01",
      "order_type": "STAFF_ORDER",
      "created_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

---

### POST /api/v1/staff-orders
**Description:** Create staff order (HRD)

**Request Body:**
```json
{
  "officer_id": 1,
  "to_command_id": 2,
  "effective_date": "2024-02-01"
}
```

**Validation Rules:**
- `officer_id`: required, exists:officers,id
- `to_command_id`: required, exists:commands,id
- `effective_date`: required, date, after_or_equal:today

**Response (201):**
```json
{
  "success": true,
  "message": "Staff order created successfully",
  "data": {
    "id": 1,
    "order_number": "SO/2024/001",
    "status": "ACTIVE"
  }
}
```

---

### POST /api/v1/movement-orders
**Description:** Create movement order (HRD)

**Request Body:**
```json
{
  "criteria_months_at_station": 24,
  "manning_request_id": null,
  "officers": [
    {
      "officer_id": 1,
      "to_command_id": 2
    },
    {
      "officer_id": 2,
      "to_command_id": 3
    }
  ]
}
```

**Validation Rules:**
- `criteria_months_at_station`: nullable, integer, min:1
- `manning_request_id`: nullable, exists:manning_requests,id
- `officers`: required, array, min:1

---

### GET /api/v1/officers/{id}/postings
**Description:** Get officer's posting history

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "command": {
        "id": 1,
        "name": "Lagos Command"
      },
      "posting_date": "2023-01-01",
      "is_current": true,
      "documented_at": "2023-01-05T10:00:00Z"
    }
  ]
}
```

---

### POST /api/v1/officers/{id}/postings/{posting_id}/document
**Description:** Document officer arrival (Staff Officer)

**Response (200):**
```json
{
  "success": true,
  "message": "Officer documented successfully",
  "data": {
    "id": 1,
    "documented_at": "2024-01-20T10:00:00Z",
    "documented_by": {
      "id": 10,
      "name": "Staff Officer Name"
    }
  }
}
```

---

## Manning Level Endpoints

### POST /api/v1/manning-requests
**Description:** Create manning request (Staff Officer)

**Request Body:**
```json
{
  "command_id": 1,
  "items": [
    {
      "rank": "DC",
      "quantity_needed": 3,
      "sex_requirement": "ANY",
      "qualification_requirement": null
    },
    {
      "rank": "AC",
      "quantity_needed": 2,
      "sex_requirement": "M",
      "qualification_requification": "B.Sc Computer Science"
    }
  ],
  "notes": "Urgent requirement for new unit"
}
```

**Validation Rules:**
- `command_id`: required, exists:commands,id
- `items`: required, array, min:1
- Each item: rank required, quantity_needed required, min:1

**Response (201):**
```json
{
  "success": true,
  "message": "Manning request created successfully",
  "data": {
    "id": 1,
    "status": "DRAFT"
  }
}
```

---

### POST /api/v1/manning-requests/{id}/submit
**Description:** Submit manning request to Area Controller (Staff Officer)

**Response (200):**
```json
{
  "success": true,
  "message": "Manning request submitted",
  "data": {
    "id": 1,
    "status": "SUBMITTED"
  }
}
```

---

### POST /api/v1/manning-requests/{id}/approve
**Description:** Approve manning request (Area Controller)

**Response (200):**
```json
{
  "success": true,
  "message": "Manning request approved and forwarded to HRD",
  "data": {
    "id": 1,
    "status": "APPROVED",
    "forwarded_to_hrd_at": "2024-01-16T10:00:00Z"
  }
}
```

---

### GET /api/v1/manning-requests
**Description:** List manning requests

**Query Parameters:**
- `command_id` - Filter by command
- `status` - Filter by status

**Access Control:**
- Staff Officer: See requests from their command
- Area Controller: See requests from their area
- HRD: See all approved requests

---

### POST /api/v1/manning-requests/{id}/match
**Description:** Match officers to manning request (HRD)

**Request Body:**
```json
{
  "matches": [
    {
      "request_item_id": 1,
      "officer_ids": [10, 11, 12]
    },
    {
      "request_item_id": 2,
      "officer_ids": [13, 14]
    }
  ]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Officers matched successfully",
  "data": {
    "manning_request_id": 1,
    "movement_orders_created": 2
  }
}
```

---

## Promotion Endpoints

### GET /api/v1/promotion-eligibility-criteria
**Description:** Get promotion eligibility criteria (HRD)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "rank": "AC",
      "years_in_rank_required": 3.0,
      "is_active": true
    }
  ]
}
```

---

### POST /api/v1/promotion-eligibility-criteria
**Description:** Set promotion criteria (HRD)

**Request Body:**
```json
{
  "rank": "AC",
  "years_in_rank_required": 3.0
}
```

---

### POST /api/v1/promotion-eligibility-lists/generate
**Description:** Generate eligibility list (HRD)

**Request Body:**
```json
{
  "year": 2024
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Eligibility list generated successfully",
  "data": {
    "id": 1,
    "year": 2024,
    "total_officers": 45,
    "generated_at": "2024-01-15T10:00:00Z"
  }
}
```

---

### GET /api/v1/promotion-eligibility-lists/{id}
**Description:** Get eligibility list with officers

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "year": 2024,
    "officers": [
      {
        "serial_number": 1,
        "officer": {
          "id": 1,
          "service_number": "57616",
          "initials": "J.",
          "surname": "Doe",
          "rank": "AC"
        },
        "years_in_rank": 3.5,
        "date_of_first_appointment": "2005-03-01",
        "date_of_present_appointment": "2020-06-15",
        "state": "Lagos",
        "date_of_birth": "1980-01-15"
      }
    ]
  }
}
```

---

### POST /api/v1/promotions
**Description:** Record promotion (Board)

**Request Body:**
```json
{
  "officer_id": 1,
  "to_rank": "DC",
  "promotion_date": "2024-02-01",
  "board_meeting_date": "2024-01-20",
  "notes": "Promoted based on performance"
}
```

**Validation Rules:**
- `officer_id`: required, exists:officers,id
- `to_rank`: required, string
- `promotion_date`: required, date
- Officer must be on eligibility list

**Response (201):**
```json
{
  "success": true,
  "message": "Promotion recorded successfully",
  "data": {
    "id": 1,
    "officer": {
      "id": 1,
      "rank": "DC"
    },
    "promotion_date": "2024-02-01"
  }
}
```

---

## Retirement Endpoints

### POST /api/v1/retirement-lists/generate
**Description:** Generate retirement list (HRD)

**Request Body:**
```json
{
  "year": 2024
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Retirement list generated successfully",
  "data": {
    "id": 1,
    "year": 2024,
    "total_officers": 12,
    "generated_at": "2024-01-15T10:00:00Z"
  }
}
```

---

### GET /api/v1/retirement-lists/{id}
**Description:** Get retirement list

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "year": 2024,
    "officers": [
      {
        "serial_number": 1,
        "officer": {
          "id": 1,
          "service_number": "57616",
          "name": "J. Doe",
          "rank": "DC"
        },
        "retirement_condition": "AGE",
        "date_of_birth": "1964-01-15",
        "date_of_first_appointment": "1989-03-01",
        "date_of_pre_retirement_leave": "2024-01-15",
        "retirement_date": "2024-04-15",
        "notified": false
      }
    ]
  }
}
```

---

### POST /api/v1/retirement-lists/{id}/notify
**Description:** Notify officers of retirement (HRD)

**Request Body:**
```json
{
  "officer_ids": [1, 2, 3]
}
```

---

## Roaster Management

### POST /api/v1/rosters
**Description:** Create duty roster (Staff Officer)

**Request Body:**
```json
{
  "command_id": 1,
  "roster_period_start": "2024-02-01",
  "roster_period_end": "2024-02-29",
  "assignments": [
    {
      "officer_id": 1,
      "duty_date": "2024-02-01",
      "shift": "Morning"
    }
  ]
}
```

---

### POST /api/v1/rosters/{id}/approve
**Description:** Approve roster (Area Controller)

**Response (200):**
```json
{
  "success": true,
  "message": "Roster approved successfully",
  "data": {
    "id": 1,
    "status": "APPROVED",
    "approved_at": "2024-01-20T10:00:00Z"
  }
}
```

---

## Quarters Management

### GET /api/v1/quarters
**Description:** List quarters (Building Unit)

**Query Parameters:**
- `command_id` - Filter by command
- `is_occupied` - Filter by occupancy

---

### POST /api/v1/officers/{id}/quarters
**Description:** Allocate quarter to officer (Building Unit)

**Request Body:**
```json
{
  "quarter_id": 1,
  "allocated_date": "2024-02-01"
}
```

---

### DELETE /api/v1/officers/{id}/quarters/{allocation_id}
**Description:** Deallocate quarter (Building Unit)

---

## NCS Employee App (Chat)

### GET /api/v1/chat/rooms
**Description:** Get user's chat rooms

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Lagos Command",
      "room_type": "COMMAND",
      "command": {
        "id": 1,
        "name": "Lagos Command"
      },
      "unread_count": 5,
      "last_message": {
        "text": "Last message text",
        "sent_at": "2024-01-15T09:00:00Z"
      }
    }
  ]
}
```

---

### GET /api/v1/chat/rooms/{id}/messages
**Description:** Get chat room messages

**Query Parameters:**
- `page` - Page number
- `per_page` - Messages per page (default: 50)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender": {
        "id": 1,
        "name": "J. Doe",
        "rank": "DC",
        "profile_picture_url": "/storage/profiles/1.jpg"
      },
      "message_text": "Hello everyone",
      "attachment_url": null,
      "created_at": "2024-01-15T10:00:00Z"
    }
  ],
  "meta": { /* pagination */ }
}
```

---

### POST /api/v1/chat/rooms/{id}/messages
**Description:** Send message to chat room

**Request Body:**
```json
{
  "message_text": "Hello everyone",
  "attachment": "base64_encoded_file_or_null"
}
```

**Validation Rules:**
- `message_text`: required, string, max:5000
- `attachment`: nullable, file, max:10MB

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "message_text": "Hello everyone",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### POST /api/v1/chat/rooms/{id}/members
**Description:** Add member to chat room (Staff Officer for management groups)

**Request Body:**
```json
{
  "officer_id": 5
}
```

---

## Document Management

### GET /api/v1/officers/{id}/documents
**Description:** Get officer's documents

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "document_type": "certificate",
      "file_name": "certificate.jpg",
      "file_url": "/storage/documents/1/certificate.jpg",
      "file_size": 1024000,
      "uploaded_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

---

### DELETE /api/v1/documents/{id}
**Description:** Delete document

**Access Control:**
- Officers: Can delete their own documents
- HRD/Staff Officer: Can delete any document

---

## Notifications

### GET /api/v1/notifications
**Description:** Get user's notifications

**Query Parameters:**
- `is_read` - Filter by read status
- `type` - Filter by notification type
- `page`, `per_page`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "notification_type": "LEAVE_APPROVED",
      "title": "Leave Application Approved",
      "message": "Your leave application has been approved",
      "is_read": false,
      "created_at": "2024-01-15T10:00:00Z",
      "entity_type": "leave_application",
      "entity_id": 1
    }
  ],
  "meta": { /* pagination */ }
}
```

---

### PATCH /api/v1/notifications/{id}/read
**Description:** Mark notification as read

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "is_read": true,
    "read_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### PATCH /api/v1/notifications/read-all
**Description:** Mark all notifications as read

---

## Reports & Lists

### GET /api/v1/reports/validated-officers
**Description:** Get validated officers for payment (Accounts)

**Query Parameters:**
- `year` - Filter by year
- `command_id` - Filter by command
- `format` - csv, excel, pdf

**Response:** File download or JSON data

---

### GET /api/v1/reports/deceased-officers
**Description:** Get deceased officers list (Accounts/Welfare)

**Query Parameters:**
- `format` - csv, excel, pdf

---

### GET /api/v1/reports/nominal-roll
**Description:** Get command nominal roll (Staff Officer)

**Query Parameters:**
- `command_id` - Command ID
- `format` - csv, excel, pdf

---

## Additional Endpoints

### GET /api/v1/commands
**Description:** List all commands

---

### GET /api/v1/commands/{id}
**Description:** Get command details

---

### GET /api/v1/officers/{id}/courses
**Description:** Get officer's courses (HRD tracks)

---

### POST /api/v1/officers/{id}/courses
**Description:** Nominate officer for course (HRD)

---

### PATCH /api/v1/courses/{id}/complete
**Description:** Mark course as completed (HRD)

---

### POST /api/v1/officers/{id}/deceased
**Description:** Report officer as deceased (Area Controller/Staff Officer)

**Request Body:**
```json
{
  "date_of_death": "2024-01-15",
  "death_certificate": "base64_encoded_file"
}
```

---

### POST /api/v1/deceased-officers/{id}/validate
**Description:** Validate deceased officer (Welfare)

---

### GET /api/v1/deceased-officers/{id}/data
**Description:** Get deceased officer data for benefits (Welfare)

---

## Webhook Endpoints (Future)

### POST /api/v1/webhooks/emolument-timeline-extended
**Description:** Webhook for cron job to extend timeline

---

### POST /api/v1/webhooks/retirement-check
**Description:** Webhook for daily retirement check

---

### POST /api/v1/webhooks/leave-alerts
**Description:** Webhook for leave expiry alerts (72 hours before)

---

### POST /api/v1/webhooks/pass-alerts
**Description:** Webhook for pass expiry alerts

---

## Notes

1. **Laravel Implementation:**
   - Use Laravel Resource Controllers
   - Implement Form Request classes for validation
   - Use Laravel Sanctum for authentication
   - Implement Policy classes for authorization
   - Use Laravel Resources for API responses
   - **Login Authentication:** Support dual login method (email OR service_number)
     - Create custom authentication logic to check both email and service_number
     - Lookup user via email first, if not found, lookup via officer's service_number
     - Ensure service_number lookup is case-insensitive

2. **Validation:**
   - All validation rules should be in Form Request classes
   - Custom validation rules for business logic (RSA PIN format, etc.)
   - Validation messages should be user-friendly

3. **Authorization:**
   - Use Laravel Policies for role-based access control
   - Middleware for command-level restrictions
   - Check permissions at controller level

4. **File Uploads:**
   - Use Laravel Storage for file management
   - Validate file types and sizes
   - Store files in organized directories
   - Generate unique file names

5. **Notifications:**
   - Use Laravel Notifications
   - Queue notifications for better performance
   - Support email, in-app, and SMS notifications

6. **Cron Jobs:**
   - Emolument timeline extension
   - Retirement list generation
   - Leave/pass expiry alerts
   - Daily system checks

7. **Caching:**
   - Cache frequently accessed data (leave types, roles, commands)
   - Cache officer lists with appropriate TTL
   - Use Redis for session and cache storage

8. **Rate Limiting:**
   - Implement rate limiting for API endpoints
   - Different limits for different roles
   - Protect against abuse

