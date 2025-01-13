<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Student::class;
    public function definition(): array
    {


        $genders = ['Male', 'Female'];
        $bloodgroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $religions = ['Islam', 'Christianity', 'Hinduism', 'Other'];
        $sections = ['1', '2', '3', '4', '5'];
        $shifts = ['Morning', 'Evening'];

        return [
            'regiNo' => $this->faker->unique()->numberBetween(100000, 999999),
            'rollNo' => $this->faker->unique()->numberBetween(1, 100),
            'session' => '1',
            'class' => $this->faker->randomElement(['cl8', 'cl9', 'cl10']),
            'group' => 'All',
            'section' => $this->faker->randomElement($sections),
            'discount_id' => $this->faker->numberBetween(100, 500),
            'shift' => $this->faker->randomElement($shifts),
            'firstName' => $this->faker->firstName,
            'middleName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'gender' => $this->faker->randomElement($genders),
            'religion' => $this->faker->randomElement($religions),
            'bloodgroup' => $this->faker->randomElement($bloodgroups),
            'nationality' => 'Pakistani',
            'dob' => $this->faker->date('Y-m-d', '2018-12-31'),
            'photo' => 'none',
            'extraActivity' => $this->faker->sentence,
            'remarks' => 'Brilliant Student',
            'fatherName' => $this->faker->name('male'),
            'fatherCellNo' => $this->faker->phoneNumber,
            'motherName' => $this->faker->name('female'),
            'motherCellNo' => $this->faker->phoneNumber,
            'localGuardian' => $this->faker->name,
            'localGuardianCell' => $this->faker->phoneNumber,
            'presentAddress' => $this->faker->address,
            'parmanentAddress' => $this->faker->address,
            'isActive' => 'Yes',
            'created_at' => now(),
            'updated_at' => now(),
            'b_form' => $this->faker->unique()->numerify('#####-#######-#'),
            'family_id' => $this->faker->randomDigitNotNull,
            'about_family' => $this->faker->sentence,
        ];
    }
}
