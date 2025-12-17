<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index()
    {
        // Define all system settings with their defaults
        $settingsConfig = [
            'retirement_age' => [
                'label' => 'Retirement Age',
                'description' => 'Age at which officers are eligible for retirement (in years)',
                'type' => 'number',
                'default' => '60',
                'min' => 50,
                'max' => 70,
            ],
            'retirement_years_of_service' => [
                'label' => 'Years of Service for Retirement',
                'description' => 'Number of years in service required for retirement eligibility',
                'type' => 'number',
                'default' => '35',
                'min' => 20,
                'max' => 50,
            ],
            'pre_retirement_leave_months' => [
                'label' => 'Pre-Retirement Leave (Months)',
                'description' => 'Number of months before retirement date when pre-retirement leave begins',
                'type' => 'number',
                'default' => '3',
                'min' => 1,
                'max' => 12,
            ],
            'annual_leave_days_gl07_below' => [
                'label' => 'Annual Leave Days (GL 07 and Below)',
                'description' => 'Number of annual leave days for officers at Grade Level 07 and below',
                'type' => 'number',
                'default' => '28',
                'min' => 1,
                'max' => 60,
            ],
            'annual_leave_days_gl08_above' => [
                'label' => 'Annual Leave Days (Level 08 and Above)',
                'description' => 'Number of annual leave days for officers at Level 08 and above',
                'type' => 'number',
                'default' => '30',
                'min' => 1,
                'max' => 60,
            ],
            'annual_leave_max_applications' => [
                'label' => 'Annual Leave Max Applications',
                'description' => 'Maximum number of times annual leave can be applied per year',
                'type' => 'number',
                'default' => '2',
                'min' => 1,
                'max' => 10,
            ],
            'pass_max_days' => [
                'label' => 'Pass Maximum Days',
                'description' => 'Maximum number of days allowed for pass applications',
                'type' => 'number',
                'default' => '5',
                'min' => 1,
                'max' => 30,
            ],
            'rsa_pin_prefix' => [
                'label' => 'RSA PIN Prefix',
                'description' => 'Prefix for Retirement Savings Account PIN (e.g., PEN)',
                'type' => 'text',
                'default' => 'PEN',
            ],
            'rsa_pin_length' => [
                'label' => 'RSA PIN Length',
                'description' => 'Expected length of RSA PIN (excluding prefix)',
                'type' => 'number',
                'default' => '12',
                'min' => 8,
                'max' => 20,
            ],
        ];

        // Get existing settings
        $settings = SystemSetting::whereIn('setting_key', array_keys($settingsConfig))->get()->keyBy('setting_key');
        
        // Merge with defaults for settings that don't exist
        foreach ($settingsConfig as $key => $config) {
            if (!$settings->has($key)) {
                $settings[$key] = new SystemSetting([
                    'setting_key' => $key,
                    'setting_value' => $config['default'],
                    'description' => $config['description'],
                ]);
            } else {
                $settings[$key]->label = $config['label'];
                $settings[$key]->type = $config['type'];
                $settings[$key]->min = $config['min'] ?? null;
                $settings[$key]->max = $config['max'] ?? null;
            }
        }

        return view('dashboards.hrd.system-settings', compact('settings', 'settingsConfig'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|string|max:255',
        ]);

        try {
            foreach ($validated['settings'] as $key => $value) {
                $setting = SystemSetting::firstOrNew(['setting_key' => $key]);
                $setting->setting_value = $value;
                $setting->updated_by = auth()->id();
                
                // Update description if it doesn't exist
                if (empty($setting->description)) {
                    $descriptions = [
                        'retirement_age' => 'Age at which officers are eligible for retirement (in years)',
                        'retirement_years_of_service' => 'Number of years in service required for retirement eligibility',
                        'pre_retirement_leave_months' => 'Number of months before retirement date when pre-retirement leave begins',
                        'annual_leave_days_gl07_below' => 'Number of annual leave days for officers at Grade Level 07 and below',
                        'annual_leave_days_gl08_above' => 'Number of annual leave days for officers at Level 08 and above',
                        'annual_leave_max_applications' => 'Maximum number of times annual leave can be applied per year',
                        'pass_max_days' => 'Maximum number of days allowed for pass applications',
                        'rsa_pin_prefix' => 'Prefix for Retirement Savings Account PIN (e.g., PEN)',
                        'rsa_pin_length' => 'Expected length of RSA PIN (excluding prefix)',
                    ];
                    $setting->description = $descriptions[$key] ?? '';
                }
                
                $setting->save();
            }

            return redirect()->route('hrd.system-settings')
                ->with('success', 'System settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}

