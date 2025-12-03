<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

// Import your models
use App\Models\User;
use App\Models\StudentDetails;
use App\Models\ResponderDetails;
use App\Models\Alert;
use App\Models\LocationHistory;
use App\Models\Notification;
use App\Models\ActivityLog;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $password = Hash::make('password123'); // Common password for everyone

        // ---------------------------------------------------------
        // 1. CREATE ADMIN (1 Row)
        // ---------------------------------------------------------
        User::create([
            'name'         => 'Super Admin',
            'login_id'     => 'admin@ialert.com',
            'password'     => $password,
            'role'         => 'admin',
            'status'       => 'active',
            'phone_number' => '09123456789',
        ]);
        $this->command->info('✅ Admin created');

        // ---------------------------------------------------------
        // 2. CREATE STUDENTS (5 Rows)
        // ---------------------------------------------------------
        $studentIds = []; // Keep track of IDs to use for Alerts later

        for ($i = 1; $i <= 5; $i++) {
            $student = User::create([
                'name'         => $faker->name,
                'login_id'     => '2025-000' . $i, // Easy IDs: 2025-0001, 2025-0002...
                'password'     => $password,
                'role'         => 'student',
                'status'       => 'active',
                'phone_number' => $faker->phoneNumber,
            ]);

            // Create Details for this student
            StudentDetails::create([
                'user_id'    => $student->id,
                'department' => $faker->randomElement(['IT', 'CS', 'Nursing', 'Engineering']),
                'year_level' => $faker->numberBetween(1, 4),
            ]);

            // Create a Notification for this student
            Notification::create([
                'user_id' => $student->id,
                'title'   => 'Welcome to iAlert',
                'message' => 'Please update your profile information.',
                'type'    => 'info',
            ]);

            // Create an Activity Log for this student
            ActivityLog::create([
                'user_id' => $student->id,
                'action'  => 'LOGIN',
                'details' => 'First time login via Mobile App',
            ]);

            $studentIds[] = $student->id;
        }
        $this->command->info('✅ 5 Students created (with details, logs, notifications)');

        // ---------------------------------------------------------
        // 3. CREATE RESPONDERS (5 Rows)
        // ---------------------------------------------------------
        for ($i = 1; $i <= 5; $i++) {
            $responder = User::create([
                'name'         => $faker->name,
                'login_id'     => 'responder' . $i . '@ialert.com',
                'password'     => $password,
                'role'         => 'responder',
                'status'       => 'active',
                'phone_number' => $faker->phoneNumber,
            ]);

            // Create Details for this responder
            ResponderDetails::create([
                'user_id'  => $responder->id,
                'position' => $faker->randomElement(['Nurse', 'Security Guard', 'Rescue Team']),
            ]);
        }
        $this->command->info('✅ 5 Responders created');

        // ---------------------------------------------------------
        // 4. CREATE ALERTS (5 Rows) & HISTORY
        // ---------------------------------------------------------
        // We link these alerts to the students we created above
        foreach ($studentIds as $studentId) {
            $student = User::find($studentId);

            $alert = Alert::create([
                'student_id'    => $student->id,
                'student_name'  => $student->name,
                'student_phone' => $student->phone_number,
                'category'      => $faker->randomElement(['Medical', 'Fire', 'Harassment']),
                'severity'      => $faker->randomElement(['mild', 'moderate', 'severe']),
                'description'   => $faker->sentence,
                'latitude'      => 14.5995 + ($faker->randomFloat(4, -0.01, 0.01)), // Near Manila
                'longitude'     => 120.9842 + ($faker->randomFloat(4, -0.01, 0.01)),
                'status'        => 'pending',
            ]);

            // Create 5 History points for this alert
            for ($k = 0; $k < 5; $k++) {
                LocationHistory::create([
                    'alert_id'  => $alert->id,
                    'latitude'  => $alert->latitude + ($faker->randomFloat(5, -0.001, 0.001)), // Slightly moved
                    'longitude' => $alert->longitude + ($faker->randomFloat(5, -0.001, 0.001)),
                ]);
            }
        }
        $this->command->info('✅ 5 Alerts created (with location history)');
    }
}