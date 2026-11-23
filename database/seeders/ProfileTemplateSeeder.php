<?php

namespace Database\Seeders;

use App\Models\ProfileTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProfileTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@ugandasaxony.org')->value('id');

        $sourceDocx = public_path('documents/UPDATED-TEMPLATE-FOR-ORGANISATION-PROFILE.docx');
        $storedDocx = 'templates/UPDATED-TEMPLATE-FOR-ORGANISATION-PROFILE.docx';

        if (is_file($sourceDocx) && ! Storage::disk('public')->exists($storedDocx)) {
            Storage::disk('public')->put($storedDocx, (string) file_get_contents($sourceDocx));
        }

        $templates = [
            [
                'title' => 'Template for Uganda-Saxony Organisation Profile',
                'description' => 'For civil society organisations and other institutions. Fill it in and send it to the coordination team.',
                'link_label' => 'Download (DOCX)',
                'file_path' => $storedDocx,
                'external_url' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Template for School Partnership Profile',
                'description' => 'For primary, secondary and vocational schools looking for a partner school in Saxony.',
                'link_label' => 'Download (PDF)',
                'file_path' => null,
                'external_url' => 'https://uganda-saxonypartnership.org/cms/wp-content/uploads/2024/05/Template-for-School-Partnership-Profile.pdf',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($templates as $data) {
            ProfileTemplate::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['created_by' => $adminId])
            );
        }
    }
}
