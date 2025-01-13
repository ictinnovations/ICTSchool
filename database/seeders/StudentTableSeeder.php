<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ['cl8', 'cl9', 'cl10'];

        foreach ($classes as $class) {
            Student::factory()
                ->count(30)
                ->state([
                    'class' => $class,
                    'session' => '1'
                ])
                ->create();
        }
    }
}
