<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use App\Models\User;
use App\Services\WebpImageService;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@ugandasaxony.org')->value('id');
        $webp = app(WebpImageService::class);

        $members = [
            [
                'name' => 'Nakato Margaret',
                'role' => 'Project Coordinator',
                'legacy_image' => 'person-nakato.jpg',
                'email' => 'nakato@katosi.org',
                'phones' => ['+256 772 587 427', '+256 754 587 427'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Fiona Mugisha Okwere',
                'role' => 'Project Officer',
                'legacy_image' => 'person-fiona.png',
                'email' => 'fiona.okwere@katosi.org',
                'phones' => ['+256 755 330 525', '+256 774 351 195'],
                'sort_order' => 2,
            ],
        ];

        foreach ($members as $data) {
            $source = public_path('images/legacy/'.$data['legacy_image']);
            $targetRelative = 'team/'.pathinfo($data['legacy_image'], PATHINFO_FILENAME).'.webp';
            $photoPath = $webp->storeLocalFile($source, $targetRelative, 'public')
                ?? '/images/legacy/'.$data['legacy_image'];

            TeamMember::firstOrCreate(
                ['name' => $data['name']],
                [
                    'role' => $data['role'],
                    'photo_path' => $photoPath,
                    'email' => $data['email'],
                    'phones' => $data['phones'],
                    'is_active' => true,
                    'sort_order' => $data['sort_order'],
                    'created_by' => $adminId,
                ]
            );
        }
    }
}
