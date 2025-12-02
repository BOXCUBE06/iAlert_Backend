<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            Student::create([
                'student_id'  => 'STU-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name'        => "Student $i",
                'password'    => Hash::make('password123'),
                'phone_number'=> '09' . rand(100000000, 999999999),
                'department'  => ['CCSICT', 'CAS', 'COE', 'CED'][array_rand(['CCSICT', 'CAS', 'COE', 'CED'])],
                'year_level'  => rand(1, 4),
                'status'      => ['active','inactive'][rand(0,1)],
            ]);
        }
    }
}
