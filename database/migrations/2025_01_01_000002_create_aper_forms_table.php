<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aper_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->foreignId('timeline_id')->constrained('aper_timelines');
            $table->integer('year');
            
            // Workflow fields
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'REPORTING_OFFICER', 'COUNTERSIGNING_OFFICER', 'OFFICER_REVIEW', 'ACCEPTED', 'REJECTED', 'STAFF_OFFICER_REVIEW', 'FINALIZED'])->default('DRAFT');
            $table->foreignId('reporting_officer_id')->nullable()->constrained('users');
            $table->foreignId('countersigning_officer_id')->nullable()->constrained('users');
            $table->foreignId('staff_officer_id')->nullable()->constrained('users'); // For reassignment
            
            // Rejection fields
            $table->boolean('is_rejected')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->enum('rejected_by_role', ['REPORTING_OFFICER', 'COUNTERSIGNING_OFFICER', 'OFFICER'])->nullable();
            
            // Timestamps for workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reporting_officer_completed_at')->nullable();
            $table->timestamp('countersigning_officer_completed_at')->nullable();
            $table->timestamp('officer_reviewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // Part 1: Personal Records
            $table->string('service_number', 50)->nullable();
            $table->string('title', 10)->nullable(); // Mr./Mrs./Miss
            $table->string('surname', 255)->nullable();
            $table->string('forenames', 255)->nullable();
            $table->string('department_area', 255)->nullable();
            $table->string('cadre', 50)->nullable(); // GD or SS
            $table->string('unit', 255)->nullable(); // For Support Staff
            $table->string('zone', 255)->nullable();
            $table->date('date_of_first_appointment')->nullable();
            $table->date('date_of_present_appointment')->nullable();
            $table->string('rank', 255)->nullable();
            $table->string('hapass', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('state_of_origin', 255)->nullable();
            $table->json('qualifications')->nullable(); // Array of {qualification, year}
            
            // Part 2: Leave Records
            $table->json('sick_leave_records')->nullable(); // Array of {from, to, days, type}
            $table->json('maternity_leave_records')->nullable(); // Array of {from, to, days}
            $table->json('annual_casual_leave_records')->nullable(); // Array of {from, to, days}
            
            // Part 2: Target Setting
            $table->json('division_targets')->nullable(); // Array of targets
            $table->json('individual_targets')->nullable(); // Array of targets
            $table->string('project_cost', 255)->nullable();
            $table->string('completion_time', 255)->nullable();
            $table->text('quantity_conformity')->nullable();
            $table->text('quality_conformity')->nullable();
            
            // Part 2: Job Description
            $table->text('main_duties')->nullable();
            $table->string('joint_discussion', 10)->nullable(); // YES/NO
            $table->string('properly_equipped', 10)->nullable(); // YES/NO
            $table->text('equipment_difficulties')->nullable();
            $table->text('difficulties_encountered')->nullable();
            $table->text('supervisor_assistance_methods')->nullable();
            $table->string('periodic_review', 255)->nullable();
            $table->string('performance_measure_up', 10)->nullable(); // YES/NO
            $table->text('solution_admonition')->nullable();
            $table->text('final_evaluation')->nullable();
            $table->text('adhoc_duties')->nullable();
            $table->string('adhoc_affected_duties', 10)->nullable(); // YES/NO
            $table->date('schedule_duty_from')->nullable();
            $table->date('schedule_duty_to')->nullable();
            
            // Part 3: Assessment of Performance (Reporting Officer)
            $table->string('targets_agreed', 10)->nullable(); // YES/NO
            $table->text('targets_agreement_details')->nullable();
            $table->string('duties_agreed', 10)->nullable(); // YES/NO
            $table->text('duties_agreement_details')->nullable();
            
            // Job Assessment/General Ability (with grades A-F)
            $table->string('job_understanding_grade', 1)->nullable(); // A-F
            $table->text('job_understanding_comment')->nullable();
            $table->string('knowledge_application_grade', 1)->nullable();
            $table->text('knowledge_application_comment')->nullable();
            $table->string('accomplishment_grade', 1)->nullable();
            $table->text('accomplishment_comment')->nullable();
            $table->string('judgement_grade', 1)->nullable();
            $table->text('judgement_comment')->nullable();
            $table->string('work_speed_accuracy_grade', 1)->nullable();
            $table->text('work_speed_accuracy_comment')->nullable();
            $table->string('written_expression_grade', 1)->nullable();
            $table->text('written_expression_comment')->nullable();
            $table->string('oral_expression_grade', 1)->nullable();
            $table->text('oral_expression_comment')->nullable();
            $table->string('staff_relations_grade', 1)->nullable();
            $table->text('staff_relations_comment')->nullable();
            $table->string('public_relations_grade', 1)->nullable();
            $table->text('public_relations_comment')->nullable();
            $table->string('staff_management_grade', 1)->nullable();
            $table->text('staff_management_comment')->nullable();
            
            // Quality of Work
            $table->string('quality_of_work_grade', 1)->nullable();
            $table->text('quality_of_work_comment')->nullable();
            $table->string('productivity_grade', 1)->nullable();
            $table->text('productivity_comment')->nullable();
            $table->string('effective_use_of_data_grade', 1)->nullable();
            $table->text('effective_use_of_data_comment')->nullable();
            $table->string('initiative_grade', 1)->nullable();
            $table->text('initiative_comment')->nullable();
            
            // Character Traits
            $table->string('dependability_grade', 1)->nullable();
            $table->text('dependability_comment')->nullable();
            $table->string('loyalty_grade', 1)->nullable();
            $table->text('loyalty_comment')->nullable();
            $table->string('honesty_grade', 1)->nullable();
            $table->text('honesty_comment')->nullable();
            $table->string('reliability_under_pressure_grade', 1)->nullable();
            $table->text('reliability_under_pressure_comment')->nullable();
            $table->string('sense_of_responsibility_grade', 1)->nullable();
            $table->text('sense_of_responsibility_comment')->nullable();
            $table->string('appearance_grade', 1)->nullable();
            $table->text('appearance_comment')->nullable();
            
            // Work Habits
            $table->string('punctuality_grade', 1)->nullable();
            $table->text('punctuality_comment')->nullable();
            $table->string('attendance_grade', 1)->nullable();
            $table->text('attendance_comment')->nullable();
            $table->string('drive_determination_grade', 1)->nullable();
            $table->text('drive_determination_comment')->nullable();
            $table->string('resource_utilization_grade', 1)->nullable();
            $table->text('resource_utilization_comment')->nullable();
            
            // Sanctions
            $table->string('disciplinary_action', 10)->nullable(); // YES/NO
            $table->text('disciplinary_action_details')->nullable();
            
            // Rewards
            $table->string('special_commendation', 10)->nullable(); // YES/NO
            $table->text('special_commendation_details')->nullable();
            
            // Leadership Attainment
            $table->string('encourage_standards_grade', 1)->nullable();
            $table->text('encourage_standards_comment')->nullable();
            $table->string('train_subordinates_grade', 1)->nullable();
            $table->text('train_subordinates_comment')->nullable();
            $table->string('good_example_grade', 1)->nullable();
            $table->text('good_example_comment')->nullable();
            $table->string('suggestions_improvements_grade', 1)->nullable();
            $table->text('suggestions_improvements_comment')->nullable();
            
            // Training Courses/Seminars
            $table->json('training_courses')->nullable(); // Array of {type, where, from, to}
            $table->text('training_enhanced_performance')->nullable();
            
            // Job Performance (Officer's self-assessment)
            $table->text('satisfactory_jobs')->nullable();
            $table->text('success_failure_causes')->nullable();
            $table->text('training_needs')->nullable();
            $table->string('effective_use_capabilities', 10)->nullable(); // YES/NO
            $table->text('better_use_abilities')->nullable();
            $table->string('job_satisfaction', 10)->nullable(); // YES/NO
            $table->text('job_satisfaction_causes')->nullable();
            
            // Overall Assessment
            $table->string('overall_assessment', 1)->nullable(); // A-F
            $table->text('training_needs_assessment')->nullable();
            $table->text('general_remarks')->nullable();
            
            // Suggestions
            $table->string('suggest_different_job', 10)->nullable(); // YES/NO
            $table->text('different_job_details')->nullable();
            $table->string('suggest_transfer', 10)->nullable(); // YES/NO
            $table->text('transfer_details')->nullable();
            
            // Promotability
            $table->string('promotability', 1)->nullable(); // A-F
            
            // Declarations
            $table->text('officer_comments')->nullable();
            $table->timestamp('officer_signed_at')->nullable();
            $table->text('reporting_officer_declaration')->nullable();
            $table->timestamp('reporting_officer_signed_at')->nullable();
            $table->foreignId('reporting_officer_user_id')->nullable()->constrained('users');
            $table->text('countersigning_officer_declaration')->nullable();
            $table->timestamp('countersigning_officer_signed_at')->nullable();
            $table->foreignId('countersigning_officer_user_id')->nullable()->constrained('users');
            $table->text('head_of_department_declaration')->nullable();
            $table->timestamp('head_of_department_signed_at')->nullable();
            $table->foreignId('head_of_department_user_id')->nullable()->constrained('users');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['officer_id', 'year']);
            $table->index('status');
            $table->index('timeline_id');
            $table->index('reporting_officer_id');
            $table->index('countersigning_officer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aper_forms');
    }
};

