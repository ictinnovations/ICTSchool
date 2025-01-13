<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $genders = ['Male', 'Female'];
        $bloodgroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $religions = ['Islam', 'Christianity', 'Buddhist', 'Other'];
        
        
        return [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'gender' => $this->faker->randomElement($genders),
            'religion' => $this->faker->randomElement($religions),
            'bloodgroup' => $this->faker->randomElement($bloodgroups),
            'nationality' => 'Pakistani',
            'dob' => $this->faker->date('Y-m-d', '1995-12-31'),
            'photo' => 'none',
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'fatherName' => $this->faker->name('male'),
            'fatherCellNo' => $this->faker->phoneNumber,
            'presentAddress' => $this->faker->address,
            'parmanentAddress' => $this->faker->address,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
