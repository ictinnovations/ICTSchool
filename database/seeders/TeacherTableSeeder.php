<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;


class TeacherTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        DB::transaction(function () use ($faker) {
            Teacher::factory()->count(15)->create()->each(function ($teacher) use ($faker) {
                User::create([
                    'firstname' => $teacher->firstName,
                    'lastname' => $teacher->lastName,
                    'desc' => 'Teacher',
                    'login' => $teacher->email,
                    'email' => $teacher->email,
                    'group' => 'teacher',
                    'password' => bcrypt('password'), // Default password
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'group_id' => $teacher->id, // Using teacher's ID
                    'access' => 'all', // Adjust as necessary
                    'regiNo' => $teacher->regiNo ?? $faker->unique()->numberBetween(100000, 999999), // Fallback if regiNo is not set
                    'phone' => $teacher->phone,
                ]);
            });
        });
    }
}
