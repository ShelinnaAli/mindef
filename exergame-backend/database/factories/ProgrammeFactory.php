<?php

namespace Database\Factories;

use App\Models\GameType;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Programme>
 */
class ProgrammeFactory extends Factory
{
    protected $model = Programme::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_id' => GameType::factory(),
            'created_by' => User::factory(),
            'name' => $this->faker->words(3, true).' Programme',
            'synopsis' => $this->faker->paragraph(),
            'cover_image' => $this->faker->imageUrl(600, 400, 'sports'),
            'intensity_level' => $this->faker->randomElement(['low', 'medium', 'high', 'extreme']),
            'session_type' => $this->faker->randomElement(['single', 'group']),
            'max_participants' => $this->faker->numberBetween(1, 20),
            'duration_minutes' => $this->faker->randomElement([15, 30, 45, 60, 90, 120]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the programme is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific duration for the programme.
     */
    public function withDuration(int $minutes): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => $minutes,
        ]);
    }

    /**
     * Set a specific intensity level.
     */
    public function withIntensity(string $level): static
    {
        return $this->state(fn (array $attributes) => [
            'intensity_level' => $level,
        ]);
    }

    /**
     * Set session type to single.
     */
    public function single(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_type' => 'single',
            'max_participants' => 1,
        ]);
    }

    /**
     * Set session type to group.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_type' => 'group',
            'max_participants' => $this->faker->numberBetween(5, 20),
        ]);
    }
}
