<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\SectionType;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the public website with content migrated from the previous uganda-saxonypartnership.org site.
 * Images live in public/images/legacy and can later be moved into the media library.
 */
class PageSeeder extends Seeder
{
    private const IMG = '/images/legacy/';

    public function run(): void
    {
        $admin = User::where('email', 'admin@ugandasaxony.org')->first();

        foreach ($this->pages() as $order => $definition) {
            $page = Page::firstOrCreate(
                ['slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'status' => ContentStatus::Published,
                    'is_homepage' => $definition['slug'] === 'home',
                    'show_in_nav' => true,
                    'nav_order' => $order + 1,
                    'meta_title' => $definition['meta_title'] ?? $definition['title'].' | Uganda–Saxony Partnership',
                    'meta_description' => $definition['meta_description'] ?? null,
                    'published_at' => now(),
                    'created_by' => $admin?->id,
                    'updated_by' => $admin?->id,
                    'published_by' => $admin?->id,
                ]
            );

            if ($page->sections()->count() === 0) {
                foreach ($definition['sections'] as $index => [$type, $content]) {
                    $page->sections()->create([
                        'type' => $type,
                        'content' => $content,
                        'settings' => [],
                        'sort_order' => $index,
                        'is_visible' => true,
                    ]);
                }
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            $this->home(),
            $this->aboutUs(),
            $this->partnerSchools(),
            $this->partnerOrganisations(),
            $this->events(),
            $this->gallery(),
            $this->ugandaInformation(),
            $this->saxonyInformation(),
            $this->fundingOpportunities(),
            $this->joinThePartnership(),
            $this->contactUs(),
        ];
    }

    /**
     * @return array{0: SectionType, 1: array<string, mixed>}
     */
    private function banner(string $eyebrow, string $heading, string $accent, string $image, string $subheading = ''): array
    {
        return [SectionType::Hero, [
            'compact' => true,
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'accent' => $accent,
            'subheading' => $subheading,
            'image_url' => self::IMG.$image,
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private function home(): array
    {
        return [
            'slug' => 'home',
            'title' => 'Home',
            'meta_title' => 'Uganda–Saxony Partnership | Building partnerships for sustainable development',
            'meta_description' => 'Strengthening cooperation between schools, civil society organisations and institutions in Uganda and the Free State of Saxony.',
            'sections' => [
                [SectionType::Hero, ['slides' => [
                    [
                        'eyebrow' => 'Uganda – Saxony Partnership',
                        'heading' => 'Building Partnerships',
                        'accent' => 'Uganda – Saxony',
                        'subheading' => 'Strengthening cooperation between schools, civil society organisations and institutions working for sustainable development.',
                        'image_url' => self::IMG.'hero-1.jpg',
                        'primary_cta_label' => 'About the partnership',
                        'primary_cta_url' => '/about-us',
                        'secondary_cta_label' => 'Express interest',
                        'secondary_cta_url' => '/join-the-partnership',
                    ],
                    [
                        'eyebrow' => 'Uganda',
                        'heading' => 'Lake Bunyonyi,',
                        'accent' => 'Uganda',
                        'subheading' => 'The Pearl of Africa — a land of rich cultures, warm hospitality and remarkable landscapes.',
                        'image_url' => self::IMG.'hero-2.jpg',
                        'primary_cta_label' => 'Information on Uganda',
                        'primary_cta_url' => '/uganda-information',
                    ],
                    [
                        'eyebrow' => 'Uganda',
                        'heading' => 'The Nile Bridge,',
                        'accent' => 'Jinja',
                        'image_url' => self::IMG.'hero-3.jpg',
                        'primary_cta_label' => 'Explore the gallery',
                        'primary_cta_url' => '/gallery',
                    ],
                    [
                        'eyebrow' => 'Welcome',
                        'heading' => 'Welcome',
                        'accent' => 'Uganda – Saxony',
                        'image_url' => self::IMG.'hero-4.jpg',
                        'primary_cta_label' => 'Partner schools',
                        'primary_cta_url' => '/partner-schools',
                    ],
                    [
                        'eyebrow' => 'Together',
                        'heading' => 'Partnership',
                        'accent' => 'Workshops',
                        'image_url' => self::IMG.'hero-5.jpg',
                        'primary_cta_label' => 'Latest events',
                        'primary_cta_url' => '/events',
                    ],
                ]]],
                [SectionType::ImageText, [
                    'index' => '01',
                    'label' => 'About the partnership',
                    'heading' => 'Partnerships that grow',
                    'accent' => 'across two regions',
                    'kicker' => 'About the Uganda–Saxony Partnership',
                    'image_url' => self::IMG.'hero-5.jpg',
                    'alt' => 'Partnership workshop in Uganda',
                    'image_position' => 'left',
                    'cta_label' => 'Read more',
                    'cta_url' => '/about-us',
                    'body' => implode("\n\n", [
                        'The Uganda-Saxony Partnership is funded by the Free State of Saxony, through the Entwicklungspolitisches Netzwerk Sachsen, ENS e.V (Development Policy Network Saxony).',
                        'The partnership project seeks to strengthen cooperation of actors in Uganda and Saxony that work in different areas of sustainable development including Civil Society Organisations (CSOs), schools, cultural institutions, sciences, business sector and municipalities interested in social and sustainable development.',
                        'The project directly contributes to the implementation of SDG 17: “Partnerships to achieve the Sustainable Development Goals”.',
                    ]),
                ]],
                [SectionType::Stats, ['items' => [
                    ['value' => '2', 'label' => 'Countries: Uganda & Saxony'],
                    ['value' => '6', 'label' => 'Partner schools profiled'],
                    ['value' => '1,050', 'label' => 'Women organised by KWDT'],
                    ['value' => '17', 'label' => 'Sustainable Development Goal'],
                ]]],
                [SectionType::PartnerLogos, [
                    'index' => '02',
                    'label' => 'Coordinating partners',
                    'heading' => 'Coordinated by',
                    'accent' => 'two organisations',
                    'body' => 'The Uganda-Saxony partnership project is coordinated by Katosi Women Development Trust (KWDT) in Uganda and Entwicklungspolitische Netzwerk Sachsen e.V. (ENS) in Germany.',
                    'logos' => [
                        [
                            'name' => 'Entwicklungspolitisches Netzwerk Sachsen (ENS)',
                            'region' => 'Saxony, Germany',
                            'description' => 'Statewide network of over 170 civil society organisations promoting global justice and development cooperation in Saxony.',
                            'image_url' => self::IMG.'logo-ens.png',
                            'url' => 'https://www.einewelt-sachsen.de/en/ens-homepage/',
                        ],
                        [
                            'name' => 'Katosi Women Development Trust (KWDT)',
                            'region' => 'Uganda',
                            'description' => 'Grassroots Ugandan organisation empowering rural women and communities through WASH, education and sustainable livelihoods.',
                            'image_url' => self::IMG.'logo-kwdt.png',
                            'url' => 'https://katosi.org/',
                        ],
                    ],
                ]],
                [SectionType::Articles, [
                    'index' => '03',
                    'label' => 'Featured events',
                    'heading' => 'Catch the most exciting',
                    'accent' => 'happenings',
                    'body' => 'Catch the most exciting happenings coming up.',
                    'limit' => 3,
                    'view_all_label' => 'View all events',
                    'view_all_url' => '/events',
                    'items' => $this->eventItems(),
                ]],
                [SectionType::FeaturedCards, [
                    'index' => '04',
                    'label' => 'Who takes part',
                    'heading' => 'Participant',
                    'accent' => 'groupings',
                    'cards' => [
                        ['title' => 'Civil Society Organisations', 'description' => 'Non Government Organisations and Community Based Organisations working across WASH, health, gender and women empowerment, youth skilling, environment, culture and art.', 'link_label' => 'Partner organisations', 'link_url' => '/partner-organisations', 'image_url' => self::IMG.'hero-4.jpg'],
                        ['title' => 'Schools', 'description' => 'Primary schools, secondary schools and vocational institutes building lasting school partnerships between Uganda and Saxony.', 'link_label' => 'Partner schools', 'link_url' => '/partner-schools', 'image_url' => self::IMG.'gallery-5.jpg'],
                        ['title' => 'Others', 'description' => 'Cultural institutions, social institutions, business sectors and municipalities interested in social and sustainable development.', 'link_label' => 'Express interest', 'link_url' => '/join-the-partnership', 'image_url' => self::IMG.'gallery-1.jpg'],
                    ],
                ]],
                [SectionType::Cta, [
                    'heading' => 'Interested in a partnership with a',
                    'accent' => 'Saxony partner?',
                    'body' => 'Download the profile template, tell us about your school or organisation and we will help match you with a partner that shares your goals.',
                    'button_label' => 'Express interest',
                    'button_url' => '/join-the-partnership',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aboutUs(): array
    {
        return [
            'slug' => 'about-us',
            'title' => 'About Us',
            'meta_description' => 'Background of the Uganda–Saxony Partnership project and its coordinating partners, KWDT and ENS.',
            'sections' => [
                $this->banner('About us', 'Building Partnerships', 'Uganda – Saxony', 'hero-1.jpg'),
                [SectionType::ImageText, [
                    'index' => '01',
                    'label' => 'Background',
                    'heading' => 'Partnership project',
                    'accent' => 'background',
                    'image_url' => self::IMG.'hero-1.jpg',
                    'alt' => 'Uganda–Saxony Partnership participants collaborating',
                    'image_position' => 'right',
                    'body' => implode("\n\n", [
                        'The Uganda-Saxony Partnership project aims at fostering and promoting partnerships between organisations and schools in Uganda and organisations and schools in the Free State of Saxony. The project is coordinated by Katosi Women Development Trust and The Development Policy Network (Entwicklungspolitisches Netzwerk Sachsen e.V., the ENS).',
                        'Together, we aim to build and support existing partnerships between schools and Civil Society Organisations working in different areas of intervention such as WASH, Health, Gender and women empowerment, Youth skilling, environment, culture, art, etc.',
                    ]),
                ]],
                [SectionType::ImageText, [
                    'index' => '02',
                    'label' => 'Coordinating partner · Uganda',
                    'heading' => 'Katosi Women',
                    'accent' => 'Development Trust (KWDT)',
                    'image_url' => self::IMG.'gallery-1.jpg',
                    'alt' => 'Katosi Women Development Trust community work in Uganda',
                    'image_position' => 'left',
                    'body' => implode("\n\n", [
                        'Katosi Women Development Trust (KWDT) is a registered Non-Governmental Organisation with registration number S.5914/6911 under the Non-Governmental Organizations Registration Statute of 1989. KWDT’s goal is to improve living standards of poor, rural fisher communities in Uganda by empowering them to engage in their development processes.',
                        'KWDT has been working with women and female youth for more than 25 years and has been instrumental in creating change in the lives of rural and fisher women and their communities. KWDT currently works with 1,050 women organised in 44 women groups in Mukono (sub counties of Ntenjeru, Mpatta, Mpunge, Nama, Nakisunga and Katosi Town Council) and other districts: Buvuma, Kalangala, Wakiso and Buikwe.',
                        'KWDT intervenes in rural and fisher communities through women groups as entry points, with interventions benefiting members and the entire communities under the following thematic areas:',
                    ]),
                    'list' => ['Economic empowerment', 'Water, Sanitation and Hygiene (WASH)', 'Education', 'Environment'],
                ]],
                [SectionType::ImageText, [
                    'index' => '03',
                    'label' => 'Coordinating partner · Germany',
                    'heading' => 'The Development Policy',
                    'accent' => 'Network Saxony (ENS)',
                    'image_url' => self::IMG.'banner-saxony.jpg',
                    'alt' => 'Saxony, Germany — home of ENS',
                    'image_position' => 'right',
                    'body' => implode("\n\n", [
                        'The Uganda-Saxony Partnership project is organised by the Entwicklungspolitisches Netzwerk Sachsen e.V. (Development Policy Network Saxony), an association of groups, initiatives and organisations committed to sustainable development and global justice. The ENS was founded in 1995 and is now a platform for development policy and intercultural initiatives in Saxony.',
                        'We want to set out on the path to a just, peaceful and environmentally friendly global society that enables everyone to live a life worth living. The fields of activity of our members include:',
                    ]),
                    'list' => [
                        'Partnership-based development cooperation',
                        'Development policy education and public relations work',
                        'Fair trade, business and sustainability',
                        'Linking environment protection and social justice',
                        'Intercultural and interreligious education',
                        'Human rights and anti-racism work',
                        'Representing interests at state and national level: engaging decision-makers in politics, business and society',
                        'Coordination and cooperation: supporting stakeholders regionally, nationally and internationally',
                        'Service: advising associations, initiatives and individuals on organisational and content-related issues',
                        'Press and public relations work: showing global connections and ways to live more justly',
                        'Education: organising seminars, workshops, panels and conferences for thematic and methodological exchange',
                    ],
                ]],
                [SectionType::FeaturedCards, [
                    'index' => '04',
                    'label' => 'Participants',
                    'heading' => 'Participant',
                    'accent' => 'groupings',
                    'cards' => [
                        ['title' => 'Civil Society Organisations', 'description' => 'Non Government Organisations · Community Based Organisations', 'link_label' => 'Partner organisations', 'link_url' => '/partner-organisations'],
                        ['title' => 'Schools', 'description' => 'Primary Schools · Secondary Schools · Vocational Institutes', 'link_label' => 'Partner schools', 'link_url' => '/partner-schools'],
                        ['title' => 'Others', 'description' => 'Cultural Institutions · Social Institutions · Business Sectors · Municipalities', 'link_label' => 'Express interest', 'link_url' => '/join-the-partnership'],
                    ],
                ]],
                [SectionType::PartnerLogos, [
                    'heading' => 'Learn more about our',
                    'accent' => 'coordinating partners',
                    'logos' => [
                        ['name' => 'Visit the ENS website', 'image_url' => self::IMG.'logo-ens.png', 'url' => 'https://www.einewelt-sachsen.de/en/ens-homepage/'],
                        ['name' => 'Visit the KWDT website', 'image_url' => self::IMG.'logo-kwdt.png', 'url' => 'https://katosi.org/'],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function partnerSchools(): array
    {
        return [
            'slug' => 'partner-schools',
            'title' => 'Partner Schools',
            'meta_description' => 'Primary, secondary and vocational schools in Uganda looking for partnerships with schools in Saxony.',
            'sections' => [
                $this->banner('Partnerships', 'Partner', 'Schools', 'gallery-5.jpg', 'Schools in Uganda that are open to partnering with a school in Saxony.'),
                [SectionType::Directory, [
                    'index' => '01',
                    'label' => 'School directory',
                    'heading' => 'Find a',
                    'accent' => 'school',
                    'groups' => ['Primary Schools', 'Secondary Schools', 'Vocational Institutes'],
                    'entries' => [
                        [
                            'group' => 'Primary Schools',
                            'name' => 'Hill Preparatory School - An Integrated Special School',
                            'country' => 'Uganda',
                            'location' => 'Naguru, Kampala City',
                            'website' => 'www.hillprepschool.ac.ug',
                            'in_partnership' => false,
                            'summary' => 'A private primary school founded in 1988 as a pioneer integrated special school where children with learning or intellectual disabilities study together with their regular counterparts.',
                            'body' => implode("\n\n", [
                                'Hill Preparatory School (HPS) is a private primary school located in Naguru, Kampala City. The school was founded in 1988 as a pioneer integrated special school where children with learning/intellectual disability study together with their regular counterparts for the mutual benefit of both categories of children.',
                                'As a pioneer school, HPS has been instrumental in championing favourable policies concerning inclusive education such as oral examinations. A Special Needs Centre was set up to cater for children who were not benefitting from the academic goals of the national Primary Leaving Examinations, offering re/assessment, parents’ counselling, psychological services, pre-vocational training, life skills, speech therapy, music therapy, sports and swimming, financial literacy and functional literacy.',
                                'Vision: “A world in which every child is given equal learning rights and opportunities.” Mission: “To use holistic and individual learning approaches to prepare children for a productive and fulfilling life.”',
                                'HPS is a Changemaker school recognised by the East African Network under the ASHOKA world group of social entrepreneurs. It has a half-Olympic size swimming pool used for therapy and training, open to the public, and its students have won several Special Olympics medals. HPS hopes to increase its partnership with public and private stakeholders.',
                            ]),
                        ],
                        [
                            'group' => 'Primary Schools',
                            'name' => 'St. Mary’s Shagasha Nursery and Primary School',
                            'country' => 'Uganda',
                            'location' => 'Mushasha village, Ntungamo district (South-Western Uganda, 370 km from Kampala)',
                            'in_partnership' => false,
                            'summary' => 'A rural public nursery and primary school founded in 2011 by the Catholic church, with 348 pupils in day and boarding sections.',
                            'body' => implode("\n\n", [
                                'St. Mary’s Shagasha is a rural public nursery and primary school founded in 2011 by the Catholic church. It has 348 pupils aged 3–15, in both day and boarding sections. Extra-curricular activities include football, netball, volleyball, chess, Scrabble, athletics, gardening, handwork, debate, music, dance and drama.',
                                'Areas of interest for partnership: health and sanitation, environmental conservation and sustainability, computer literacy, science and technology — alongside the holistic upbringing of pupils through games and sports, nature conservation and creativity using locally available materials.',
                                'Being in a rural setting, the school offers partner pupils and staff the chance to learn about new cultures, enjoy nature and experience the hospitality of Ugandan people. The children and the community are loving, social, friendly and eager to learn from one another.',
                            ]),
                        ],
                        [
                            'group' => 'Secondary Schools',
                            'name' => 'Gulu High School',
                            'country' => 'Uganda',
                            'location' => 'Gulu City, Northern Uganda',
                            'website' => 'www.guluhighschool.ac.ug',
                            'in_partnership' => false,
                            'summary' => 'A public secondary school established in 1914, one of the oldest in the region, with over 700 students including visually impaired students.',
                            'body' => implode("\n\n", [
                                'Gulu High School is a public secondary school in Gulu City, Northern Uganda, established by the Christian Missionary Society in 1914. It has grown to nurture academically, morally and spiritually upright students, including some of Uganda’s renowned public figures.',
                                'The school currently has over 700 students, including visually impaired students, and offers holistic learning that ensures students with visual impairments also have access to quality education.',
                                'Beyond academics, it offers cricket, basketball, handball and para-olympics for blind students, plus debating, environment, wildlife and agriculture clubs. Ongoing projects include poultry and piggery, tree planting, agriculture and liquid soap making, equipping learners with hands-on skills.',
                            ]),
                        ],
                        [
                            'group' => 'Secondary Schools',
                            'name' => 'St. Paul High School Rushooka',
                            'country' => 'Uganda',
                            'location' => 'Ntungamo district, South-Western Uganda',
                            'in_partnership' => false,
                            'summary' => 'A public secondary school established in 1980 by Mbarara Archdiocese, offering Ordinary and Advanced Level education.',
                            'body' => implode("\n\n", [
                                'St. Paul High School Rushooka is a public secondary school established in 1980 by Mbarara Archdiocese in Ntungamo district. It offers both Ordinary (lower secondary) and Advanced Level (upper secondary) education with a wide range of subjects including Physics, Chemistry, Biology, Agriculture, Mathematics, Computer lessons, History, Geography, Religious Education, English, literature in English, Kiswahili and literature in Kiswahili.',
                                'The school places great emphasis on co-curricular activities, clubs and sports, and upholds core values such as transparency, integrity and accountability.',
                                'It welcomes collaboration in areas such as introducing new sports, improving infrastructure and enhancing academic resources, and invites interested partners to join its journey of excellence and transformation.',
                            ]),
                        ],
                        [
                            'group' => 'Secondary Schools',
                            'name' => 'St. Balikuddembe Senior Secondary School Kisoga',
                            'country' => 'Uganda',
                            'location' => 'Kisoga village, Mukono District (52 km outside Kampala)',
                            'website' => 'https://www.stbalikuddembekisoga.com',
                            'in_partnership' => false,
                            'summary' => 'A Catholic-founded mixed day and boarding school with about 1,000 students and more than 60 academic staff.',
                            'body' => implode("\n\n", [
                                'St. Balikuddembe Senior Secondary School Kisoga is a Catholic-founded, mixed day and boarding secondary school offering science and arts classes at Ordinary and Advanced level. Founded in 1995, it has about 1,000 students and more than 60 dedicated academic staff.',
                                'The school offers a holistic learning approach that nurtures academic excellence, creativity and Christian values. Students are encouraged to take part in soccer, basketball, music, dance and drama, debate and environmental clubs.',
                                'Situated 52 km outside Kampala in a semi-urban area of Mukono district, it is one of the most sought-after schools in the central region, with a safe and conducive environment for learning and growth.',
                            ]),
                        ],
                        [
                            'group' => 'Vocational Institutes',
                            'name' => 'Kaliro Technical Institute',
                            'country' => 'Uganda',
                            'location' => 'Kaliro District, Eastern Uganda',
                            'website' => 'https://kliti.tvet.go.ug/',
                            'in_partnership' => false,
                            'summary' => 'A public vocational institution established in 1983, equipping students with practical skills for employment and entrepreneurship.',
                            'body' => implode("\n\n", [
                                'Kaliro Technical Institute is a public vocational institution in Kaliro District, Eastern Uganda, established in 1983 by the Government of Uganda under the Ministry of Education and Sports. It offers programmes from plumbing and building construction to automotive mechanics, fashion design and electrical systems.',
                                'Key focus areas for partnership: exchange of ideas and best practices; skills enhancement through specialised training and workshops; innovation and technology in teaching and curriculum development; instructor/teacher exchange; and sports and recreation, including equipment and games such as basketball and woodball.',
                            ]),
                        ],
                    ],
                ]],
                [SectionType::Cta, [
                    'heading' => 'Is your school looking for a',
                    'accent' => 'partner?',
                    'body' => 'Download the School Partnership Profile template and let us help you find a matching partner in Saxony.',
                    'button_label' => 'Express interest',
                    'button_url' => '/join-the-partnership',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function partnerOrganisations(): array
    {
        return [
            'slug' => 'partner-organisations',
            'title' => 'Partner CSOs',
            'meta_title' => 'Partner Organisations | Uganda–Saxony Partnership',
            'meta_description' => 'Civil society organisations in Uganda taking part in the Uganda–Saxony Partnership.',
            'sections' => [
                $this->banner('Partnerships', 'Partner', 'Organisations', 'hero-4.jpg', 'Civil Society Organisations working for sustainable development.'),
                [SectionType::Directory, [
                    'index' => '01',
                    'label' => 'Organisation directory',
                    'heading' => 'Civil Society',
                    'accent' => 'Organisations',
                    'groups' => ['Non Government Organisations', 'Community Based Organisations'],
                    'entries' => [
                        [
                            'group' => 'Non Government Organisations',
                            'name' => 'Katosi Women Development Trust',
                            'country' => 'Uganda',
                            'location' => 'Katosi fisher community, Ntenjeru sub-county, Mukono District',
                            'website' => 'https://katosi.org/',
                            'in_partnership' => false,
                            'summary' => 'A non-profit organisation improving living standards of poor, rural fisher communities by supporting women to engage in their own development.',
                            'body' => implode("\n\n", [
                                'Katosi Women Development Trust (KWDT) is a non-profit organisation that aims to improve living standards of poor, rural fisher communities in Uganda by supporting women to engage in their development processes. KWDT has worked with rural and fisher women for the last 25 years, gradually increasing both the number of women groups and its geographical scope and areas of intervention.',
                                'KWDT started in 1996 as Katosi Women Fishing Group in Katosi fisher community, a semi-traditional society that placed control of resources in the hands of men. The group supported women to enter male-dominated fishing activities and to remove water hyacinth from Lake Victoria. After the 2000 fishing ban, it diversified into farming and livestock, and by 2004 other women groups joined, prompting the formation of KWDT. Today it works with 1,050 women organised in 44 women groups.',
                                'Vision: “Empowered women and youth with healthy and productive livelihood in a sustainable environment.” Mission: “Enabling women and female youth in rural and fisher communities to effectively engage in their social, economic & political development for sustainable livelihoods.”',
                            ]),
                        ],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function eventItems(): array
    {
        return [
            [
                'title' => 'Strengthening Partnerships: Mr. Christoph Schmitt, Saxon Policy Advisor engages Ugandan CSOs’ representatives during recent visit to Uganda',
                'date' => '29 May 2025',
                'tag' => 'Delegation Visit',
                'location' => 'Uganda',
                'image_url' => self::IMG.'event-320.jpg',
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
                'date' => '10 June 2024',
                'tag' => 'Partner Visit',
                'location' => 'Hoima, Uganda',
                'image_url' => self::IMG.'event-208.png',
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
                'date' => '13 February 2024',
                'tag' => 'School Visit',
                'location' => 'Kampala, Uganda / Saxony, Germany',
                'image_url' => self::IMG.'event-158.jpg',
                'excerpt' => 'On 13th February 2024, we celebrated the signing of a school partnership between Kololo Senior Secondary School, Kampala, and Gemeinschaftsschule Campus Cordis, Saxony.',
                'body' => implode("\n\n", [
                    'On 13th February 2024, we celebrated a significant achievement – the signing of a school partnership between Kololo Senior Secondary School, Kampala, Uganda, and Gemeinschaftsschule Campus Cordis, Saxony, Germany. The partnership marks the beginning of a long-term journey for the two institutions to collaborate in environmental conservation, scientific innovation, cultural exchange, student and teacher exchange, and improving learning experiences.',
                    'The partnership will facilitate the exchange of teaching methods, cultural knowledge and educational experiences between the two schools. Students will undertake joint projects in Education for Sustainable Development, science, language and culture. It will also enable teachers to exchange ideas and best practices to enhance the quality of education on both sides.',
                ]),
            ],
            [
                'title' => 'Delight Foundation Africa',
                'date' => '8 May 2024',
                'tag' => 'Partner Visit',
                'location' => 'Uganda',
                'image_url' => self::IMG.'gallery-6.jpeg',
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
                'date' => '31 December 2023',
                'tag' => 'School Visit',
                'location' => 'Hoima district, Uganda',
                'image_url' => self::IMG.'event-154.jpg',
                'excerpt' => 'The BSW team conducted a two-day welding training at St. Simon Peter Vocational Training Institute, benefiting 43 participants.',
                'body' => implode("\n\n", [
                    'On the 31st of October and 1st of November 2023, the Bildungswerk der Sächsischen Wirtschaft gGmbH International (BSW) team, led by Ms. Manja Hübner and Ms. Monique Schmieder, conducted a two-day welding training at St. Simon Peter Vocational Training Institute in Hoima district. The sessions were facilitated by BSW’s international craftsman, Mr. Nehlen Lars, and gave students, instructors and craftsmen a foundational understanding of welding, Virtual Reality sessions and hands-on practical exercises.',
                    'A total of 43 participants benefited and received certificates of participation. The virtual reality sessions were particularly exciting for many students, as it was their first exposure to this innovative training method.',
                    'The BSW team pledged to support the institute with training materials and equipment so it can continue offering valuable virtual reality sessions. Reverend Father Joseph Bigirwa, the director, conveyed his appreciation, with special thanks to Ms. Manja for revisiting the school following her initial visit during the delegation visit in July 2023.',
                ]),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function events(): array
    {
        return [
            'slug' => 'events',
            'title' => 'Events',
            'meta_title' => 'Events & News | Uganda–Saxony Partnership',
            'meta_description' => 'News and events from the Uganda–Saxony Partnership: delegation visits, trainings and school partnership ceremonies.',
            'sections' => [
                $this->banner('Events & news', 'Events', '& News', 'hero-5.jpg', 'We proudly present what is happening in and all around the partnership.'),
                [SectionType::Articles, [
                    'index' => '01',
                    'label' => 'Latest',
                    'heading' => 'Stories from',
                    'accent' => 'the partnership',
                    'items' => $this->eventItems(),
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function gallery(): array
    {
        return [
            'slug' => 'gallery',
            'title' => 'Gallery',
            'meta_description' => 'Photos from partnership visits, workshops and school ceremonies in Uganda and Saxony.',
            'sections' => [
                $this->banner('Gallery', 'Frames of', 'Inspiration', 'gallery-1.jpg', 'Discover the visual inspirations that move us.'),
                [SectionType::Gallery, [
                    'items' => [
                        ['image_url' => self::IMG.'gallery-1.jpg', 'caption' => 'Saxony 2024 Mission Visit'],
                        ['image_url' => self::IMG.'gallery-2.png', 'caption' => 'Gymnasium Tolkerwitz Dresden'],
                        ['image_url' => self::IMG.'gallery-3.jpg', 'caption' => 'St. Simon Peter Vocational Institute'],
                        ['image_url' => self::IMG.'hero-4.jpg', 'caption' => '2023 July CSOs Workshop'],
                        ['image_url' => self::IMG.'gallery-5.jpg', 'caption' => 'School partnership ceremony'],
                        ['image_url' => self::IMG.'gallery-6.jpeg', 'caption' => 'Delight Foundation'],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ugandaInformation(): array
    {
        return [
            'slug' => 'uganda-information',
            'title' => 'Information on Uganda',
            'meta_description' => 'Country, culture, climate and school system information about Uganda.',
            'sections' => [
                $this->banner('Resource centre', 'Information on', 'Uganda', 'hero-2.jpg', 'Lake Bunyonyi Hills'),
                [SectionType::Tabs, [
                    'tabs' => [
                        [
                            'title' => 'About Uganda',
                            'groups' => [
                                ['heading' => 'Uganda country information', 'body' => implode("\n\n", [
                                    'Uganda, popularly known as ‘The Pearl of Africa’, is located in East Africa and lies astride the equator. It is a land-locked country bordered by Kenya in the East, Tanzania in the South, Rwanda in the Southwest, Democratic Republic of Congo in the West and South Sudan in the North. The southern part of the country includes a substantial portion of Lake Victoria, shared with Kenya and Tanzania, situating the country in the African Great Lakes region.',
                                    'Uganda also lies within the Nile basin, and has a varied but generally equatorial climate. Uganda takes its name from the Buganda kingdom, which encompasses a large portion of the south of the country including the capital Kampala.',
                                ]), 'image_url' => self::IMG.'map-uganda.png', 'image_caption' => 'Map of Uganda showing neighbouring countries and administrative districts'],
                                ['heading' => 'Hospitality and culture', 'body' => implode("\n\n", [
                                    'Ugandans are known for their hospitality and friendliness towards each other and visitors alike. However, it is advisable to be cautious when dealing with strangers. Police officers are very helpful and are easily identified by their uniforms.',
                                    'Uganda has a very strong cultural heritage. Many regions have kingdoms including Buganda, Busoga, Bunyoro and Toro. Ugandans hail from a diversity of rich cultures and lifestyles, and culture and traditions are also expressed through a wide range of arts and crafts.',
                                ])],
                                ['heading' => 'Climate', 'body' => 'Uganda enjoys a tropical climate, with abundant sunshine all year round and moderate temperatures. In Kampala, near Lake Victoria, average daily temperatures range from 18° to 28° C (65° to 83° F) in January and from 17° to 25° C (62° to 77° F) in July. The climate is generally rainy with two dry seasons (December to February, June to August), and semiarid East Sudanian savanna in the north near Sudan.'],
                            ],
                        ],
                        [
                            'title' => 'Uganda School System',
                            'groups' => [
                                ['heading' => 'The Uganda school system', 'body' => 'Uganda considers education a basic human right. The system has 7 years of primary education, 6 years of secondary education (4 years of lower secondary and 2 years of upper secondary), and 3 to 5 years of post-secondary education. Throughout all levels, modules are taught and assessed in English.'],
                                ['heading' => 'Early Childhood Development and Nursery', 'body' => 'The National Integrated Early Childhood Development Policy and Action Plan guides the development of children’s full potential from conception to 8 years of age. Parents and caregivers are expected to help children grow and thrive physically, mentally, emotionally, spiritually, morally and socially. Early childhood centres include pre-school, nursery schools and day care centres.'],
                                ['heading' => 'Primary Education', 'body' => 'Despite its best intentions, there are few truly free state schools in Uganda, and the quality of paid private schools varies tremendously. The school year runs from February to December and begins with 7 years of primary education. For pupils in classes of up to one hundred who fail to pass their leaving examinations, this ends their hopes.'],
                                ['heading' => 'Middle Education', 'body' => 'The first phase of secondary academic education takes 4 years, at the end of which students must pass their O-Levels before proceeding further. Alternatively they may attend a technical college for 3 years, and then go on to study at a technical institute or primary teacher training college.'],
                                ['heading' => 'Secondary Education', 'body' => 'Secondary schooling still follows the British academic model where traditional subjects continue to be taught. A successful A-Level pass opens up the possibility of tertiary education for the few who make it this far.'],
                                ['heading' => 'Vocational Education', 'body' => 'Uganda faces high unemployment among young people, and there is an urgent need to kick-start the economy. Many foreign aid agencies and governments are working hand in hand with local authorities to turn this into a reality.'],
                                ['heading' => 'Tertiary Education', 'body' => 'There are 5 state universities, 11 religiously-affiliated universities and 10 private secular universities in Uganda, plus 4 technical colleges, of which 1 is private. Fewer than 50% of Ugandan students who qualify for tertiary education find places. The second oldest and by far the largest institution is Makerere University, founded in 1922 on a hill overlooking Kampala, with several campuses and 8 halls of residence, 2 of which are for women.'],
                            ],
                        ],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function saxonyInformation(): array
    {
        return [
            'slug' => 'saxony-information',
            'title' => 'Information on Saxony',
            'meta_description' => 'Country, climate and school system information about the Free State of Saxony.',
            'sections' => [
                $this->banner('Resource centre', 'Information on', 'Saxony', 'banner-saxony.jpg', 'Elbe River'),
                [SectionType::Tabs, [
                    'tabs' => [
                        [
                            'title' => 'About Saxony',
                            'groups' => [
                                ['heading' => 'The Free State of Saxony', 'body' => implode("\n\n", [
                                    'Saxony is a federal state in the east of the Federal Republic of Germany. Until 1990, its territory was part of the GDR. Around 4.1 million people live in Saxony. The average age is 46.8 years, which is higher than the national average of 44.7 years in 2021. The three largest cities are Dresden, Leipzig and Chemnitz. Dresden is also the capital of the Free State and the seat of the Saxon government.',
                                    'Saxony borders Poland to the east and the Czech Republic to the south. Lusatia, the eastern part of Saxony, has been home to the Sorbs, a Slavic ethnic group with its own culture and language, for more than 1,000 years.',
                                ]), 'image_url' => self::IMG.'map-saxony.png', 'image_caption' => 'Map of Saxony'],
                                ['heading' => 'Climate', 'body' => implode("\n\n", [
                                    'Due to its location in the east of Germany and its distance from the Atlantic Ocean, the climate in Saxony is more continental than in other German federal states: generally less rainfall, colder winters and warmer summers.',
                                    'Spring (March–May) is rainier, with temperatures rising to an average of 5° to 15° Celsius; frost can still occur at night until mid-May, especially in the southern mountains. Summer (June–August) is the warmest and brightest season, with temperatures from 20° to over 30° Celsius and more frequent thunderstorms. In autumn (September–November) days get shorter and temperatures range from 18° to 5° Celsius. Winter (December–February) is the coldest season, with average temperatures between 5° and −5° Celsius and short days.',
                                    'Vegetation follows the seasons: nature comes back to life in spring, trees and plants blossom and bear fruit over the summer, late summer and early autumn is the main harvest time, and nature rests again after the leaves fall.',
                                ])],
                            ],
                        ],
                        [
                            'title' => 'Saxony School System',
                            'groups' => [
                                ['heading' => 'The Saxon school system', 'body' => 'In Saxony, as in the rest of Germany, children start school at the age of 6. Starting school is often a big family celebration at which children receive gifts such as sugar cones filled with sweets. Similar to Uganda, there are different types of schools, starting with primary school, then secondary school and grammar school. Pupils then have the opportunity to learn a profession or go to university.'],
                                ['heading' => 'Primary school', 'body' => implode("\n\n", [
                                    'Pupils attend primary school (Grundschule) for the first 4 years, where they learn basic skills such as reading, writing and maths. With around 20 pupils, classes are usually smaller than in other school types.',
                                    'Children learn in the morning and then go home or are looked after in the after-school care centre, where they can do homework or play with friends. At the end of primary school, pupils receive a recommendation for secondary school or grammar school.',
                                ])],
                                ['heading' => 'Secondary school', 'body' => implode("\n\n", [
                                    'At secondary school (Oberschule), students can complete the Hauptschulabschluss after year 9 or the Realschulabschluss after year 10. They study general subjects as well as technical, business, language or artistic subjects and prepare for later vocational training.',
                                    'Class size is around 25 to 30 students.',
                                ])],
                                ['heading' => 'Grammar school', 'body' => implode("\n\n", [
                                    'At grammar school (Gymnasium), students study from year 5 to year 12 and then take exams in several subjects, the Abitur. The Abitur entitles them to study at a college or university, and is also required for some vocational training programmes such as bank clerk, media designer or laboratory technician.',
                                    'Students can choose a grammar school with a language, science or art specialisation according to their interests. Class sizes are similar to the secondary school.',
                                ])],
                                ['heading' => 'Community school', 'body' => 'At a community school (Gemeinschaftsschule), students continue to learn together after primary school and can, in principle, complete all of the school-leaving qualifications mentioned above.'],
                                ['heading' => 'Special school', 'body' => 'This type of school (Förderschule) educates students with special educational needs and enables them to lead as independent a life as possible. Students can complete a Hauptschule or Realschule qualification, and may transfer to other school types.'],
                                ['heading' => 'Inclusion', 'body' => implode("\n\n", [
                                    'The system, divided into mainstream and special schools, has long been criticised for unequal treatment of students. Students with special educational needs therefore also have the right to attend a regular primary, secondary or grammar school; the decision lies with the parents.',
                                    'The inclusive organisation of schools and lessons is a long process whose goal is equal opportunities for all students.',
                                ])],
                                ['heading' => 'Vocational school', 'body' => 'After general schooling, students can learn a profession in the dual system: they attend a vocational school (Berufsschule) for theoretical knowledge and at the same time learn practical skills in a training company such as a hospital, joinery or law firm. An apprenticeship usually lasts two to three years.'],
                                ['heading' => 'University', 'body' => implode("\n\n", [
                                    'The Abitur entitles students to study at a college or university/university of applied sciences. Depending on the programme, they can complete a three-year bachelor’s degree or a diploma (about five years, offered in some programmes only). After the bachelor’s degree there is the option of a postgraduate programme ending with a master’s degree.',
                                    'Universities of applied sciences are generally more practice-oriented and usually specialise in certain subject areas.',
                                ])],
                                ['heading' => 'Education is the responsibility of the federal states', 'body' => 'In Germany, education is the responsibility of the individual federal states, so there are differences from state to state. For example, students in Saxony go to school for a maximum of 12 years, while in North Rhine-Westphalia it is 13 years in some cases. Curricula are decided by the education ministries of the individual states.'],
                                ['heading' => 'The school year', 'body' => 'The school year in Saxony begins in August and is divided into 2 semesters. The first half-year runs from August to February, when students receive their half-year reports. The second half-year runs from the end of February/beginning of March to June/July, followed by a 6-week summer holiday. School holidays vary from state to state.'],
                            ],
                        ],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fundingOpportunities(): array
    {
        $ensaRules = [
            ['label' => 'Who is eligible to apply?', 'text' => 'Public or private general education and vocational schools based in Germany; non-profit non-governmental organisations or school support associations based in Germany.'],
            ['label' => 'Pedagogic support', 'text' => 'In the case of ENSA funding, participants from the German school take part in the accompanying kick-off and final conferences and an evaluation workshop in the year of the trip. The conferences prepare and reflect on the trip and take place in spring and autumn.'],
            ['label' => 'Financial support', 'text' => 'Up to 75% of eligible expenses; 25% must be covered by own or third-party funds (not additional federal funds). Maximum standard funding €14,000, with an increase of €1,000 possible for special inclusion requirements. Travel costs and programme expenses are funded; no personnel costs.'],
            ['label' => 'Application', 'text' => 'July to September for trips in the following year. Notification is issued in December.'],
        ];

        return [
            'slug' => 'funding-opportunities',
            'title' => 'Funding Opportunities',
            'meta_description' => 'Funding programmes for school partnerships and youth exchange between Germany and African countries.',
            'sections' => [
                $this->banner('Resource centre', 'Funding', 'Opportunities', 'banner-saxony.jpg', 'Programmes that support school partnerships and youth exchange.'),
                [SectionType::Tabs, [
                    'accordion' => true,
                    'tabs' => [
                        [
                            'title' => 'School Partnerships',
                            'intro' => 'ENSA promotes school partnerships between schools in Germany and countries in the Global South and South-East Europe, so that students learn about different perspectives, better understand global interrelationships and work together for a sustainable world.',
                            'groups' => [
                                ['heading' => 'ENSA – Development Policy School Exchange Programme', 'body' => implode("\n\n", [
                                    'Educational programmes: ENSA offers training formats and networking events to support teachers and employees of accompanying organisations and to strengthen the integration of global learning / education for sustainable development (ESD) topics in the classroom.',
                                    'Funding programme: for reciprocal visits between partner schools, ENSA funds initiation and exchange trips and offers regular information events on the programme.',
                                ])],
                                ['heading' => '1) Initiation trips', 'body' => 'Initiation trips serve to get to know each other personally in new school partnerships, exchange expectations and deepen the partnership.', 'items' => array_merge($ensaRules, [
                                    ['label' => 'Group size', 'text' => 'Minimum of 4 and maximum of 6 people, including at least 2 students; minimum student age is 14.'],
                                    ['label' => 'Duration', 'text' => 'Two trips are applied for: outgoing and incoming. Travel period 1 March – 31 October. Up to 7 programme days plus arrival and departure days are eligible; up to 5 additional days at participants’ own cost.'],
                                ])],
                                ['heading' => '2) Exchange trips', 'body' => 'Exchange trips let students visit the partner school in the Global South (outgoing) or host students from the partner school (incoming). They are only possible if the partnership has existed for at least one year, and focus on working together on a topic of the school’s choice in education for sustainable development.', 'items' => array_merge($ensaRules, [
                                    ['label' => 'Group size', 'text' => 'Minimum of 6 and maximum of 12 participants (students and accompanying persons); minimum student age is 14. Additional accompanying persons cover their own costs.'],
                                    ['label' => 'Duration', 'text' => 'Travel period 1 March – 31 October; 10 to 14 programme days plus arrival and departure days; up to 5 additional days at participants’ own cost.'],
                                ])],
                            ],
                        ],
                        [
                            'title' => 'German-African Youth Office',
                            'intro' => 'The German-African Youth Office (DAJW) brings young people from Germany and African countries together to work for socially, ecologically and economically sustainable development, through its two programme lines Teams up! and Team works!.',
                            'groups' => [
                                ['heading' => 'Teams Up!', 'items' => [
                                    ['label' => 'What is funded?', 'text' => 'International youth exchanges and related accompanying or model projects that deal with the 17 Sustainable Development Goals (SDGs). Bilateral encounters, multilateral cooperation with two African partners and, exceptionally, triangular cooperation are possible.'],
                                    ['label' => 'Target group', 'text' => 'Young people between the ages of 16 and 30.'],
                                    ['label' => 'Objectives', 'text' => 'Strengthening youth engagement for sustainable development, intercultural learning, and qualifying multipliers to lead German–African youth exchange programmes.'],
                                    ['label' => 'Requirements', 'text' => 'A German and an African partner, both non-profit organisations that are not missionary, state or commercially active. The German partner must have undergone an audit of the organisation. The project is planned and implemented in close cooperation between the two partners.'],
                                    ['label' => 'Project phases & application', 'text' => 'An expression of interest as a brief concept is due no later than 8 weeks before the application. Projects follow 5 phases: Preparation, Initial meeting, Intermediate, Return visit, and Follow-up. The full application includes a technical and educational concept, programme schedule, SDG relevance, financial plan, and a security and crisis concept. All German participants register on the “ELEFAND” crisis preparedness list of the Federal Foreign Office.'],
                                    ['label' => 'Duration & funding', 'text' => 'Maximum project duration of 2 years. Proportional funding of up to 75% of total expenditure, divided equally between the partners (BRKG travel rules). Personnel costs and fees can be included; no lump sums.'],
                                ]],
                                ['heading' => 'Team Works! — Overview', 'items' => [
                                    ['label' => 'What is funded?', 'text' => 'A tandem programme in which young and experienced professionals work together. Young professionals complete a 2.5 to 6-week work-shadowing placement in an African country or in Germany, accompanied by experts from the Senior Expert Service (SES), in companies, administration, social or medical institutions. Inclusive internships are offered.'],
                                    ['label' => 'Target group', 'text' => 'Young people in training occupations from Germany and African countries.'],
                                    ['label' => 'Objectives', 'text' => 'To promote partnership-based cooperation from a global learning perspective, professional exchange and commitment to sustainable development.'],
                                ]],
                                ['heading' => 'Team Works! — Work shadowing in an African country', 'items' => [
                                    ['label' => 'Prerequisites', 'text' => 'For young German skilled workers in training occupations aged 18–30, at least in the 3rd year of an apprenticeship.'],
                                    ['label' => 'Procedure', 'text' => 'Submission of an SES profile, a 2-day preparation seminar, visa processing, and registration on the Federal Foreign Office ELEFAND crisis preparedness list.'],
                                    ['label' => 'Accommodation', 'text' => 'Arranged near the placement centre and shared with the accompanying SES expert.'],
                                    ['label' => 'What is financed?', 'text' => 'Flights, meals, accommodation, and preparation seminars are covered; participants cover their own excursions and pocket money.'],
                                    ['label' => 'Insurance cover', 'text' => 'International health, personal liability, luggage, repatriation, and accident insurance are included.'],
                                ]],
                                ['heading' => 'Team Works! — Work shadowing in Germany', 'items' => [
                                    ['label' => 'Prerequisites', 'text' => 'For young African skilled workers in training occupations aged 18–30, at least in the 3rd year of an apprenticeship.'],
                                    ['label' => 'Procedure', 'text' => 'Submission of an SES profile, digital preparation, online intercultural and language training, with a permanent contact person and SES mentor throughout the stay.'],
                                    ['label' => 'Accommodation', 'text' => 'Arranged by SES close to the host training centre or workplace.'],
                                    ['label' => 'What is financed?', 'text' => 'Flights, visas, meals, accommodation, preparation seminars, and pocket money are covered.'],
                                    ['label' => 'Insurance cover', 'text' => 'International health, accident, and personal liability insurance are included.'],
                                ]],
                            ],
                        ],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function joinThePartnership(): array
    {
        return [
            'slug' => 'join-the-partnership',
            'title' => 'Join the Partnership',
            'meta_description' => 'Express your interest in a partnership with a Saxony partner by downloading and completing a profile template.',
            'sections' => [
                $this->banner('Get involved', 'Join the', 'Partnership', 'banner-join.png', 'We are pleased that you are interested in a partnership with a Saxony partner.'),
                [SectionType::RichText, [
                    'index' => '01',
                    'label' => 'Participate',
                    'heading' => 'How to',
                    'accent' => 'participate',
                    'body' => implode("\n\n", [
                        'In order to identify a suitable partner, we ask you to download and fill in the profile template. The answers are intended to give your prospective school/organisation partner a first impression.',
                        'A similar questionnaire will be filled in by your prospective school partner. This will ease future communication and enable us to identify and match partners with similar interests and goals.',
                    ]),
                ]],
                [SectionType::FeaturedCards, [
                    'index' => '02',
                    'label' => 'Participant profile templates',
                    'heading' => 'Download a',
                    'accent' => 'profile template',
                    'cards' => [
                        ['title' => 'Template for Uganda-Saxony Organisation Profile', 'description' => 'For civil society organisations and other institutions. Fill it in and send it to the coordination team.', 'link_label' => 'Download (DOCX)', 'link_url' => '/storage/templates/UPDATED-TEMPLATE-FOR-ORGANISATION-PROFILE.docx'],
                        ['title' => 'Template for School Partnership Profile', 'description' => 'For primary, secondary and vocational schools looking for a partner school in Saxony.', 'link_label' => 'Download (PDF)', 'link_url' => 'https://uganda-saxonypartnership.org/cms/wp-content/uploads/2024/05/Template-for-School-Partnership-Profile.pdf'],
                    ],
                ]],
                [SectionType::Cta, [
                    'heading' => 'Questions before you',
                    'accent' => 'apply?',
                    'body' => 'Our coordination team in Kampala is happy to help.',
                    'button_label' => 'Contact us',
                    'button_url' => '/contact-us',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contactUs(): array
    {
        return [
            'slug' => 'contact-us',
            'title' => 'Contact Us',
            'meta_description' => 'Contact the Uganda–Saxony Partnership coordination team in Kampala.',
            'sections' => [
                $this->banner('Contact', 'Get in', 'touch', 'hero-3.jpg', 'Reach the coordination office in Kampala.'),
                [SectionType::People, [
                    'index' => '01',
                    'label' => 'Coordinating team · Uganda',
                    'heading' => 'Meet the',
                    'accent' => 'coordinating team',
                    'people' => [
                        ['name' => 'Nakato Margaret', 'role' => 'Project Coordinator', 'image_url' => self::IMG.'person-nakato.jpg', 'email' => 'nakato@katosi.org', 'phones' => ['+256 772 587 427', '+256 754 587 427']],
                        ['name' => 'Fiona Mugisha Okwere', 'role' => 'Project Officer', 'image_url' => self::IMG.'person-fiona.png', 'email' => 'fiona.okwere@katosi.org', 'phones' => ['+256 755 330 525', '+256 774 351 195']],
                    ],
                ]],
                [SectionType::Contact, [
                    'index' => '02',
                    'label' => 'Write to us',
                    'heading' => 'Send us a',
                    'accent' => 'message',
                    'details' => [
                        ['label' => 'Contact', 'lines' => ['+256 772 748 774', '+256 704 090 711']],
                        ['label' => 'Postal address', 'lines' => ['P.O. Box 33929', 'Kampala, Uganda']],
                        ['label' => 'Coordination office', 'lines' => ['Plot 902 – Off Kalema Rd, Makamba Zone', 'Lungujja – Kikandwa, Rubaga']],
                        ['label' => 'Email', 'lines' => ['info@uganda-saxonypartnership.org', 'ugandasaxony@gmail.com']],
                    ],
                ]],
            ],
        ];
    }
}
