<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupApplication extends Command
{
    protected $signature = 'app:setup';
    protected $description = 'Setup the NCS Employee Portal application';

    public function handle()
    {
        $this->info('Setting up NCS Employee Portal...');

        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('✓ Migrations completed');

        // Seed initial data
        $this->info('Seeding initial data...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->info('✓ Seeders completed');

        // Create storage link
        $this->info('Creating storage link...');
        Artisan::call('storage:link');
        $this->info('✓ Storage link created');

        // Create storage directories
        $this->info('Creating storage directories...');
        $directories = [
            'documents',
            'profiles',
            'certificates',
        ];

        foreach ($directories as $dir) {
            $path = storage_path("app/{$dir}");
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        $this->info('✓ Storage directories created');

        $this->info('');
        $this->info('Setup completed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your .env file with database credentials');
        $this->info('2. Create your first HRD user account');
        $this->info('3. Start the development server: php artisan serve');
    }
}
