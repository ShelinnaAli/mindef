<?php

namespace Database\Factories;

use App\Models\GameType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameType>
 */
class GameTypeFactory extends Factory
{
    protected $model = GameType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gameTypes = [
            'Cardio', 'Strength', 'Flexibility', 'Endurance',
            'Balance', 'Coordination', 'Agility', 'Speed',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($gameTypes),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the game type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
