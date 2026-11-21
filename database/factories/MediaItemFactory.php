<?php

namespace Database\Factories;

use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    protected $model = MediaItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->uuid().'.jpg';

        return [
            'folder_id' => null,
            'collection' => 'general',
            'disk' => 'public',
            'path' => 'media/'.$fileName,
            'file_name' => $fileName,
            'original_name' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10240, 204800),
            'width' => 1200,
            'height' => 800,
            'alt_text' => fake()->sentence(4),
            'caption' => fake()->sentence(),
            'uploaded_by' => User::factory(),
        ];
    }
}
