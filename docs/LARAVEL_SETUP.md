# Laravel Project Setup Guide

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL/MariaDB or PostgreSQL
- Node.js & NPM (for frontend assets)
- Git

---

## Step 1: Initialize Laravel Project

```bash
# Create new Laravel project
composer create-project laravel/laravel pisportal

# Navigate to project directory
cd pisportal

# Install additional packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require intervention/image
composer require laravel/tinker
```

---

## Step 2: Environment Configuration

### .env File Configuration

```env
APP_NAME="NCS Employee Portal"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Africa/Lagos
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pisportal
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@ncsportal.gov.ng"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

---

## Step 3: Configure Laravel Sanctum

### Publish Sanctum Configuration
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Update config/sanctum.php
```php
'expiration' => 1440, // 24 hours in minutes
'token_prefix' => '',
```

### Update config/cors.php
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000', 'http://localhost:5173'], // Add your frontend URLs
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

---

## Step 4: Configure Spatie Permission

### Publish Migration
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Update config/permission.php (if needed)
- Default guard: 'sanctum'
- Cache expiration: 3600

---

## Step 5: File Storage Configuration

### Update config/filesystems.php
```php
'disks' => [
    // ... existing disks ...
    
    'documents' => [
        'driver' => 'local',
        'root' => storage_path('app/documents'),
        'url' => env('APP_URL').'/storage/documents',
        'visibility' => 'private',
        'throw' => false,
    ],
    
    'profiles' => [
        'driver' => 'local',
        'root' => storage_path('app/profiles'),
        'url' => env('APP_URL').'/storage/profiles',
        'visibility' => 'public',
        'throw' => false,
    ],
    
    'certificates' => [
        'driver' => 'local',
        'root' => storage_path('app/certificates'),
        'url' => env('APP_URL').'/storage/certificates',
        'visibility' => 'private',
        'throw' => false,
    ],
],
```

### Create Storage Directories
```bash
mkdir -p storage/app/documents
mkdir -p storage/app/profiles
mkdir -p storage/app/certificates
mkdir -p storage/app/public
php artisan storage:link
```

---

## Step 6: Database Configuration

### Create Database
```sql
CREATE DATABASE pisportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Update config/database.php (if needed)
- Set default connection
- Configure connection pool
- Set timezone: 'Africa/Lagos'

---

## Step 7: Application Configuration

### Update config/app.php
```php
'timezone' => 'Africa/Lagos',
'locale' => 'en',
'fallback_locale' => 'en',
'faker_locale' => 'en_NG', // Nigerian locale if available
```

---

## Step 8: API Configuration

### Update config/sanctum.php
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,localhost:5173,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### Update routes/api.php
```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

---

## Step 9: Project Structure

### Recommended Directory Structure
```
pisportal/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── OfficerController.php
│   │   │   │   │   ├── EmolumentController.php
│   │   │   │   │   └── ...
│   │   │   │   └── BaseController.php
│   │   ├── Requests/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── Auth/
│   │   │           │   └── LoginRequest.php
│   │   │           ├── Officer/
│   │   │           │   └── OnboardingRequest.php
│   │   │           └── ...
│   │   ├── Resources/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── OfficerResource.php
│   │   │           ├── EmolumentResource.php
│   │   │           └── ...
│   │   └── Middleware/
│   │       ├── CommandAccess.php
│   │       └── RoleMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Officer.php
│   │   ├── Emolument.php
│   │   └── ...
│   ├── Policies/
│   │   ├── OfficerPolicy.php
│   │   ├── EmolumentPolicy.php
│   │   └── ...
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── EmolumentService.php
│   │   ├── LeaveService.php
│   │   └── ...
│   ├── Jobs/
│   │   ├── SendNotificationJob.php
│   │   ├── CheckRetirementJob.php
│   │   └── ...
│   ├── Notifications/
│   │   ├── LeaveApprovedNotification.php
│   │   ├── EmolumentValidatedNotification.php
│   │   └── ...
│   └── Helpers/
│       ├── ServiceNumberHelper.php
│       └── ValidationHelper.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   ├── RoleSeeder.php
│   │   ├── LeaveTypeSeeder.php
│   │   └── ...
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
├── config/
├── storage/
└── tests/
```

---

## Step 10: Create Base Classes

### Base API Controller
Create `app/Http/Controllers/Api/V1/BaseController.php`:
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    protected function successResponse($data = null, $message = 'Operation completed successfully', $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        return response()->json($response, $statusCode);
    }

    protected function errorResponse($message = 'An error occurred', $errors = null, $statusCode = 400, $code = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($code) {
            $response['meta']['code'] = $code;
        }

        return response()->json($response, $statusCode);
    }

    protected function paginatedResponse($data, $meta, $links = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'meta' => $meta,
        ];

        if ($links) {
            $response['links'] = $links;
        }

        return response()->json($response);
    }
}
```

---

## Step 11: Create Custom Middleware

### Command Access Middleware
Create `app/Http/Middleware/CommandAccess.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommandAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // HRD has system-wide access
        if ($user->hasRole('HRD')) {
            return $next($request);
        }

        // Check command-level access
        $commandId = $request->route('command_id') ?? $request->input('command_id');
        
        if ($commandId && $user->officer && $user->officer->present_station != $commandId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to access this command.',
                'meta' => [
                    'code' => 'PERMISSION_DENIED',
                ],
            ], 403);
        }

        return $next($request);
    }
}
```

### Register in `app/Http/Kernel.php`:
```php
protected $middlewareAliases = [
    // ... existing middleware ...
    'command.access' => \App\Http\Middleware\CommandAccess::class,
];
```

---

## Step 12: Create Helper Classes

### Service Number Helper
Create `app/Helpers/ServiceNumberHelper.php`:
```php
<?php

namespace App\Helpers;

use App\Models\Officer;

class ServiceNumberHelper
{
    /**
     * Generate next service number
     */
    public static function generateNext(): string
    {
        $lastOfficer = Officer::orderBy('service_number', 'desc')->first();
        
        if (!$lastOfficer) {
            return '57616'; // Starting service number
        }

        $lastNumber = (int) $lastOfficer->service_number;
        return (string) ($lastNumber + 1);
    }

    /**
     * Validate service number format
     */
    public static function validate(string $serviceNumber): bool
    {
        return preg_match('/^\d{5,}$/', $serviceNumber);
    }
}
```

### Register in `composer.json`:
```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    },
    "files": [
        "app/Helpers/helpers.php"
    ]
}
```

Run: `composer dump-autoload`

---

## Step 13: Create Custom Validation Rules

### RSA PIN Validation Rule
Create `app/Rules/RsaPin.php`:
```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RsaPin implements Rule
{
    public function passes($attribute, $value): bool
    {
        // RSA PIN: Usually 12 digits with PEN prefix
        return preg_match('/^PEN\d{12}$/', $value);
    }

    public function message(): string
    {
        return 'The :attribute must be 12 digits with PEN prefix (e.g., PEN123456789012).';
    }
}
```

---

## Step 14: Queue Configuration

### Update .env
```env
QUEUE_CONNECTION=database
```

### Create Jobs Table
```bash
php artisan queue:table
php artisan migrate
```

---

## Step 15: Schedule Configuration

### Update app/Console/Kernel.php
```php
protected function schedule(Schedule $schedule)
{
    // Daily retirement check
    $schedule->call(function () {
        \App\Jobs\CheckRetirementJob::dispatch();
    })->daily();

    // Emolument timeline extension check (if configured)
    $schedule->call(function () {
        \App\Jobs\CheckEmolumentTimelineJob::dispatch();
    })->daily();

    // Leave/pass expiry alerts
    $schedule->call(function () {
        \App\Jobs\SendLeaveExpiryAlertsJob::dispatch();
        \App\Jobs\SendPassExpiryAlertsJob::dispatch();
    })->hourly();
}
```

---

## Step 16: Testing Setup

### Create Test Directory Structure
```
tests/
├── Feature/
│   ├── Api/
│   │   └── V1/
│   │       ├── AuthTest.php
│   │       ├── OfficerTest.php
│   │       └── ...
│   └── ...
└── Unit/
    └── ...
```

### Update phpunit.xml
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

---

## Step 17: API Documentation Setup (Optional)

### Install Laravel API Documentation Package
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### Update config/l5-swagger.php
- Set API base path
- Configure security definitions
- Enable/disable UI

---

## Step 18: Initial Setup Commands

### Create Setup Command
Create `app/Console/Commands/SetupApplication.php`:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupApplication extends Command
{
    protected $signature = 'app:setup';
    protected $description = 'Setup the application';

    public function handle()
    {
        $this->info('Setting up NCS Employee Portal...');

        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate');

        // Seed initial data
        $this->info('Seeding initial data...');
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
        Artisan::call('db:seed', ['--class' => 'LeaveTypeSeeder']);

        // Create storage link
        $this->info('Creating storage link...');
        Artisan::call('storage:link');

        $this->info('Setup completed successfully!');
    }
}
```

---

## Step 19: Development Tools

### Install Development Packages
```bash
# Code quality
composer require --dev friendsofphp/php-cs-fixer
composer require --dev phpstan/phpstan

# Testing
composer require --dev pestphp/pest
```

### Create .php-cs-fixer.php
```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
```

---

## Step 20: Git Configuration

### Create .gitignore (Laravel default + additions)
```
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
```

### Create README.md
```markdown
# NCS Employee Portal

Laravel-based employee management system for Nigerian Customs Service.

## Installation

1. Clone repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Run `php artisan key:generate`
5. Configure database in `.env`
6. Run `php artisan migrate`
7. Run `php artisan app:setup`
8. Run `php artisan serve`

## API Documentation

API documentation available at `/api/documentation` (when configured)

## Testing

Run `php artisan test`
```

---

## Next Steps

1. **Create Database Migrations** - Convert schema to Laravel migrations
2. **Create Models** - Eloquent models with relationships
3. **Create Controllers** - API controllers implementing endpoints
4. **Create Form Requests** - Validation classes
5. **Create Resources** - API response transformers
6. **Create Policies** - Authorization logic
7. **Create Services** - Business logic layer
8. **Create Seeders** - Initial data population
9. **Create Tests** - Feature and unit tests
10. **Frontend Integration** - Connect with your preferred UI

---

## Notes

- All timestamps use Africa/Lagos timezone
- API versioning: `/api/v1/`
- Authentication: Laravel Sanctum
- File storage: Local filesystem (can be changed to S3 later)
- Queue: Database driver (can be changed to Redis later)
- Caching: File driver (can be changed to Redis later)

---

## Environment-Specific Configurations

### Development
- APP_DEBUG=true
- LOG_LEVEL=debug
- CACHE_DRIVER=file

### Production
- APP_DEBUG=false
- LOG_LEVEL=error
- CACHE_DRIVER=redis
- QUEUE_CONNECTION=redis
- SESSION_DRIVER=redis

