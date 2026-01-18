<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->info('=' . str_repeat('=', 60));
        
        // Step 1: Core prerequisites (Roles, Commands, Leave Types)
        $this->command->info('\n📋 Step 1: Seeding core prerequisites...');
        $this->call([
            RoleSeeder::class,
            LeaveTypeSeeder::class,
            ZoneAndCommandSeeder::class, // Zones and all commands
            BankSeeder::class,
            PfaSeeder::class,
        ]);
        
        // Step 2: Create system users (CGC, TRADOC, ICT, Admin users)
        $this->command->info('\n👤 Step 2: Creating system users...');
        $this->call([
            CreateCGCUserSeeder::class,
            CreateTRADOCICTUsersSeeder::class,
            UserSeeder::class,
        ]);
        
        // Step 3: Seed comprehensive test data
        $this->command->info('\n🎯 Step 3: Seeding comprehensive test data...');
        $this->call([
            CompleteSystemSeeder::class, // Complete system with all roles (80+ officers)
            ComprehensiveSeeder::class, // ~50 officers with comprehensive data
            TestDataSeeder::class, // 100 officers with full test data
        ]);
        
        // Step 4: Seed specialized test data (these don't delete existing data)
        $this->command->info('\n🧪 Step 4: Seeding specialized test data...');
        $this->call([
            HRDTestDataSeeder::class, // HRD test data (50 TEST officers)
            PromotionCriteriaTestSeeder::class, // 5 officers for promotion testing
            TestPreretirementLeaveSeeder::class, // 10 officers for preretirement testing
            TrainingResultSeeder::class, // Training results for new recruits
        ]);
        
        $this->command->info('\n' . str_repeat('=', 62));
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('=' . str_repeat('=', 62));
        $this->command->info('\n📊 Summary:');
        $this->command->info('  ✓ All roles created');
        $this->command->info('  ✓ All commands and zones created');
        $this->command->info('  ✓ All system users created');
        $this->command->info('  ✓ Comprehensive test data loaded');
        $this->command->info('  ✓ Specialized test scenarios created');
        $this->command->info('\n🔑 Test Credentials:');
        $this->command->info('  • HRD: hrd@ncs.gov.ng / password123');
        $this->command->info('  • CGC: cgc@ncs.gov.ng / password123');
        $this->command->info('  • Officer: officer@ncs.gov.ng / password123');
        $this->command->info('  • TRADOC: tradoc@ncs.gov.ng / password123');
        $this->command->info('  • ICT: ict@ncs.gov.ng / password123');
    }
}
