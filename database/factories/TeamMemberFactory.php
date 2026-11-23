<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'role' => $this->faker->jobTitle(),
            'photo_path' => null,
            'email' => $this->faker->safeEmail(),
            'phones' => [$this->faker->phoneNumber()],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
