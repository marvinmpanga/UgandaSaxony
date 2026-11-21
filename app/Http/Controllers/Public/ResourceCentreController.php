<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ResourceItem;
use Inertia\Inertia;
use Inertia\Response;

class ResourceCentreController extends Controller
{
    public function index(): Response
    {
        $resources = ResourceItem::query()
            ->published()
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get()
            ->map(fn (ResourceItem $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'category' => $item->category,
                'excerpt' => $item->excerpt,
                'cover_image' => $item->cover_image_url,
                'has_attachment' => filled($item->attachment_path),
                'published_at' => $item->published_at?->format('d M Y'),
            ])
            ->values();

        $categories = $resources
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();

        return Inertia::render('public/ResourceCentreIndex', [
            'resources' => $resources,
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): Response
    {
        $resource = ResourceItem::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = ResourceItem::query()
            ->published()
            ->where('id', '!=', $resource->id)
            ->when($resource->category, fn ($q) => $q->where('category', $resource->category))
            ->orderBy('sort_order')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (ResourceItem $item): array => [
                'title' => $item->title,
                'slug' => $item->slug,
                'category' => $item->category,
                'excerpt' => $item->excerpt,
                'cover_image' => $item->cover_image_url,
                'published_at' => $item->published_at?->format('d M Y'),
            ])
            ->values();

        return Inertia::render('public/ResourceShow', [
            'resource' => [
                'title' => $resource->title,
                'slug' => $resource->slug,
                'category' => $resource->category,
                'excerpt' => $resource->excerpt,
                'body' => $resource->body,
                'cover_image' => $resource->cover_image_url,
                'attachment_url' => $resource->attachment_url,
                'published_at' => $resource->published_at?->format('d M Y'),
            ],
            'related' => $related,
            'editUrl' => "/admin/resource-centre/{$resource->id}/edit",
        ]);
    }
}
