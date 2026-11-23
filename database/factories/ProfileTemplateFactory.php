<?php

namespace Database\Factories;

use App\Models\ProfileTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileTemplate>
 */
class ProfileTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'link_label' => 'Download (DOCX)',
            'file_path' => null,
            'external_url' => $this->faker->url(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
