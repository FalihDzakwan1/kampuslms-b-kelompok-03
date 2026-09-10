<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;


    public function definition(): array
    {
        return [
            'code' => 'SI' . fake()->unique()->numerify('#######'),

            'name' => fake()->randomElement([
                'Basis Data',
                'Pemrograman Web',
                'Jaringan Komputer'
            ]),

            'description' => fake()->sentence(),

            'sks' => fake()->numberBetween(2, 4),

            'lecturer_id' => User::where('role', 'dosen')
                                 ->inRandomOrder()
                                 ->first()
                                 ->id,

            'status' => 'active',
        ];
    }
}