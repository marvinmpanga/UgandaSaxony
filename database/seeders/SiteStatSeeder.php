<?php

namespace Database\Seeders;

use App\Models\SiteStat;
use Illuminate\Database\Seeder;

class SiteStatSeeder extends Seeder
{
    /**
     * Seed the default homepage statistics bar.
     */
    public function run(): void
    {
        $stats = [
            [
                'key' => 'countries',
                'value' => '2',
                'label' => 'Countries: Uganda & Saxony',
                'sort_order' => 1,
            ],
            [
                'key' => 'partner_schools',
                'value' => '6',
                'label' => 'Partner schools profiled',
                'sort_order' => 2,
            ],
            [
                'key' => 'women_organised',
                'value' => '1,050',
                'label' => 'Women organised by KWDT',
                'sort_order' => 3,
            ],
            [
                'key' => 'sdg',
                'value' => '17',
                'label' => 'Sustainable Development Goal',
                'sort_order' => 4,
            ],
        ];

        foreach ($stats as $stat) {
            SiteStat::firstOrCreate(
                ['key' => $stat['key']],
                $stat
            );
        }
    }
}
