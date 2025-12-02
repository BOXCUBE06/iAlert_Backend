<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Medical', 'Fire', 'Violence', 'Accident', 'Harassment'];
        $severities = ['severe', 'moderate', 'mild'];
        $statuses = ['pending', 'responded', 'resolved', 'cancelled'];

        // ISU Echague approximate coordinate center  
        $baseLat = 16.7060;
        $baseLng = 121.6775;

        for ($i = 1; $i <= 50; $i++) {
            DB::table('alerts')->insert([
                'student_id'   => rand(1, 10), // assumes 10 students exist
                'category'     => $categories[array_rand($categories)],
                'severity'     => $severities[array_rand($severities)],
                'status'       => $statuses[array_rand($statuses)],
                'description'  => "Dummy alert report #" . $i,

                // random small deviations to stay inside ISU campus area
                'latitude'     => $baseLat + ((rand(-50, 50)) / 10000),
                'longitude'    => $baseLng + ((rand(-50, 50)) / 10000),

                'created_at'   => Carbon::now()->subMinutes(rand(1, 5000)),
                'updated_at'   => Carbon::now(),

                // 30% chance it has response and resolution timestamps
                'responded_at' => rand(0, 10) > 7 ? Carbon::now()->subMinutes(rand(1, 3000)) : null,
                'resolved_at'  => rand(0, 10) > 8 ? Carbon::now()->subMinutes(rand(1, 2000)) : null,
            ]);
        }
    }
}
