<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => ContentStatus::Draft,
            'is_homepage' => false,
            'show_in_nav' => true,
            'nav_order' => fake()->numberBetween(1, 20),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'published_at' => null,
            'scheduled_publish_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
            'published_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function homepage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_homepage' => true,
            'slug' => 'home',
        ]);
    }
}
