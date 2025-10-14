<?php

namespace Database\Factories;

use App\Models\Record;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordFactory extends Factory
{
  protected $model = Record::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'weight' => $this->faker->randomFloat(1, 40, 100),
            'sleep_hours' => $this->faker->numberBetween(4, 10),
            'sleep_minutes' => $this->faker->numberBetween(0, 59),
            'meals' => ['朝食', '昼食', '夕食'],
            'meal_detail' => $this->faker->sentence(),
            'meal_photos' => ['photo1.jpg', 'photo2.jpg'],
            'exercises' => ['ランニング', '筋トレ'],
            'exercise_detail' => $this->faker->sentence(),
        ];
    }

}
