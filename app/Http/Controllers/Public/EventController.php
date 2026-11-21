<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Return a paginated JSON batch of published events ordered latest-first for infinite scroll.
     */
    public function feed(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 6), 1), 24);

        $paginator = Article::query()
            ->published()
            ->orderByRaw('COALESCE(event_date, published_at) DESC')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $items = $paginator->getCollection()->map(fn (Article $article): array => [
            'title' => $article->title,
            'slug' => $article->slug,
            'tag' => $article->tag,
            'date' => $article->event_date?->format('d M Y') ?? $article->published_at?->format('d M Y'),
            'location' => $article->location,
            'excerpt' => $article->excerpt,
            'image_url' => $article->cover_image_url,
        ])->values()->all();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Render a dedicated read page for a single article/event by slug.
     */
    public function show(string $slug): Response
    {
        $article = Article::published()
            ->with('images')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return Inertia::render('public/EventShow', [
            'event' => [
                'title' => $article->title,
                'slug' => $article->slug,
                'tag' => $article->tag,
                'date' => $article->event_date?->format('d M Y') ?? $article->published_at?->format('d M Y'),
                'location' => $article->location,
                'excerpt' => $article->excerpt,
                'body' => $article->body,
                'image_url' => $article->cover_image_url,
                'gallery' => $article->images->map(fn (ArticleImage $img): array => [
                    'url' => $img->url,
                    'caption' => $img->caption,
                ])->values()->all(),
            ],
            'relatedEvents' => $related->map(fn (Article $a): array => [
                'title' => $a->title,
                'slug' => $a->slug,
                'tag' => $a->tag,
                'date' => $a->event_date?->format('d M Y') ?? $a->published_at?->format('d M Y'),
                'excerpt' => $a->excerpt,
                'image_url' => $a->cover_image_url,
            ])->values()->all(),
            'editUrl' => "/admin/articles/{$article->id}/edit",
        ]);
    }
}
