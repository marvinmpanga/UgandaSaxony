<?php

namespace Database\Factories;

use App\Enums\SectionType;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageSection>
 */
class PageSectionFactory extends Factory
{
    protected $model = PageSection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => SectionType::RichText,
            'content' => [
                'heading' => fake()->sentence(4),
                'body' => fake()->paragraph(3),
            ],
            'settings' => [
                'background' => 'default',
            ],
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
