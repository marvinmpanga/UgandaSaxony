<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\GalleryImage;
use App\Models\MediaFolder;
use App\Models\MediaItem;
use App\Models\Partner;
use App\Models\ResourceItem;
use App\Models\User;
use App\Services\WebpImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds the Filament-managed dynamic tables (Articles, Partners, GalleryImages, ResourceItems,
 * MediaFolders, MediaItems) with WebP images stored on the public disk so every image renders
 * natively in Filament tables, FileUpload edit previews, Media Library, and the public website.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@ugandasaxony.org')->value('id');
        $webp = app(WebpImageService::class);

        $this->seedArticles($adminId, $webp);
        $this->seedPartners($adminId, $webp);
        $this->seedGalleryImages($adminId, $webp);
        $this->seedResources($adminId, $webp);
        $this->seedMediaLibrary($adminId, $webp);
    }

    /**
     * Convert a legacy image in public/images/legacy/ into a WebP stored on the public disk.
     */
    private function importLegacyImage(
        WebpImageService $webp,
        string $legacyFileName,
        string $targetDirectory,
        int $maxWidth = 1920,
    ): string {
        $source = public_path('images/legacy/'.$legacyFileName);
        $baseName = pathinfo($legacyFileName, PATHINFO_FILENAME);
        $targetRelative = trim($targetDirectory, '/').'/'.$baseName.'.webp';

        $stored = $webp->storeLocalFile($source, $targetRelative, 'public', 82, $maxWidth);

        return $stored ?? ('/images/legacy/'.$legacyFileName);
    }

    private function seedArticles(?int $adminId, WebpImageService $webp): void
    {
        $articles = [
            [
                'title' => 'Strengthening Partnerships: Mr. Christoph Schmitt, Saxon Policy Advisor engages Ugandan CSOs’ representatives during recent visit to Uganda',
                'event_date' => '2025-05-29',
                'tag' => 'Delegation Visit',
                'location' => 'Uganda',
                'legacy_image' => 'event-320.jpg',
                'featured' => true,
                'excerpt' => 'We were honored to host Mr. Christoph Schmitt, Policy Advisor, Saxony State Chancellery, during his recent visit to Uganda, aimed at deepening collaborations between Uganda and Saxony.',
                'body' => implode("\n\n", [
                    'We were honored to host Mr. Christoph Schmitt, Policy Advisor, Saxony State Chancellery, during his recent visit to Uganda. This visit follows several other visits to Uganda in recent years aimed at deepening collaborations between Uganda and Saxony.',
                    'One of the key highlights was the engagement with representatives from Ugandan CSOs, including Dr. Moses Isooba, Executive Director, Uganda National NGO Forum; Mr. Dickens Kemugisha, Executive Director, Africa Institute for Energy Governance (AFIEGO); Ms. Agwang Sarah, Director of Programmes, Uganda Women’s Network (UWONET); Ms. Norris Nakimbugwe, Partnerships and Advocacy Officer, UWONET; and Mr. Muweleze Ramadhan (UWONET).',
                    'The meeting was graced by His Excellency Ambassador Danny Ssozi, Uganda’s Head of Mission in Berlin, who echoed the importance of international cooperation, solidarity, and partnerships rooted in shared values and mutual respect.',
                    'We were also delighted to be joined by representatives of Civil Society Organizations from Saxony, Germany, among them Dr. André Gerth and Mr. Konrad Sternisko from Gesellschaft für Entwicklung International Sachsen e.V. (Genisa), and Mr. Dieter Anders from GIZ Germany.',
                    'The meeting shed light on the impactful work of CSOs in Uganda, underscoring their vital role in complementing the government’s efforts. The discussion also highlighted the pressing challenges facing the CSO sector, particularly in light of the shrinking funding landscape that threatens to hinder progress.',
                    'In his remarks, Mr. Christoph Schmitt noted that his visit, along with several other delegation visits to Uganda, aims at paving the way for the people of Saxony to establish friendships with the people of Uganda.',
                    'We sincerely appreciate the positive and enthusiastic response from the CSOs that dedicated their time and effort to actively participate. We also extend our heartfelt gratitude to GIZ for their invaluable technical and logistical support, which was crucial in facilitating this important engagement.',
                ]),
            ],
            [
                'title' => '2024 BSW Training at St. Simon Vocational Training Institute Hoima',
                'event_date' => '2024-06-10',
                'tag' => 'Partner Visit',
                'location' => 'Hoima, Uganda',
                'legacy_image' => 'event-208.png',
                'featured' => true,
                'excerpt' => 'From 10th to 15th June 2024, the team from the Bildungswerk der Sächsischen Wirtschaft (BSW) conducted another training at St. Simon Peter’s Vocational Training Institute in Hoima.',
                'body' => implode("\n\n", [
                    'From 10th to 15th June 2024, the team from the Bildungswerk der Sächsischen Wirtschaft (BSW) conducted another training session at St. Simon Peter’s Vocational Training Institute in Hoima, facilitated by Mr. Nehlen Lars – International Welding Instructor. This Training of Trainers (ToT) programme benefited 10 teachers, equipping them with skills and knowledge in welding.',
                    'During the five-day training, participants learnt basic theoretical aspects of arc and TIG welding, virtual welding using the VRT Steinhauser system, and practical welding sessions. The training was designed according to the standards set by the German Association of Welding, to enhance the teaching capabilities of the teachers at the institute.',
                    'The ToT programme ensures sustainable exchange and learning, as the trained teachers will pass on their newly acquired knowledge to their students, consolidating the skills within the institution in the long term.',
                    'In addition to the local training, the 10 teachers are scheduled to travel to Saxony, Germany, for an advanced one-week training at one of the BSW campuses. To prepare, a session on German culture was conducted.',
                    'Eight final year students also benefited from the welding training. The BSW team also donated a computer and equipment so the school can continue to offer VR training sessions.',
                    'Fr. Joseph Bigirwa, the director of St. Simon Peter Vocational Training Institute, appreciated the BSW team, led by Ms. Manja Hübner and Mr. Nehlen Lars, for their commitment to improving vocational education through such collaborative efforts.',
                ]),
            ],
            [
                'title' => 'School Partnership Ceremony for Kololo Senior Secondary School and Gemeinschaftsschule Campus Cordis',
                'event_date' => '2024-02-13',
                'tag' => 'School Visit',
                'location' => 'Kampala, Uganda / Saxony, Germany',
                'legacy_image' => 'event-158.jpg',
                'featured' => true,
                'excerpt' => 'On 13th February 2024, we celebrated the signing of a school partnership between Kololo Senior Secondary School, Kampala, and Gemeinschaftsschule Campus Cordis, Saxony.',
                'body' => implode("\n\n", [
                    'On 13th February 2024, we celebrated a significant achievement – the signing of a school partnership between Kololo Senior Secondary School, Kampala, Uganda, and Gemeinschaftsschule Campus Cordis, Saxony, Germany. The partnership marks the beginning of a long-term journey for the two institutions to collaborate in environmental conservation, scientific innovation, cultural exchange, student and teacher exchange, and improving learning experiences.',
                    'The partnership will facilitate the exchange of teaching methods, cultural knowledge and educational experiences between the two schools. Students will undertake joint projects in Education for Sustainable Development, science, language and culture. It will also enable teachers to exchange ideas and best practices to enhance the quality of education on both sides.',
                ]),
            ],
            [
                'title' => 'Delight Foundation Africa',
                'event_date' => '2024-05-08',
                'tag' => 'Partner Visit',
                'location' => 'Uganda',
                'legacy_image' => 'gallery-6.jpeg',
                'featured' => false,
                'excerpt' => 'During the 2024 mission visit led by Mr. Christoph Schmitt, we visited Delight Foundation Africa and were welcomed by its brass band, cultural dances and poems by the children.',
                'body' => implode("\n\n", [
                    'On 8th February 2024, during the 2024 Mission visit led by Mr. Christoph Schmitt, Policy Advisor for the Saxony State Chancellery, we visited Delight Foundation Africa. The warm welcome from the Delight Foundation brass band set the tone for an incredible day, with live performances, cultural dances and heartfelt poems by the talented children at the Foundation.',
                    'Delight Foundation Africa has been making a positive impact in its community through partnerships, including with Omwana Kinder und Jugendhilfe Uganda e.V. led by Anna Kroning. Through this partnership it has received music equipment, funding for education sponsorship programmes, support for vulnerable households, and other initiatives empowering women and girls.',
                    'Through the Delight Foundation Brass Band, the children engage in music, dance and drama, spending their time meaningfully and productively.',
                    'Mr. Christoph Schmitt thanked the director, Mr. Joseph Muwanguzi, and the children for the warm reception, remarking that his experience ‘was something he would carry with him for a lifetime.’',
                ]),
            ],
            [
                'title' => 'St. Simon Peter Vocational Institute',
                'event_date' => '2023-12-31',
                'tag' => 'School Visit',
                'location' => 'Hoima district, Uganda',
                'legacy_image' => 'event-154.jpg',
                'featured' => false,
                'excerpt' => 'The BSW team conducted a two-day welding training at St. Simon Peter Vocational Training Institute, benefiting 43 participants.',
                'body' => implode("\n\n", [
                    'On the 31st of October and 1st of November 2023, the Bildungswerk der Sächsischen Wirtschaft gGmbH International (BSW) team, led by Ms. Manja Hübner and Ms. Monique Schmieder, conducted a two-day welding training at St. Simon Peter Vocational Training Institute in Hoima district. The sessions were facilitated by BSW’s international craftsman, Mr. Nehlen Lars, and gave students, instructors and craftsmen a foundational understanding of welding, Virtual Reality sessions and hands-on practical exercises.',
                    'A total of 43 participants benefited and received certificates of participation. The virtual reality sessions were particularly exciting for many students, as it was their first exposure to this innovative training method.',
                    'The BSW team pledged to support the institute with training materials and equipment so it can continue offering valuable virtual reality sessions. Reverend Father Joseph Bigirwa, the director, conveyed his appreciation, with special thanks to Ms. Manja for revisiting the school following her initial visit during the delegation visit in July 2023.',
                ]),
            ],
        ];

        foreach ($articles as $data) {
            $slug = Str::slug($data['title']);
            $eventDate = Carbon::parse($data['event_date']);
            $coverPath = $this->importLegacyImage($webp, $data['legacy_image'], 'articles/covers');

            $article = Article::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'tag' => $data['tag'],
                    'location' => $data['location'],
                    'event_date' => $eventDate,
                    'excerpt' => $data['excerpt'],
                    'body' => $data['body'],
                    'cover_image' => $coverPath,
                    'status' => 'published',
                    'featured' => $data['featured'],
                    'published_at' => $eventDate,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            // Migrate any previously seeded /images/legacy/... cover to public-disk WebP
            if (str_starts_with((string) $article->cover_image, '/images/legacy/')) {
                $article->update(['cover_image' => $coverPath]);
            }
        }
    }

    private function seedPartners(?int $adminId, WebpImageService $webp): void
    {
        $partners = [
            [
                'category' => 'Primary Schools',
                'name' => 'Hill Preparatory School - An Integrated Special School',
                'country' => 'Uganda',
                'location' => 'Naguru, Kampala City',
                'website_url' => 'https://www.hillprepschool.ac.ug',
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'Hill Preparatory School (HPS) is a private primary school located in Naguru, Kampala City. The school was founded in 1988 as a pioneer integrated special school where children with learning/intellectual disability study together with their regular counterparts for the mutual benefit of both categories of children.',
                    'As a pioneer school, HPS has been instrumental in championing favourable policies concerning inclusive education such as oral examinations. A Special Needs Centre was set up to cater for children who were not benefitting from the academic goals of the national Primary Leaving Examinations, offering re/assessment, parents’ counselling, psychological services, pre-vocational training, life skills, speech therapy, music therapy, sports and swimming, financial literacy and functional literacy.',
                    'Vision: “A world in which every child is given equal learning rights and opportunities.” Mission: “To use holistic and individual learning approaches to prepare children for a productive and fulfilling life.”',
                    'HPS is a Changemaker school recognised by the East African Network under the ASHOKA world group of social entrepreneurs. It has a half-Olympic size swimming pool used for therapy and training, open to the public, and its students have won several Special Olympics medals. HPS hopes to increase its partnership with public and private stakeholders.',
                ]),
            ],
            [
                'category' => 'Primary Schools',
                'name' => 'St. Mary’s Shagasha Nursery and Primary School',
                'country' => 'Uganda',
                'location' => 'Mushasha village, Ntungamo district (South-Western Uganda, 370 km from Kampala)',
                'website_url' => null,
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'St. Mary’s Shagasha is a rural public nursery and primary school founded in 2011 by the Catholic church. It has 348 pupils aged 3–15, in both day and boarding sections. Extra-curricular activities include football, netball, volleyball, chess, Scrabble, athletics, gardening, handwork, debate, music, dance and drama.',
                    'Areas of interest for partnership: health and sanitation, environmental conservation and sustainability, computer literacy, science and technology — alongside the holistic upbringing of pupils through games and sports, nature conservation and creativity using locally available materials.',
                    'Being in a rural setting, the school offers partner pupils and staff the chance to learn about new cultures, enjoy nature and experience the hospitality of Ugandan people. The children and the community are loving, social, friendly and eager to learn from one another.',
                ]),
            ],
            [
                'category' => 'Secondary Schools',
                'name' => 'Gulu High School',
                'country' => 'Uganda',
                'location' => 'Gulu City, Northern Uganda',
                'website_url' => 'https://www.guluhighschool.ac.ug',
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'Gulu High School is a public secondary school in Gulu City, Northern Uganda, established by the Christian Missionary Society in 1914. It has grown to nurture academically, morally and spiritually upright students, including some of Uganda’s renowned public figures.',
                    'The school currently has over 700 students, including visually impaired students, and offers holistic learning that ensures students with visual impairments also have access to quality education.',
                    'Beyond academics, it offers cricket, basketball, handball and para-olympics for blind students, plus debating, environment, wildlife and agriculture clubs. Ongoing projects include poultry and piggery, tree planting, agriculture and liquid soap making, equipping learners with hands-on skills.',
                ]),
            ],
            [
                'category' => 'Secondary Schools',
                'name' => 'St. Paul High School Rushooka',
                'country' => 'Uganda',
                'location' => 'Ntungamo district, South-Western Uganda',
                'website_url' => null,
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'St. Paul High School Rushooka is a public secondary school established in 1980 by Mbarara Archdiocese in Ntungamo district. It offers both Ordinary (lower secondary) and Advanced Level (upper secondary) education with a wide range of subjects including Physics, Chemistry, Biology, Agriculture, Mathematics, Computer lessons, History, Geography, Religious Education, English, literature in English, Kiswahili and literature in Kiswahili.',
                    'The school places great emphasis on co-curricular activities, clubs and sports, and upholds core values such as transparency, integrity and accountability.',
                    'It welcomes collaboration in areas such as introducing new sports, improving infrastructure and enhancing academic resources, and invites interested partners to join its journey of excellence and transformation.',
                ]),
            ],
            [
                'category' => 'Secondary Schools',
                'name' => 'St. Balikuddembe Senior Secondary School Kisoga',
                'country' => 'Uganda',
                'location' => 'Kisoga village, Mukono District (52 km outside Kampala)',
                'website_url' => 'https://www.stbalikuddembekisoga.com',
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'St. Balikuddembe Senior Secondary School Kisoga is a Catholic-founded, mixed day and boarding secondary school offering science and arts classes at Ordinary and Advanced level. Founded in 1995, it has about 1,000 students and more than 60 dedicated academic staff.',
                    'The school offers a holistic learning approach that nurtures academic excellence, creativity and Christian values. Students are encouraged to take part in soccer, basketball, music, dance and drama, debate and environmental clubs.',
                    'Situated 52 km outside Kampala in a semi-urban area of Mukono district, it is one of the most sought-after schools in the central region, with a safe and conducive environment for learning and growth.',
                ]),
            ],
            [
                'category' => 'Vocational Institutes',
                'name' => 'Kaliro Technical Institute',
                'country' => 'Uganda',
                'location' => 'Kaliro District, Eastern Uganda',
                'website_url' => 'https://kliti.tvet.go.ug/',
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'Kaliro Technical Institute is a public vocational institution in Kaliro District, Eastern Uganda, established in 1983 by the Government of Uganda under the Ministry of Education and Sports. It offers programmes from plumbing and building construction to automotive mechanics, fashion design and electrical systems.',
                    'Key focus areas for partnership: exchange of ideas and best practices; skills enhancement through specialised training and workshops; innovation and technology in teaching and curriculum development; instructor/teacher exchange; and sports and recreation, including equipment and games such as basketball and woodball.',
                ]),
            ],
            [
                'category' => 'Non Government Organisations',
                'name' => 'Katosi Women Development Trust',
                'country' => 'Uganda',
                'location' => 'Katosi fisher community, Ntenjeru sub-county, Mukono District',
                'website_url' => 'https://katosi.org/',
                'legacy_logo' => 'logo-kwdt.png',
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'Katosi Women Development Trust (KWDT) is a non-profit organisation (Uganda NGO Bureau Reg 5761) that aims to improve living standards of poor, rural fisher communities in Uganda by supporting women to engage in their development processes. KWDT has worked with rural and fisher women for the last 25 years, gradually increasing both the number of women groups and its geographical scope and areas of intervention.',
                    'KWDT started in 1996 as Katosi Women Fishing Group (KWFG) in Katosi fisher community, Ntenjeru sub-county, Mukono District, a semi-traditional society that placed control of resources in the hands of men. The group supported women to enter male-dominated fishing activities and to remove water hyacinth from Lake Victoria. In 2000 it became Katosi Women Fishing and Development Association (KWFDA) after diversifying into farming and livestock, and by 2004 other women groups joined, prompting the formation of KWDT. Today it works with 1,050 women organised in 44 women groups.',
                    'Vision: “Empowered women and youth with healthy and productive livelihood in a sustainable environment.” Mission: “Enabling women and female youth in rural and fisher communities to effectively engage in their social, economic & political development for sustainable livelihoods.”',
                ]),
            ],
            [
                'category' => 'Community Based Organisations',
                'name' => 'Kakiri Family Care Organisation (KAFCO)',
                'country' => 'Uganda',
                'location' => 'Kakiri – Kambe, 25km along Hoima Road, Wakiso District (P.O. Box 121706 Kampala)',
                'website_url' => null,
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'Kakiri Family Care Organisation (KAFCO) is a community-based organisation established in 2009 in Kakiri Sub-County, Wakiso District, currently serving over 300 families and more than 900 beneficiaries.',
                    'Key Areas of Intervention: (1) Skills Development & Economic Empowerment — over 85 youth trained, 61 Village Savings and Loan Association (VSLA) groups with 1,220 members, supporting 300 families; (2) Health & Mental Health Support — HIV/AIDS awareness, 310 individuals reached with psychosocial first aid, and 450 referral pathways established; (3) Family Strengthening & Gender — Parenting for Respectability and Gender Mainstreaming programmes.',
                    'Partnership Track Record: KAFCO has collaborated with SOS Children’s Village – Uganda, TASO, Mildmay Uganda, CRANE, and Children’s AID Fund – Uganda, and welcomes new collaborations in youth skilling, community health, and family strengthening.',
                    'Contact: Mr. Mabinda Musa Saad (Executive Director) — Email: Kafco2009@gmail.com | Location: Kakiri – Kambe, 25km along Hoima Road, 400m Off Devon Petrol Station, P.O. Box 121706 Kampala, Uganda.',
                ]),
            ],
            [
                'category' => 'Community Based Organisations',
                'name' => 'The Action for Development of Grassroots Communities (ADEGCO)',
                'country' => 'Uganda',
                'location' => 'Plot 11, Malinga Road, Bukedea Town, Teso Sub-Region, Eastern Uganda',
                'website_url' => 'https://www.adegco.org/',
                'in_partnership' => false,
                'description' => implode("\n\n", [
                    'The Action for Development of Grassroots Communities (ADEGCO) is a registered community-based organisation founded in 2017 by Joseph Obore in Bukedea District, Teso sub-region, Eastern Uganda.',
                    'ADEGCO works directly with grassroots communities across three core pillars: Education & Skills Training, Sustainable Agriculture, and Climate Justice — aligned with the UN Sustainable Development Goals (SDGs), Uganda Vision 2040, and the National Development Plan (NDP).',
                    'Rooted in a Theory of Change centred on endogenous development (“Nothing about us without us”), ADEGCO empowers local communities to design and lead sustainable solutions while building transparent, accountable partnerships with national and international stakeholders.',
                ]),
            ],
        ];

        foreach ($partners as $index => $data) {
            $slug = Str::slug($data['name']);
            $logoPath = isset($data['legacy_logo'])
                ? $this->importLegacyImage($webp, $data['legacy_logo'], 'partners/logos', maxWidth: 600)
                : null;

            $partner = Partner::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'country' => $data['country'],
                    'location' => $data['location'],
                    'logo_url' => $logoPath,
                    'website_url' => $data['website_url'] ?? null,
                    'description' => $data['description'],
                    'in_partnership' => $data['in_partnership'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_by' => $adminId,
                ]
            );

            if ($logoPath !== null && str_starts_with((string) $partner->logo_url, '/images/legacy/')) {
                $partner->update(['logo_url' => $logoPath]);
            }
        }
    }

    private function seedGalleryImages(?int $adminId, WebpImageService $webp): void
    {
        $images = [
            ['legacy' => 'gallery-1.jpg', 'caption' => 'Saxony 2024 Mission Visit', 'album' => 'Delegation Visits'],
            ['legacy' => 'gallery-2.png', 'caption' => 'Gymnasium Tolkerwitz Dresden', 'album' => 'Partner Schools'],
            ['legacy' => 'gallery-3.jpg', 'caption' => 'St. Simon Peter Vocational Institute', 'album' => 'Vocational Training'],
            ['legacy' => 'hero-4.jpg', 'caption' => '2023 July CSOs Workshop', 'album' => 'CSO Workshops'],
            ['legacy' => 'gallery-5.jpg', 'caption' => 'School partnership ceremony', 'album' => 'Partner Schools'],
            ['legacy' => 'gallery-6.jpeg', 'caption' => 'Delight Foundation', 'album' => 'Partner Visits'],
        ];

        foreach ($images as $index => $data) {
            $webpPath = $this->importLegacyImage($webp, $data['legacy'], 'gallery');
            $legacyPath = '/images/legacy/'.$data['legacy'];

            $existing = GalleryImage::whereIn('path', [$webpPath, $legacyPath])->first();

            if ($existing) {
                if (str_starts_with($existing->path, '/images/legacy/')) {
                    $existing->update(['path' => $webpPath]);
                }

                continue;
            }

            GalleryImage::create([
                'path' => $webpPath,
                'caption' => $data['caption'],
                'album' => $data['album'],
                'is_visible' => true,
                'sort_order' => $index + 1,
                'created_by' => $adminId,
            ]);
        }
    }

    private function seedResources(?int $adminId, WebpImageService $webp): void
    {
        $resources = [
            [
                'title' => 'School Partnership Profile Guide',
                'category' => 'Guides & Reports',
                'excerpt' => 'Information and guidelines for Ugandan and Saxon schools preparing a partnership profile.',
                'body' => '<p>Schools in Uganda and the Free State of Saxony interested in forming a long-term school partnership can submit their institutional profile through the coordinating offices (KWDT in Uganda and ENS in Saxony).</p><p>Key focus areas include Education for Sustainable Development (ESD), cultural and language exchange, teacher collaboration, environmental conservation, and practical vocational skills.</p>',
                'legacy_image' => 'gallery-5.jpg',
            ],
            [
                'title' => 'Funding Programmes for Uganda–Saxony Cooperation',
                'category' => 'Funding Opportunities',
                'excerpt' => 'An overview of public funding programmes supporting school exchanges, civil society projects, and municipal cooperation.',
                'body' => '<p>Partnership initiatives between Uganda and Saxony can access several German and international funding lines, including ENSA (Development Policy School Exchange Programme), Engagement Global programmes, and Saxony state funding lines coordinated with ENS.</p><p>Contact the coordinating offices for guidance on eligibility, timelines, and joint application preparation.</p>',
                'legacy_image' => 'banner-saxony.jpg',
            ],
        ];

        foreach ($resources as $index => $data) {
            $slug = Str::slug($data['title']);
            $coverPath = $this->importLegacyImage($webp, $data['legacy_image'], 'resources/covers');

            $resource = ResourceItem::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'category' => $data['category'],
                    'excerpt' => $data['excerpt'],
                    'body' => $data['body'],
                    'cover_image' => $coverPath,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $index + 1,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            if (str_starts_with((string) $resource->cover_image, '/images/legacy/')) {
                $resource->update(['cover_image' => $coverPath]);
            }
        }
    }

    private function seedMediaLibrary(?int $adminId, WebpImageService $webp): void
    {
        $folders = [
            'events-news' => MediaFolder::firstOrCreate(
                ['slug' => 'events-news'],
                ['name' => 'Events & News', 'description' => 'Event and article photography', 'created_by' => $adminId]
            ),
            'gallery' => MediaFolder::firstOrCreate(
                ['slug' => 'gallery'],
                ['name' => 'Gallery', 'description' => 'Public gallery photos', 'created_by' => $adminId]
            ),
            'partners' => MediaFolder::firstOrCreate(
                ['slug' => 'partners'],
                ['name' => 'Partners & Logos', 'description' => 'Partner organisation and school logos', 'created_by' => $adminId]
            ),
            'website-assets' => MediaFolder::firstOrCreate(
                ['slug' => 'website-assets'],
                ['name' => 'Website Assets', 'description' => 'Hero banners, maps, and team portraits', 'created_by' => $adminId]
            ),
        ];

        $legacyDir = public_path('images/legacy');

        if (! is_dir($legacyDir)) {
            return;
        }

        foreach (scandir($legacyDir) ?: [] as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $absolutePath = $legacyDir.DIRECTORY_SEPARATOR.$fileName;

            if (! is_file($absolutePath)) {
                continue;
            }

            $folderKey = match (true) {
                str_starts_with($fileName, 'event-') => 'events-news',
                str_starts_with($fileName, 'gallery-') => 'gallery',
                str_starts_with($fileName, 'logo') || str_contains($fileName, 'coat-of-arms') => 'partners',
                default => 'website-assets',
            };

            $storedPath = $this->importLegacyImage($webp, $fileName, 'media/'.$folderKey);

            if (! Storage::disk('public')->exists($storedPath)) {
                continue;
            }

            $dimensions = @getimagesize(Storage::disk('public')->path($storedPath));

            MediaItem::firstOrCreate(
                ['path' => $storedPath],
                [
                    'folder_id' => $folders[$folderKey]->id,
                    'collection' => $folderKey,
                    'disk' => 'public',
                    'file_name' => basename($storedPath),
                    'original_name' => $fileName,
                    'mime_type' => Storage::disk('public')->mimeType($storedPath) ?: 'image/webp',
                    'size' => Storage::disk('public')->size($storedPath),
                    'width' => is_array($dimensions) ? $dimensions[0] : null,
                    'height' => is_array($dimensions) ? $dimensions[1] : null,
                    'alt_text' => Str::headline(pathinfo($fileName, PATHINFO_FILENAME)),
                    'uploaded_by' => $adminId,
                ]
            );
        }
    }
}
