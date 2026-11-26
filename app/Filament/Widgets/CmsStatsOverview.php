<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\GalleryImageResource;
use App\Filament\Resources\PartnerResource;
use App\Filament\Resources\ResourceResource;
use App\Filament\Resources\SiteStatResource;
use App\Models\Article;
use App\Models\GalleryImage;
use App\Models\MediaItem;
use App\Models\Partner;
use App\Models\ResourceItem;
use App\Models\SiteStat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CmsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('pages.view') || $user->can('media.view') || $user->can('settings.manage');
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user?->can('pages.view')) {
            $publishedArticles = Article::published()->count();
            $totalArticles = Article::query()->count();

            $activePartners = Partner::active()->count();
            $totalPartners = Partner::query()->count();

            $publishedResources = ResourceItem::published()->count();
            $totalResources = ResourceItem::query()->count();

            $stats[] = Stat::make('Events & News', "{$publishedArticles} published")
                ->description("{$totalArticles} total · Live on Hero & Events")
                ->icon('heroicon-o-newspaper')
                ->color('primary')
                ->url(ArticleResource::getUrl('index'));

            $stats[] = Stat::make('Partnerships', "{$activePartners} active")
                ->description("{$totalPartners} total schools & CSOs")
                ->icon('heroicon-o-building-office-2')
                ->color('success')
                ->url(PartnerResource::getUrl('index'));

            $stats[] = Stat::make('Resource Centre', "{$publishedResources} published")
                ->description("{$totalResources} total guides & documents")
                ->icon('heroicon-o-book-open')
                ->color('info')
                ->url(ResourceResource::getUrl('index'));
        }

        if ($user?->can('media.view')) {
            $visibleGallery = GalleryImage::visible()->count();
            $totalMedia = MediaItem::query()->count();

            $stats[] = Stat::make('Gallery & Media', $visibleGallery)
                ->description("{$visibleGallery} gallery photos · {$totalMedia} library files")
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->url(GalleryImageResource::getUrl('index'));
        }

        if ($user?->can('settings.manage')) {
            $stats[] = Stat::make('Homepage Stats', SiteStat::query()->count())
                ->description('Key figures on homepage bar')
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->url(SiteStatResource::getUrl('index'));
        }

        return $stats;
    }
}
