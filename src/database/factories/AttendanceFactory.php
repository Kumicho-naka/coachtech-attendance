<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-30 days', 'now');
        $startTime = Carbon::instance($date)->setTime(9, 0);
        $endTime = Carbon::instance($date)->setTime(18, 0);

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'finished',
            'remarks' => null,
        ];
    }
}
