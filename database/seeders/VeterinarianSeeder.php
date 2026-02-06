<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Veterinarian;
use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VeterinarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specializations
        $specializations = [
            ['name' => 'Small Animal Medicine', 'description' => 'Treatment of dogs, cats, and small pets'],
            ['name' => 'Large Animal Medicine', 'description' => 'Treatment of livestock and farm animals'],
            ['name' => 'Avian Medicine', 'description' => 'Treatment of birds and poultry'],
            ['name' => 'Surgery', 'description' => 'Surgical procedures for all animals'],
            ['name' => 'Dentistry', 'description' => 'Dental care for animals'],
            ['name' => 'Emergency Care', 'description' => 'Emergency and critical care'],
        ];

        foreach ($specializations as $spec) {
            Specialization::firstOrCreate(['name' => $spec['name']], $spec);
        }

        // Create sample veterinarians
        $veterinarians = [
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@vetcare.com',
                'license_number' => 'VET001',
                'experience_years' => 10,
                'bio' => 'Experienced veterinarian specializing in small animals and emergency care. Passionate about providing compassionate care to all pets.',
                'consultation_fee' => 500.00,
                'specializations' => ['Small Animal Medicine', 'Emergency Care'],
            ],
            [
                'name' => 'Dr. Michael Chen',
                'email' => 'michael.chen@vetcare.com',
                'license_number' => 'VET002',
                'experience_years' => 15,
                'bio' => 'Expert in large animal medicine with extensive experience in cattle, goats, and sheep. Serving farming communities for over 15 years.',
                'consultation_fee' => 600.00,
                'specializations' => ['Large Animal Medicine', 'Surgery'],
            ],
            [
                'name' => 'Dr. Emily Rodriguez',
                'email' => 'emily.rodriguez@vetcare.com',
                'license_number' => 'VET003',
                'experience_years' => 8,
                'bio' => 'Specialist in avian medicine and exotic pets. Dedicated to the health and wellbeing of birds and poultry.',
                'consultation_fee' => 550.00,
                'specializations' => ['Avian Medicine', 'Small Animal Medicine'],
            ],
            [
                'name' => 'Dr. Ahmed Hassan',
                'email' => 'ahmed.hassan@vetcare.com',
                'license_number' => 'VET004',
                'experience_years' => 12,
                'bio' => 'Skilled surgeon with expertise in both small and large animals. Committed to providing the highest quality surgical care.',
                'consultation_fee' => 700.00,
                'specializations' => ['Surgery', 'Large Animal Medicine'],
            ],
            [
                'name' => 'Dr. Lisa Thompson',
                'email' => 'lisa.thompson@vetcare.com',
                'license_number' => 'VET005',
                'experience_years' => 6,
                'bio' => 'Dental specialist for animals. Providing comprehensive dental care to keep your animals healthy and happy.',
                'consultation_fee' => 450.00,
                'specializations' => ['Dentistry', 'Small Animal Medicine'],
            ],
        ];

        foreach ($veterinarians as $vetData) {
            // Create user
            $user = User::firstOrCreate(
                ['email' => $vetData['email']],
                [
                    'name' => $vetData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'veterinarian',
                ]
            );

            // Create veterinarian profile
            $vet = Veterinarian::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'license_number' => $vetData['license_number'],
                    'experience_years' => $vetData['experience_years'],
                    'bio' => $vetData['bio'],
                    'consultation_fee' => $vetData['consultation_fee'],
                ]
            );

            // Attach specializations
            $specIds = Specialization::whereIn('name', $vetData['specializations'])->pluck('id');
            $vet->specializations()->sync($specIds);
        }

        $this->command->info('Veterinarians and specializations seeded successfully!');
    }
}
