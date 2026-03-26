<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'patient_id'            => Patient::factory(),
            'evaluated_at'          => now(),
            'weight_kg'             => $this->faker->randomFloat(2, 3, 40),
            'bcs'                   => $this->faker->numberBetween(1, 5),
            'iris_stage'            => $this->faker->randomElement(['I', 'II', 'III', 'IV']),
            'creatinine'            => $this->faker->randomFloat(2, 1.0, 8.0),
            'bun'                   => $this->faker->randomFloat(2, 10, 120),
            'phosphorus'            => $this->faker->randomFloat(2, 2.0, 7.0),
            'potassium'             => $this->faker->randomFloat(2, 3.0, 6.0),
            'sodium'                => $this->faker->randomFloat(2, 135, 155),
            'urine_density'         => $this->faker->randomFloat(3, 1.001, 1.040),
            'proteinuria'           => 'unknown',
            'appetite'              => 'normal',
            'activity_level'        => 'medium',
            'special_considerations'=> null,
        ];
    }
}
