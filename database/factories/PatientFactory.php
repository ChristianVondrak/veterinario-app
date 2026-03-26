<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name'                => $this->faker->firstName(),
            'species'             => 'canino',
            'breed'               => 'Mestizo',
            'sex'                 => $this->faker->randomElement(['male', 'female']),
            'reproductive_status' => 'castrada',
            'birth_date'          => $this->faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
        ];
    }
}
