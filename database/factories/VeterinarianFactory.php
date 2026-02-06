<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Veterinarian>
 */
class VeterinarianFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Veterinarian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'veterinarian'])->id,
            'license_number' => 'VET-' . $this->faker->unique()->numerify('######'),
            'experience_years' => $this->faker->numberBetween(1, 30),
            'bio' => $this->faker->paragraph(),
            'consultation_fee' => $this->faker->randomFloat(2, 200, 2000),
            'profile_image' => null,
        ];
    }
}
