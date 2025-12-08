<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alert;
use App\Models\LocationHistory;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Define Realistic Hotspots within ISU Echague 
        // Based on your bounding box: 16°43'25.9"N to 16°43'00.9"N
        $hotspots = [
            [
                'name' => 'Main Gate / Highway Area',
                'lat' => 16.7236, 
                'lng' => 121.6888,
                'common_types' => ['accident', 'harassment']
            ],
            [
                'name' => 'Admin / Front Academic Buildings',
                'lat' => 16.7225, 
                'lng' => 121.6895,
                'common_types' => ['medical', 'fire']
            ],
            [
                'name' => 'CICS / Engineering Complex (Middle)',
                'lat' => 16.7210, 
                'lng' => 121.6910,
                'common_types' => ['medical', 'fire', 'natural_disaster']
            ],
            [
                'name' => 'Oval / Grandstand / Dorms (Rear)',
                'lat' => 16.7185, 
                'lng' => 121.6925,
                'common_types' => ['harassment', 'natural_disaster', 'medical']
            ]
        ];

        $severities = ['mild', 'moderate', 'severe'];
        
        // 2. Generate 30 Alerts
        for ($i = 0; $i < 30; $i++) {
            
            // Pick a random student ID (2-6)
            $studentId = rand(2, 6);
            $student = User::find($studentId);

            // Safety check in case user doesn't exist in your DB yet
            if (!$student) continue; 

            // Pick a random hotspot
            $spot = $hotspots[array_rand($hotspots)];

            // Add "Jitter" to coordinates so they aren't all on the exact same pixel
            // 0.0001 is roughly 11 meters. This simulates different rooms/spots near the building.
            $lat = $spot['lat'] + (rand(-15, 15) / 10000); 
            $lng = $spot['lng'] + (rand(-15, 15) / 10000);

            // Pick category based on hotspot preference (70% chance) or random (30%)
            if (rand(1, 100) <= 70) {
                $category = $spot['common_types'][array_rand($spot['common_types'])];
            } else {
                $category = fake()->randomElement(['medical', 'fire', 'harassment', 'accident', 'natural_disaster']);
            }

            // Determine Severity based on category logic
            if ($category === 'fire' || $category === 'natural_disaster') {
                $severity = 'severe';
            } elseif ($category === 'accident') {
                $severity = fake()->randomElement(['moderate', 'severe']);
            } else {
                $severity = fake()->randomElement(['mild', 'moderate', 'severe']);
            }

            // Create timestamp (spread over the last 7 days)
            $createdAt = Carbon::now()->subDays(rand(0, 7))->subHours(rand(1, 12));

            // Create the Alert
            $alert = Alert::create([
                'student_id'    => $student->id,
                'student_name'  => $student->name,
                'student_phone' => $student->phone_number ?? '09123456789',
                'category'      => $category,
                'severity'      => $severity,
                'description'   => "Test alert near {$spot['name']}. Please send help immediately.",
                'latitude'      => $lat,
                'longitude'     => $lng,
                'status'        => fake()->randomElement(['pending', 'accepted', 'resolved']), // Mix of statuses
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ]);

            // Create Location History (Required by your app logic)
            LocationHistory::create([
                'alert_id'  => $alert->id,
                'latitude'  => $lat,
                'longitude' => $lng,
                'created_at'=> $createdAt,
            ]);

            // Simulate Notifications (Optional, but good for realism)
            // Assuming Admin is ID 1
            Notification::create([
                'user_id'  => 1, // Admin
                'alert_id' => $alert->id,
                'title'    => 'EMERGENCY ALERT',
                'message'  => "{$student->name} needs help! Severity: {$severity}",
                'type'     => 'alert',
                'created_at' => $createdAt,
            ]);
        }
    }
}