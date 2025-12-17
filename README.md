# NCS Employee Portal

A comprehensive employee management system for the Nigeria Customs Service (NCS).

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Node.js (for assets, optional)

### Installation

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   Edit `.env` and set your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ncs_portal
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run Migrations & Seed**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Start Server**
   ```bash
   php artisan serve
   ```

6. **Access Application**
   - Frontend: http://localhost:8000
   - Login: http://localhost:8000/login

### Default Login Credentials

**HRD Account:**
- Email: `hrd@ncs.gov.ng`
- Password: `password123`

**Staff Officer Account:**
- Email: `staff@ncs.gov.ng`
- Password: `password123`

## Project Structure

```
pisportal/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/        # API Controllers
│   │   │   ├── Auth/          # Authentication Controllers
│   │   │   └── DashboardController.php
│   │   ├── Requests/          # Form Request Validation
│   │   ├── Resources/         # API Resources
│   │   └── Middleware/        # Custom Middleware
│   ├── Models/                # Eloquent Models
│   ├── Services/              # Business Logic Services
│   └── Jobs/                  # Background Jobs
├── database/
│   ├── migrations/            # Database Migrations
│   └── seeders/              # Database Seeders
├── resources/
│   └── views/
│       ├── layouts/           # Blade Layouts
│       ├── components/        # Blade Components
│       ├── auth/              # Authentication Views
│       ├── dashboards/        # Dashboard Views
│       └── forms/             # Form Views
├── routes/
│   ├── web.php               # Web Routes
│   └── api.php               # API Routes
└── ncs-employee-portal/       # Static Assets (CSS, JS, Images)
```

## Features

### Roles & Permissions
- **HRD**: Full system access, officer management, emolument timeline
- **Officer**: Raise emolument, apply for leave/pass, view profile
- **Staff Officer**: Leave/pass management, manning level, duty roster
- **Assessor**: Emolument assessment
- **Validator**: Emolument validation
- **Area Controller**: Command-level oversight
- **DC Admin**: District-level administration
- **Accounts**: Financial processing
- **Board**: Promotion approvals
- **Building Unit**: Quarter management
- **Establishment**: Service number allocation
- **Welfare**: Deceased officer management

### Core Workflows
1. **Emolument**: Raise → Assess → Validate → Approve → Process
2. **Leave/Pass**: Apply → Review → Approve/Reject
3. **Onboarding**: Multi-step officer registration
4. **Promotion**: Initiate → Assess → Board Approval
5. **Retirement**: Process → Final Settlement
6. **Deceased Officer**: Notification → Settlement

## API Documentation

All API endpoints are versioned under `/api/v1/`.

### Authentication
- `POST /api/v1/auth/login` - Login with email or service number
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/me` - Get current user

### Key Endpoints
- Emoluments: `/api/v1/emoluments`
- Leave Applications: `/api/v1/leave-applications`
- Pass Applications: `/api/v1/pass-applications`
- Officers: `/api/v1/officers`
- Commands: `/api/v1/commands`

See `docs/API_SPECIFICATION.md` for complete API documentation.

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Database Migrations
```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback
```

## Documentation

- **System Specification**: `docs/SYSTEM_SPECIFICATION.md`
- **Database Schema**: `docs/DATABASE_SCHEMA.md`
- **API Specification**: `docs/API_SPECIFICATION.md`
- **Laravel Setup**: `docs/LARAVEL_SETUP.md`

## Support

For issues or questions, contact the development team.

## License

Proprietary - Nigeria Customs Service
# mis.ncsportal.com
