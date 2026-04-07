<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomTypes = ['Studio', 'Hall', 'Room', 'Court', 'Arena'];
        $roomNames = ['A', 'B', 'C', 'Main', 'Training', 'Fitness', 'Yoga', 'Dance'];

        return [
            'name' => $this->faker->randomElement($roomTypes).' '.$this->faker->randomElement($roomNames),
            'description' => $this->faker->optional()->sentence(),
            'capacity' => $this->faker->numberBetween(5, 50),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the room is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific capacity for the room.
     */
    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity,
        ]);
    }

    /**
     * Create a small room (capacity <= 10).
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Create a large room (capacity >= 30).
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $this->faker->numberBetween(30, 100),
        ]);
    }
}
