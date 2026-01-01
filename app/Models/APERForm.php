<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APERForm extends Model
{
    use HasFactory;

    protected $table = 'aper_forms';

    protected $fillable = [
        'officer_id',
        'timeline_id',
        'year',
        'status',
        'reporting_officer_id',
        'countersigning_officer_id',
        'staff_officer_id',
        'is_rejected',
        'rejection_reason',
        'staff_officer_rejection_reason',
        'rejected_by_role',
        'submitted_at',
        'reporting_officer_completed_at',
        'countersigning_officer_completed_at',
        'officer_reviewed_at',
        'accepted_at',
        'rejected_at',
        'finalized_at',
        // Part 1 fields
        'service_number',
        'title',
        'surname',
        'forenames',
        'department_area',
        'cadre',
        'unit',
        'zone',
        'date_of_first_appointment',
        'date_of_present_appointment',
        'rank',
        'hapass',
        'date_of_birth',
        'state_of_origin',
        'qualifications',
        // Part 2 fields
        'sick_leave_records',
        'maternity_leave_records',
        'annual_casual_leave_records',
        'division_targets',
        'individual_targets',
        'project_cost',
        'completion_time',
        'quantity_conformity',
        'quality_conformity',
        'main_duties',
        'joint_discussion',
        'properly_equipped',
        'equipment_difficulties',
        'difficulties_encountered',
        'supervisor_assistance_methods',
        'periodic_review',
        'performance_measure_up',
        'solution_admonition',
        'final_evaluation',
        'adhoc_duties',
        'adhoc_affected_duties',
        'schedule_duty_from',
        'schedule_duty_to',
        'served_under_supervisor',
        // Part 3 fields
        'targets_agreed',
        'other_comments',
        'targets_agreement_details',
        'duties_agreed',
        'duties_agreement_details',
        'job_understanding_grade',
        'job_understanding_comment',
        'knowledge_application_grade',
        'knowledge_application_comment',
        'accomplishment_grade',
        'accomplishment_comment',
        'judgement_grade',
        'judgement_comment',
        'work_speed_accuracy_grade',
        'work_speed_accuracy_comment',
        'written_expression_grade',
        'written_expression_comment',
        'oral_expression_grade',
        'oral_expression_comment',
        'staff_relations_grade',
        'staff_relations_comment',
        'public_relations_grade',
        'public_relations_comment',
        'staff_management_grade',
        'staff_management_comment',
        'quality_of_work_grade',
        'quality_of_work_comment',
        'productivity_grade',
        'productivity_comment',
        'effective_use_of_data_grade',
        'effective_use_of_data_comment',
        'initiative_grade',
        'initiative_comment',
        'dependability_grade',
        'dependability_comment',
        'loyalty_grade',
        'loyalty_comment',
        'honesty_grade',
        'honesty_comment',
        'reliability_under_pressure_grade',
        'reliability_under_pressure_comment',
        'sense_of_responsibility_grade',
        'sense_of_responsibility_comment',
        'appearance_grade',
        'appearance_comment',
        'punctuality_grade',
        'punctuality_comment',
        'attendance_grade',
        'attendance_comment',
        'drive_determination_grade',
        'drive_determination_comment',
        'resource_utilization_grade',
        'resource_utilization_comment',
        'disciplinary_action',
        'disciplinary_action_details',
        'special_commendation',
        'special_commendation_details',
        'encourage_standards_grade',
        'encourage_standards_comment',
        'train_subordinates_grade',
        'train_subordinates_comment',
        'good_example_grade',
        'good_example_comment',
        'suggestions_improvements_grade',
        'suggestions_improvements_comment',
        'training_courses',
        'training_enhanced_performance',
        'satisfactory_jobs',
        'success_failure_causes',
        'training_needs',
        'effective_use_capabilities',
        'better_use_abilities',
        'job_satisfaction',
        'job_satisfaction_causes',
        'overall_assessment',
        'training_needs_assessment',
        'general_remarks',
        'suggest_different_job',
        'different_job_details',
        'hrd_score',
        'hrd_score_notes',
        'hrd_graded_at',
        'hrd_graded_by',
        'suggest_transfer',
        'transfer_details',
        'promotability',
        'officer_comments',
        'officer_signed_at',
        'reporting_officer_declaration',
        'reporting_officer_signed_at',
        'reporting_officer_user_id',
        'countersigning_officer_declaration',
        'countersigning_officer_signed_at',
        'countersigning_officer_user_id',
        'head_of_department_declaration',
        'head_of_department_signed_at',
        'head_of_department_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_rejected' => 'boolean',
            'submitted_at' => 'datetime',
            'reporting_officer_completed_at' => 'datetime',
            'countersigning_officer_completed_at' => 'datetime',
            'officer_reviewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'finalized_at' => 'datetime',
            'date_of_first_appointment' => 'date',
            'date_of_present_appointment' => 'date',
            'date_of_birth' => 'date',
            'schedule_duty_from' => 'date',
            'schedule_duty_to' => 'date',
            'qualifications' => 'array',
            'sick_leave_records' => 'array',
            'maternity_leave_records' => 'array',
            'annual_casual_leave_records' => 'array',
            'division_targets' => 'array',
            'individual_targets' => 'array',
            'training_courses' => 'array',
            'officer_signed_at' => 'datetime',
            'reporting_officer_signed_at' => 'datetime',
            'countersigning_officer_signed_at' => 'datetime',
            'head_of_department_signed_at' => 'datetime',
            'hrd_graded_at' => 'datetime',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function timeline()
    {
        return $this->belongsTo(APERTimeline::class, 'timeline_id');
    }

    public function reportingOfficer()
    {
        return $this->belongsTo(User::class, 'reporting_officer_id');
    }

    public function countersigningOfficer()
    {
        return $this->belongsTo(User::class, 'countersigning_officer_id');
    }

    public function staffOfficer()
    {
        return $this->belongsTo(User::class, 'staff_officer_id');
    }

    public function reportingOfficerUser()
    {
        return $this->belongsTo(User::class, 'reporting_officer_user_id');
    }

    public function countersigningOfficerUser()
    {
        return $this->belongsTo(User::class, 'countersigning_officer_user_id');
    }

    public function headOfDepartment()
    {
        return $this->belongsTo(User::class, 'head_of_department_user_id');
    }

    public function hrdGradedBy()
    {
        return $this->belongsTo(User::class, 'hrd_graded_by');
    }

    // Helper methods
    public function canBeAccessedBy($user)
    {
        // Check if user is the assigned reporting officer
        if ($this->reporting_officer_id === $user->id && $this->status === 'REPORTING_OFFICER') {
            return true;
        }

        // Check if user is the assigned countersigning officer
        if ($this->countersigning_officer_id === $user->id && $this->status === 'COUNTERSIGNING_OFFICER') {
            return true;
        }

        // Check if user is the officer being evaluated
        if ($this->officer->user_id === $user->id && $this->status === 'OFFICER_REVIEW') {
            return true;
        }

        // Check if user is Staff Officer and form is in STAFF_OFFICER_REVIEW status
        if ($this->status === 'STAFF_OFFICER_REVIEW') {
            $userRoles = $user->roles->pluck('name')->toArray();
            if (in_array('Staff Officer', $userRoles)) {
                // Also check same command
                $staffOfficer = $user->officer;
                if ($staffOfficer && $staffOfficer->present_station === $this->officer->present_station) {
                    return true;
                }
            }
        }

        // Check if user is HRD or Staff Officer (for reassignment and viewing)
        $userRoles = $user->roles->pluck('name')->toArray();
        if (in_array('HRD', $userRoles) || in_array('Staff Officer', $userRoles)) {
            return true;
        }

        return false;
    }

    public function canBeReassigned()
    {
        return $this->is_rejected && in_array($this->status, ['REPORTING_OFFICER', 'COUNTERSIGNING_OFFICER', 'STAFF_OFFICER_REVIEW']);
    }

    public function isActiveForYear()
    {
        // Check if there's already an accepted form for this officer and year
        $existingForm = self::where('officer_id', $this->officer_id)
            ->where('year', $this->year)
            ->where('status', 'ACCEPTED')
            ->where('id', '!=', $this->id)
            ->first();

        return !$existingForm;
    }
}

