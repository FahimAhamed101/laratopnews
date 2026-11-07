<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->resetSeededData();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $now = now();
        $images = $this->images();
        $permissions = $this->seedPermissions();

        $superAdminRole = Role::findOrCreate('Super Admin', 'admin');
        $writerRole = Role::findOrCreate('Writer', 'admin');

        $superAdminRole->syncPermissions($permissions);
        $writerRole->syncPermissions([
            'news index',
            'news create',
            'news update',
            'category index',
            'languages index',
        ]);

        $admin = Admin::create([
            'name' => 'Nadia Rahman',
            'email' => 'admin@admin.com',
            'image' => $images['admin'],
            'password' => Hash::make('12345678'),
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $admin->assignRole($superAdminRole);

        $writer = Admin::create([
            'name' => 'Arif Hasan',
            'email' => 'writer@gmail.com',
            'image' => $images['writer'],
            'password' => Hash::make('12345678'),
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $writer->assignRole($writerRole);

        $communityAuthor = new Admin();
        $communityAuthor->name = 'Community Desk';
        $communityAuthor->email = 'community@dailymetro.test';
        $communityAuthor->image = $images['writer'];
        $communityAuthor->password = Hash::make('12345678');
        $communityAuthor->status = 1;
        $communityAuthor->created_at = $now;
        $communityAuthor->updated_at = $now;
        $communityAuthor->save();
        $communityAuthor->assignRole($writerRole);

        $this->seedLanguages($now);
        $this->seedSettings($images, $now);
        $categoryIds = $this->seedCategories($now);
        $this->seedHomeSections($categoryIds, $now);
        $this->seedAds($images, $now);
        $this->seedNews($images, $categoryIds, $admin->id, $writer->id, $now);
        $this->seedUsersAndComments($images, $categoryIds, $communityAuthor->id, $now);
        $this->seedPages($images, $now);
        $this->seedFooter($images, $now);
        $this->seedSocials($now);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function resetSeededData(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        foreach ([
            'news_tags',
            'comments',
            'news',
            'tags',
            'home_section_settings',
            'categories',
            'ads',
            'footer_grid_ones',
            'footer_grid_twos',
            'footer_grid_threes',
            'footer_titles',
            'footer_infos',
            'social_counts',
            'social_links',
            'subscribers',
            'received_mails',
            'abouts',
            'contacts',
            'settings',
            'languages',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'admins',
        ] as $table) {
            DB::table($table)->delete();
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    private function images(): array
    {
        return [
            'logo' => 'uploads/fcGC0SXmYvI0kepIBB1usluTLpoPms.png',
            'favicon' => 'uploads/bgSXgTV5CNlB1pZwLyH0eyNp5O4cCZ.png',
            'admin' => 'uploads/EKIABPcZLJ0EvV2kLoMM4eST6n8whJ.jpg',
            'writer' => 'uploads/CS4y8VcBnqwoNeTHDfCTsKJrEgK2SI.jpg',
            'adTop' => 'uploads/daily-metro-news-ad-wide.png',
            'adMiddle' => 'uploads/daily-metro-news-ad-wide.png',
            'adView' => 'uploads/daily-metro-news-ad-view.png',
            'adNews' => 'uploads/daily-metro-news-ad-news.png',
            'adSide' => 'uploads/daily-metro-news-ad-sidebar.png',
            'government' => 'uploads/UXYFEjGSw74rBcf0wsLJLe19S8EDrK.jpg',
            'business' => 'uploads/umzIRAdE2wXkKyvLjqkqBBLyggSOj4.jpg',
            'technology' => 'uploads/UAXcKCDMrjkQ2x8cJX8hAp6PC6cA0N.jpg',
            'sports' => 'uploads/lbKu10FF4NY8zcKOZSCkF978xy9kfC.jpeg',
            'health' => 'uploads/TCYzchq0e3vbMkLIrNXVdxotzXfsxs.webp',
            'climate' => 'uploads/ynLtBJFhlfwIXjmmMnVHZFxD39xZ0B.webp',
            'education' => 'uploads/D1jCqpkd35fDTtXTia7mFIziYKumr4.webp',
            'culture' => 'uploads/hKh9Oy6ApZY8b3R6aYGjKCci81Jc3a.webp',
            'travel' => 'uploads/dJo0FMq152rCtUIU9cRuLIMY8aBe1r.webp',
            'food' => 'uploads/Bt9h1Jgqhma510TaP6FyepGD1BRrXt.webp',
            'finance' => 'uploads/cCIYvybi3hG75lhuGcg8vYyeeFXls2.webp',
            'science' => 'uploads/6PYJVWQOvsJx2GQ4h3Fo9IL2NMFYiH.webp',
        ];
    }

    private function seedPermissions(): array
    {
        $permissions = [
            'category index',
            'category create',
            'category update',
            'category delete',
            'news index',
            'news create',
            'news update',
            'news delete',
            'news status',
            'news all-access',
            'social count index',
            'social count create',
            'social count update',
            'social count delete',
            'contact message index',
            'contact message update',
            'about index',
            'about update',
            'contact index',
            'contact update',
            'conatact index',
            'home section index',
            'home section update',
            'advertisement index',
            'advertisement update',
            'languages index',
            'languages create',
            'languages update',
            'languages delete',
            'subscribers index',
            'subscribers delete',
            'footer index',
            'footer create',
            'footer update',
            'footer destroy',
            'access management index',
            'access management create',
            'access management update',
            'access management destroy',
            'setting index',
            'setting update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        return $permissions;
    }

    private function seedLanguages($now): void
    {
        DB::table('languages')->insert([
            ['name' => 'English', 'lang' => 'en', 'slug' => 'en', 'default' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bangla', 'lang' => 'bn', 'slug' => 'bn', 'default' => 0, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedSettings(array $images, $now): void
    {
        DB::table('settings')->insert([
            ['key' => 'site_name', 'value' => 'Extremis News', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_logo', 'value' => $images['logo'], 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_favicon', 'value' => $images['favicon'], 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_seo_title', 'value' => 'Extremis News - Verified Local and Global Updates', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_seo_description', 'value' => 'Extremis News publishes verified updates for readers who need clear, useful reporting without noise.', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_seo_keywords', 'value' => 'news, Extremis News, business, technology, sports, health, climate, culture', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_color', 'value' => '#1066ff', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_microsoft_api_host', 'value' => 'microsoft-translator-text.p.rapidapi.com', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'site_microsoft_api_key', 'value' => 'demo-key', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedCategories($now): array
    {
        $categories = [
            ['key' => 'national', 'en' => 'National', 'bn' => 'জাতীয়', 'slug' => 'national', 'show_at_nav' => 1],
            ['key' => 'business', 'en' => 'Business', 'bn' => 'ব্যবসা', 'slug' => 'business', 'show_at_nav' => 1],
            ['key' => 'technology', 'en' => 'Technology', 'bn' => 'প্রযুক্তি', 'slug' => 'technology', 'show_at_nav' => 1],
            ['key' => 'sports', 'en' => 'Sports', 'bn' => 'খেলা', 'slug' => 'sports', 'show_at_nav' => 1],
            ['key' => 'health', 'en' => 'Health', 'bn' => 'স্বাস্থ্য', 'slug' => 'health', 'show_at_nav' => 1],
            ['key' => 'climate', 'en' => 'Climate', 'bn' => 'জলবায়ু', 'slug' => 'climate', 'show_at_nav' => 0],
            ['key' => 'culture', 'en' => 'Culture', 'bn' => 'সংস্কৃতি', 'slug' => 'culture', 'show_at_nav' => 0],
        ];

        $ids = [];

        foreach ($categories as $category) {
            foreach (['en', 'bn'] as $language) {
                $ids[$language][$category['key']] = DB::table('categories')->insertGetId([
                    'language' => $language,
                    'name' => $category[$language],
                    'slug' => $language === 'en' ? $category['slug'] : $category['slug'].'-bn',
                    'show_at_nav' => $category['show_at_nav'],
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $ids;
    }

    private function seedHomeSections(array $categoryIds, $now): void
    {
        foreach (['en', 'bn'] as $language) {
            DB::table('home_section_settings')->insert([
                'category_section_one' => $categoryIds[$language]['national'],
                'category_section_two' => $categoryIds[$language]['business'],
                'category_section_three' => $categoryIds[$language]['technology'],
                'category_section_four' => $categoryIds[$language]['sports'],
                'language' => $language,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedAds(array $images, $now): void
    {
        DB::table('ads')->insert([
            'home_top_bar_ad' => $images['adTop'],
            'home_top_bar_ad_status' => 1,
            'home_middle_ad' => $images['adMiddle'],
            'home_middle_ad_status' => 1,
            'view_page_ad' => $images['adView'],
            'view_page_ad_status' => 1,
            'news_page_ad' => $images['adNews'],
            'news_page_ad_status' => 1,
            'side_bar_ad' => $images['adSide'],
            'side_bar_ad_status' => 1,
            'home_top_bar_ad_url' => url('/news'),
            'home_middle_ad_url' => url('/contact'),
            'view_page_ad_url' => url('/news'),
            'news_page_ad_url' => url('/news'),
            'side_bar_ad_url' => url('/contact'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedNews(array $images, array $categoryIds, int $adminId, int $writerId, $now): void
    {
        $items = [
            ['national', 'Cabinet Clears Dhaka Bus Route Reform for Trial Launch', 'ঢাকার বাস রুট সংস্কারের পরীক্ষামূলক চালু অনুমোদন', $images['government'], ['transport', 'government', 'dhaka'], ['পরিবহন', 'সরকার', 'ঢাকা'], 1, 1, 1],
            ['business', 'Exporters Report Strong Apparel Orders Ahead of Summer Season', 'গ্রীষ্ম মৌসুমের আগে পোশাক রপ্তানিতে শক্ত অর্ডার', $images['business'], ['business', 'export', 'garments'], ['ব্যবসা', 'রপ্তানি', 'পোশাক'], 0, 1, 1],
            ['technology', 'Local Software Firms Expand Hiring as AI Projects Increase', 'এআই প্রকল্প বাড়ায় স্থানীয় সফটওয়্যার প্রতিষ্ঠানে নিয়োগ বৃদ্ধি', $images['technology'], ['technology', 'ai', 'jobs'], ['প্রযুক্তি', 'এআই', 'চাকরি'], 1, 1, 1],
            ['sports', 'Tigers Announce Squad for Upcoming T20 Series', 'আসন্ন টি-টোয়েন্টি সিরিজের দল ঘোষণা করল টাইগাররা', $images['sports'], ['cricket', 'sports', 'series'], ['ক্রিকেট', 'খেলা', 'সিরিজ'], 0, 1, 1],
            ['health', 'Hospitals Add Evening Clinics to Reduce Patient Waiting Time', 'রোগীর অপেক্ষা কমাতে হাসপাতালে সন্ধ্যাকালীন ক্লিনিক চালু', $images['health'], ['health', 'hospital', 'care'], ['স্বাস্থ্য', 'হাসপাতাল', 'সেবা'], 1, 0, 1],
            ['climate', 'Coastal Communities Build New Shelters Before Monsoon', 'বর্ষার আগে উপকূলে নতুন আশ্রয়কেন্দ্র নির্মাণ', $images['climate'], ['climate', 'coast', 'monsoon'], ['জলবায়ু', 'উপকূল', 'বর্ষা'], 0, 0, 1],
            ['national', 'Education Board Publishes Revised Academic Calendar', 'সংশোধিত শিক্ষাবর্ষের ক্যালেন্ডার প্রকাশ করল বোর্ড', $images['education'], ['education', 'schools', 'calendar'], ['শিক্ষা', 'স্কুল', 'ক্যালেন্ডার'], 0, 0, 0],
            ['culture', 'Film Festival Opens With Focus on New Directors', 'নতুন নির্মাতাদের নিয়ে চলচ্চিত্র উৎসব শুরু', $images['culture'], ['culture', 'film', 'festival'], ['সংস্কৃতি', 'চলচ্চিত্র', 'উৎসব'], 0, 0, 1],
            ['business', 'Mobile Banking Use Rises Across Rural Markets', 'গ্রামীণ বাজারে মোবাইল ব্যাংকিং ব্যবহার বেড়েছে', $images['finance'], ['finance', 'mobile banking', 'rural'], ['অর্থনীতি', 'মোবাইল ব্যাংকিং', 'গ্রাম'], 0, 0, 1],
            ['technology', 'University Lab Tests Low-Cost Water Quality Sensors', 'স্বল্প খরচের পানির মান সেন্সর পরীক্ষা করছে বিশ্ববিদ্যালয় ল্যাব', $images['science'], ['science', 'water', 'research'], ['বিজ্ঞান', 'পানি', 'গবেষণা'], 0, 0, 0],
            ['sports', 'Women Footballers Begin Training Camp in Sylhet', 'সিলেটে নারী ফুটবলারদের প্রশিক্ষণ ক্যাম্প শুরু', $images['travel'], ['football', 'training', 'sylhet'], ['ফুটবল', 'প্রশিক্ষণ', 'সিলেট'], 0, 0, 0],
            ['health', 'Nutrition Program Reaches More Than 50,000 Schoolchildren', '৫০ হাজারের বেশি শিক্ষার্থীর কাছে পৌঁছেছে পুষ্টি কর্মসূচি', $images['food'], ['nutrition', 'children', 'schools'], ['পুষ্টি', 'শিশু', 'স্কুল'], 0, 0, 0],
            ['national', 'National Rail Network Unveils Digital Ticketing and High-Speed Upgrade Plan', 'জাতীয় রেলওয়েতে ডিজিটাল টিকিট ও দ্রুতগতির আধুনিকায়ন পরিকল্পনা প্রকাশ', $images['travel'], ['railway', 'transport', 'infrastructure'], ['রেলওয়ে', 'পরিবহন', 'অবকাঠামো'], 0, 0, 1],
            ['business', 'Central Bank Upgrades Digital Payment Framework to Boost SME Trade', 'ক্ষুদ্র উদ্যোক্তাদের বাণিজ্যে ডিজিটাল লেনদেন নীতিমালা সংস্কার করল কেন্দ্রীয় ব্যাংক', $images['finance'], ['banking', 'fintech', 'economy'], ['ব্যাংকিং', 'ফিনটেক', 'অর্থনীতি'], 1, 0, 1],
            ['technology', 'Semiconductor Design Hub Launched to Train Next-Generation Chip Engineers', 'সেমিকন্ডাক্টর চিপ প্রকৌশলী তৈরিতে অত্যাধুনিক ডিজাইন ল্যাব চালু', $images['technology'], ['semiconductor', 'chips', 'engineering'], ['সেমিকন্ডাক্টর', 'চিপ', 'প্রকৌশল'], 0, 1, 1],
            ['health', 'Telemedicine Network Expands Primary Care Access to Remote Districts', 'দূরবর্তী জেলায় প্রাথমিক স্বাস্থ্যসেবা পৌঁছাতে টেলিমেডিসিন নেটওয়ার্ক সম্প্রসারণ', $images['health'], ['telemedicine', 'healthcare', 'digital health'], ['টেলিমেডিসিন', 'স্বাস্থ্যসেবা', 'ডিজিটাল স্বাস্থ্য'], 0, 0, 1],
            ['climate', 'Renewable Solar Grid Installations Exceed National Target for Second Quarter', 'দ্বিতীয় প্রান্তিকে জাতীয় লক্ষ্যমাত্রা ছাড়িয়েছে সৌরবিদ্যুৎ সংযোগ', $images['climate'], ['solar', 'energy', 'sustainability'], ['সৌরবিদ্যুৎ', 'শক্তি', 'পরিবেশ'], 0, 1, 0],
            ['culture', 'National Heritage Archive Completes Digitization of Historical Manuscripts', 'ঐতিহাসিক পাণ্ডুলিপির ডিজিটাল সংরক্ষণ সম্পন্ন করল জাতীয় আর্কাইভ', $images['culture'], ['heritage', 'history', 'archive'], ['ঐতিহ্য', 'ইতিহাস', 'আর্কাইভ'], 0, 0, 0],
            ['sports', 'Youth Athletics Academy Opens High-Performance Training Center', 'তরুণ অ্যাথলেটদের জন্য উচ্চমানের প্রশিক্ষণ কেন্দ্র চালু করল অ্যাকাডেমি', $images['sports'], ['athletics', 'youth sports', 'training'], ['অ্যাথলেটিকস', 'খেলাধুলা', 'প্রশিক্ষণ'], 0, 0, 0],
            ['national', 'Clean Energy Public Transport Fleet Expands Across Major Metropolitan Corridors', 'প্রধান মেট্রোপলিটন করিডোরে পরিচ্ছন্ন জ্বালানির গণপরিবহন বহর সম্প্রসারিত', $images['government'], ['clean energy', 'city', 'transport'], ['পরিচ্ছন্ন জ্বালানি', 'শহর', 'পরিবহন'], 0, 0, 0],
        ];

        foreach ($items as $index => [$categorySlug, $englishTitle, $banglaTitle, $image, $englishTags, $banglaTags, $breaking, $slider, $popular]) {
            foreach (['en', 'bn'] as $language) {
                $title = $language === 'en' ? $englishTitle : $banglaTitle;
                $tags = $language === 'en' ? $englishTags : $banglaTags;
                $newsId = DB::table('news')->insertGetId([
                    'language' => $language,
                    'category_id' => $categoryIds[$language][$categorySlug],
                    'author_id' => $index % 3 === 0 ? $adminId : $writerId,
                    'image' => $image,
                    'title' => $title,
                    'slug' => Str::slug($englishTitle).($language === 'bn' ? '-bn' : ''),
                    'content' => $this->articleContent($title, $categorySlug, $language),
                    'meta_title' => $title,
                    'meta_description' => $language === 'en'
                        ? 'In-depth investigative report from Extremis News on '.$englishTitle.', detailing strategic implications and official responses.'
                        : 'এক্সট্রিমিস নিউজের বিশেষ প্রতিবেদন: '.$banglaTitle.'-এর সার্বিক পটভূমি ও ভবিষ্যৎ প্রভাব।',
                    'is_breaking_news' => $breaking,
                    'show_at_slider' => $slider,
                    'show_at_popular' => $popular,
                    'is_approved' => 1,
                    'status' => 1,
                    'views' => 140 + ($index * 35) + ($language === 'bn' ? 24 : 0),
                    'created_at' => $now->copy()->subHours($index * 6),
                    'updated_at' => $now->copy()->subHours($index * 2),
                ]);

                foreach ($tags as $tagName) {
                    $tagId = DB::table('tags')->insertGetId([
                        'name' => $tagName,
                        'language' => $language,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('news_tags')->insert([
                        'news_id' => $newsId,
                        'tag_id' => $tagId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function articleContent(string $title, string $category, string $language): string
    {
        $catName = ucfirst($category);

        if ($language === 'bn') {
            return <<<HTML
<div class="article-source-meta" style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px; margin-bottom: 1.5rem; font-size: 0.88rem; color: #475569;">
    <span style="color: #1066ff; font-weight: 700;">&#10003; যাচাইকৃত প্রতিবেদন</span>
    <span style="color: #cbd5e1;">|</span>
    <span>এক্সট্রিমিস নিউজ অনুসন্ধানী ডেস্ক | বিভাগ: <strong>{$catName}</strong></span>
</div>

<p class="lead" style="font-size: 1.15rem; font-weight: 500; line-height: 1.7; color: #2d3748; margin-bottom: 1.5rem;">
    <strong>{$title}</strong> সংক্রান্ত বিষয়ে সংশ্লিষ্ট নীতিনির্ধারক, প্রাতিষ্ঠানিক কর্মকর্তা ও মাঠপর্যায়ের বিশেষজ্ঞদের প্রত্যক্ষ মতামত পর্যালোচনা করে এক্সট্রিমিস নিউজ ডেস্ক একটি সমন্বিত অনুসন্ধানী প্রতিবেদন প্রস্তুত করেছে। এই উদ্যোগ সংশ্লিষ্ট খাত এবং সাধারণ মানুষের দৈনন্দিন কার্যক্রমে তাৎপর্যপূর্ণ ইতিবাচক রূপান্তরের সূচনা করতে পারে বলে অভিমত বিশেষজ্ঞদের।
</p>

<div class="key-takeaways-box" style="background: #f8fafc; border-left: 4px solid #1066ff; padding: 1.25rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h4 style="margin-top: 0; margin-bottom: 0.75rem; color: #0f172a; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;"><span style="color:#1066ff;">&#9679;</span> মূল বিষয় ও উল্লেখযোগ্য দিকসমূহ</h4>
    <ul style="margin: 0; padding-left: 1.25rem; color: #334155; line-height: 1.65;">
        <li style="margin-bottom: 0.4rem;">সংশ্লিষ্ট মন্ত্রণালয়ের ঊর্ধ্বতন পর্যায়ে আনুষ্ঠানিক পর্যালোচনা ও অনুমোদন প্রক্রিয়া সফলভাবে সমাপ্ত হয়েছে।</li>
        <li style="margin-bottom: 0.4rem;">কার্যক্রম বাস্তবায়নে আধুনিক প্রযুক্তিগত অবকাঠামো এবং কার্যকর মনিটরিং সেল গঠনের নির্দেশনা প্রদান করা হয়েছে।</li>
        <li style="margin-bottom: 0.4rem;">আগামী ত্রৈমাসিকের মধ্যে মাঠপর্যায়ের ফলাফল পর্যালোচনা সাপেক্ষে পরবর্তী সম্প্রসারণের সিদ্ধান্ত নেওয়া হবে।</li>
        <li style="margin-bottom: 0.4rem;">নাগরিক সেবা সহজীকরণ ও স্বচ্ছতা বৃদ্ধিতে নিয়মিত ডিজিটাল ড্যাশবোর্ডের মাধ্যমে অগ্রগতির তথ্য প্রকাশের সিদ্ধান্ত গৃহীত হয়েছে।</li>
    </ul>
</div>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">১. পটভূমি, নীতিগত ভিত্তি এবং প্রেক্ষাপট</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    সাম্প্রতিক বছরগুলোতে {$category} খাতে যে কাঠামোগত পরিবর্তনের দাবি উঠছিল, এই পদক্ষেপ সেই ধারাবাহিকতার এক সময়োপযোগী প্রতিফলন। সংশ্লিষ্ট খাতের বিশেষজ্ঞগণ দীর্ঘদিন যাবৎ একটি সমন্বিত নীতিমালার প্রয়োজনীয়তার ওপর গুরুত্বারোপ করে আসছিলেন। নীতি প্রণয়নকারী কর্তৃপক্ষের বক্তব্য অনুযায়ী, আন্তর্জাতিক মানদণ্ড এবং স্থানীয় প্রয়োজনের মধ্যে সামঞ্জস্য বজায় রেখেই বর্তমান রূপরেখাটি প্রণয়ন করা হয়েছে।
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    বিশেষ করে টেকসই উন্নয়ন লক্ষ্যমাত্রা এবং নাগরিক সেবার মানোন্নয়নের যে অঙ্গীকার জাতীয় পর্যায়ে রয়েছে, তার সাথে এই উদ্যোগ ওতপ্রোতভাবে জড়িত। সংশ্লিষ্ট কর্তৃপক্ষ নিশ্চিত করেছেন যে, প্রকল্প বাস্তবায়নের প্রতিটি স্তরে জবাবদিহিতা নিশ্চিত করার জন্য একটি বিশেষ অডিট ও পর্যবেক্ষণ টিম সার্বক্ষণিক দায়িত্ব পালন করবে।
</p>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">২. প্রাতিষ্ঠানিক বাস্তবায়ন ও অর্থনৈতিক প্রভাব</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    প্রস্তাবিত কর্মপরিকল্পনার অর্থনৈতিক দিকটি অত্যন্ত সূক্ষ্মভাবে বিশ্লেষণ করা হয়েছে। বরাদ্দকৃত সম্পদের সর্বোচ্চ সদ্ব্যবহার নিশ্চিত করতে সরকারি ও বেসরকারি খাতের অংশীদারিত্বকে উৎসাহিত করা হচ্ছে। প্রাথমিক প্রাক্কলন অনুযায়ী, এই সংস্কার বাস্তবায়ন হলে প্রশাসনিক কার্যক্রমে সময় ও ব্যয় উভয়েই উল্লেখযোগ্য হারে হ্রাস পাবে, যা সামগ্রিক অর্থনীতিতে উৎপাদনশীলতা বাড়াতে সহায়ক হবে।
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    অর্থনৈতিক বিশ্লেষকদের মতে, উন্নয়নশীল অর্থনীতির ক্ষেত্রে প্রাতিষ্ঠানিক সিদ্ধান্তগুলোর দ্রুত বাস্তবায়নই সাফল্যের মূল চাবিকাঠি। ফলে নীতিগত স্বচ্ছতা থাকলে বিদেশি বিনিয়োগ ও স্থানীয় উদ্যোক্তাদের অংশগ্রহণ বহুগুণ বৃদ্ধি পেতে পারে।
</p>

<blockquote style="border-left: 4px solid #1066ff; background: #f1f5f9; padding: 1.25rem 1.5rem; margin: 2rem 0; border-radius: 0 8px 8px 0; font-style: italic; color: #1e293b;">
    <p style="margin-bottom: 0.5rem; font-size: 1.1rem; line-height: 1.6;">“এই রূপান্তর শুধু একটি প্রশাসনিক সিদ্ধান্ত নয়; বরং এটি দীর্ঘমেয়াদে প্রাতিষ্ঠানিক সক্ষমতা এবং নাগরিক আস্থার ভিত্তি সুদৃঢ় করার একটি যুগান্তকারী পদক্ষেপ।”</p>
    <footer style="font-style: normal; font-size: 0.9rem; color: #64748b; font-weight: 600;">— ড. এ. এস. চৌধুরী, জ্যেষ্ঠ পলিসি বিশ্লেষক ও গবেষক</footer>
</blockquote>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">৩. সম্ভাব্য চ্যালেঞ্জ ও নজরদারির প্রয়োজনীয়তা</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    যেকোনো বৃহৎ নীতিগত রূপান্তরে প্রাথমিক স্তরে কিছু প্রযুক্তিগত ও প্রক্রিয়াগত প্রতিবন্ধকতা দেখা দেওয়া স্বাভাবিক। বিশেষজ্ঞরা সতর্ক করে দিয়ে বলেছেন, প্রাতিষ্ঠানিক সমন্বয়হীনতা যাতে কাজের গতি শ্লথ না করতে পারে, সেজন্য নিয়মিত ইন্টার-এজেন্সি বৈঠক আয়োজন জরুরি। এছাড়াও সংশ্লিষ্ট কর্মীদের জন্য বিশেষায়িত প্রশিক্ষণ কর্মশালার আয়োজন করা না হলে মাঠপর্যায়ে সেবার কাঙ্ক্ষিত মান নিশ্চিত করা কঠিন হতে পারে।
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    জনসচেতনতা বৃদ্ধির বিষয়টিও এখানে সমান গুরুত্ব বহন করে। সাধারণ নাগরিকরা যাতে নতুন নিয়মাবলি ও সুবিধা সম্পর্কে স্পষ্ট ধারণা পান, সেজন্য গণমাধ্যম ও ডিজিটাল প্লাটফর্মে তথ্যবহুল প্রচার কার্যক্রম জোরদার করার প্রস্তাব করা হয়েছে।
</p>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">৪. ভবিষ্যৎ গতিপথ ও পর্যালোচনার রূপরেখা</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    পরবর্তী ১২ থেকে ২৪ মাসের জন্য একটি সুনির্দিষ্ট টাইমলাইন নির্ধারণ করা হয়েছে। প্রতি ছয় মাস অন্তর স্বাধীন নিরীক্ষা কমিটির মাধ্যমে অগ্রগতির মূল্যায়ন প্রতিবেদন প্রকাশ করা হবে। এতে করে যেকোনো বিচ্যুতি তাৎক্ষণিকভাবে সংশোধন করা সম্ভব হবে।
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    এক্সট্রিমিস নিউজ এই উন্নয়ন প্রক্রিয়ার প্রতিটি ধাপ নিবিড়ভাবে পর্যবেক্ষণ করবে এবং তথ্যের বস্তুনিষ্ঠতা বজায় রেখে পাঠকদের নিয়মিত আপডেট প্রদান করবে।
</p>

<div class="faq-section" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
    <h3 style="font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: 1.25rem;">সাধারণ প্রশ্নোত্তর (FAQ)</h3>
    <div class="faq-item" style="margin-bottom: 1.25rem; background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="font-size: 1.05rem; font-weight: 600; color: #1066ff; margin-bottom: 0.5rem;">প্রশ্ন: এই নতুন সিদ্ধান্তের ফলে সাধারণ নাগরিকরা কীভাবে সরাসরি উপকৃত হবেন?</h4>
        <p style="margin: 0; color: #475569; line-height: 1.6;">উত্তর: সেবা প্রাপ্তির সময়সীমা উল্লেখযোগ্যভাবে কমবে, স্বচ্ছতা বৃদ্ধি পাবে এবং ডিজিটাল ব্যবস্থার মাধ্যমে যে কোনো স্থান থেকে হালনাগাদ তথ্য পাওয়া সহজ হবে।</p>
    </div>
    <div class="faq-item" style="margin-bottom: 1.25rem; background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="font-size: 1.05rem; font-weight: 600; color: #1066ff; margin-bottom: 0.5rem;">প্রশ্ন: কার্যক্রমটি কখন থেকে পূর্ণাঙ্গভাবে কার্যকর হবে?</h4>
        <p style="margin: 0; color: #475569; line-height: 1.6;">উত্তর: প্রাথমিক পর্যায়ের ট্রায়াল সফলভাবে সম্পন্ন হওয়ার পর আগামী ৯০ দিনের মধ্যে ধাপে ধাপে এটি দেশব্যাপী কার্যকর করা হবে।</p>
    </div>
    <div class="faq-item" style="margin-bottom: 1.25rem; background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="font-size: 1.05rem; font-weight: 600; color: #1066ff; margin-bottom: 0.5rem;">প্রশ্ন: সাধারণ নাগরিক বা অংশীজনরা কীভাবে মতামত বা অভিযোগ জানাতে পারবেন?</h4>
        <p style="margin: 0; color: #475569; line-height: 1.6;">উত্তর: নির্দিষ্ট অনলাইন ফিডব্যাক পোর্টাল এবং সংশ্লিষ্ট নিয়ন্ত্রণকারী দপ্তরের হটলাইনের মাধ্যমে সরাসরি তথ্য ও মতামত পাঠানো যাবে।</p>
    </div>
</div>
HTML;
        }

        return <<<HTML
<div class="article-source-meta" style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px; margin-bottom: 1.5rem; font-size: 0.88rem; color: #475569;">
    <span style="color: #1066ff; font-weight: 700;">&#10003; Verified Investigative Coverage</span>
    <span style="color: #cbd5e1;">|</span>
    <span>Extremis News Wire | Section: <strong>{$catName}</strong></span>
</div>

<p class="lead" style="font-size: 1.15rem; font-weight: 500; line-height: 1.7; color: #2d3748; margin-bottom: 1.5rem;">
    In an extensive policy and field review regarding <strong>{$title}</strong>, senior analysts, regulatory officials, and key community stakeholders have provided on-the-record assessments highlighting structural reforms, economic realities, and measurable impacts across the {$category} sector.
</p>

<div class="key-takeaways-box" style="background: #f8fafc; border-left: 4px solid #1066ff; padding: 1.25rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h4 style="margin-top: 0; margin-bottom: 0.75rem; color: #0f172a; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;"><span style="color:#1066ff;">&#9679;</span> Key Takeaways & Strategic Highlights</h4>
    <ul style="margin: 0; padding-left: 1.25rem; color: #334155; line-height: 1.65;">
        <li style="margin-bottom: 0.4rem;">High-level regulatory directives and formal institutional reviews have been completed with binding operational timelines.</li>
        <li style="margin-bottom: 0.4rem;">Modernized digital compliance mechanisms and dedicated oversight committees have been chartered to prevent procedural bottlenecks.</li>
        <li style="margin-bottom: 0.4rem;">Preliminary financial analyses indicate significant efficiency gains, reduced transaction frictions, and stronger capital mobilization.</li>
        <li style="margin-bottom: 0.4rem;">Independent review boards will publish transparent quarterly milestones to ensure accountability and civic feedback integration.</li>
    </ul>
</div>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">1. Policy Genesis, Institutional Context, and Historical Drivers</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    The development surrounding {$title} represents a decisive inflection point in contemporary {$category} governance. Over the past several quarters, sector analysts and public interest groups had repeatedly underscored the need for modernized operating standards that address both systemic inefficiencies and shifting global benchmarks. Officials confirmed that this initiative was developed following extensive stakeholder consultations and comparative international regulatory benchmarking.
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    By aligning localized implementation strategies with broader macroeconomic stability goals, authorities aim to create a resilient operating environment. Central to this strategy is the formal integration of data-driven tracking mechanisms that enable real-time risk identification, procedural audits, and transparent performance disclosures.
</p>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">2. Operational Implementation and Economic Ramifications</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    From an operational perspective, the transition requires a coordinated realignment among government agencies, private sector partners, and regional oversight bodies. Financial models suggest that capital allocations directed toward modernizing infrastructure and streamlining workflows will yield positive returns through reduced downtime and administrative cost containment.
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    Economists monitoring the sector observe that policy predictability serves as a catalyst for sustainable investment. When regulatory frameworks are transparent, standardized, and enforceable, commercial confidence increases, encouraging both domestic innovation and foreign direct investment.
</p>

<blockquote style="border-left: 4px solid #1066ff; background: #f1f5f9; padding: 1.25rem 1.5rem; margin: 2rem 0; border-radius: 0 8px 8px 0; font-style: italic; color: #1e293b;">
    <p style="margin-bottom: 0.5rem; font-size: 1.1rem; line-height: 1.6;">“Sustainable institutional reform requires clear accountability, comprehensive stakeholder participation, and unwavering commitment to factual integrity. This framework establishes a solid baseline for long-term progress.”</p>
    <footer style="font-style: normal; font-size: 0.9rem; color: #64748b; font-weight: 600;">— Dr. Marcus Vance, Senior Fellow at the Global Policy Institute</footer>
</blockquote>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">3. Structural Challenges, Compliance Hurdles, and Public Debate</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    Despite the positive projections, industry specialists have identified several friction points that require proactive management. Inter-agency coordination remains a primary challenge, particularly where overlapping administrative jurisdictions can result in implementation delays. Moreover, ensuring adequate technical training for frontline personnel is critical to preventing service interruptions during the rollout phases.
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    Consumer advocacy representatives have also urged authorities to maintain accessible grievance redressal avenues. Clear public communication and accessible digital hotlines will be vital in ensuring that community concerns are addressed with speed, fairness, and procedural transparency.
</p>

<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">4. Future Outlook and What to Watch Next</h2>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    Looking ahead, the initiative will undergo formal milestone evaluations over the next 12 to 24 months. Key indicators under observation include operational uptime, compliance rates, cost savings, and public satisfaction indices. Should intermediate targets be met, authorities have signaled readiness to scale the model across affiliated sectors.
</p>
<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">
    Extremis News will continue to monitor field updates, official briefings, and stakeholder responses, providing ongoing investigative coverage as new developments unfold.
</p>

<div class="faq-section" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
    <h3 style="font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: 1.25rem;">Frequently Asked Questions (FAQ)</h3>
    <div class="faq-item" style="margin-bottom: 1.25rem; background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="font-size: 1.05rem; font-weight: 600; color: #1066ff; margin-bottom: 0.5rem;">How does this development directly impact everyday citizens and businesses?</h4>
        <p style="margin: 0; color: #475569; line-height: 1.6;">The reforms aim to significantly lower administrative turnaround times, enhance digital service accessibility, and ensure transparent, predictable operating procedures.</p>
    </div>
    <div class="faq-item" style="margin-bottom: 1.25rem; background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="font-size: 1.05rem; font-weight: 600; color: #1066ff; margin-bottom: 0.5rem;">What is the anticipated deployment timeline?</h4>
        <p style="margin: 0; color: #475569; line-height: 1.6;">Following initial pilot validation, phased nationwide integration is scheduled across subsequent quarters, with progress reviews conducted every 90 days.</p>
    </div>
    <div class="faq-item" style="margin-bottom: 1.25rem; background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="font-size: 1.05rem; font-weight: 600; color: #1066ff; margin-bottom: 0.5rem;">Where can stakeholders submit feedback or report implementation issues?</h4>
        <p style="margin: 0; color: #475569; line-height: 1.6;">Public submissions can be directed through dedicated departmental portals, official public inquiry hotlines, or via the newsroom tips desk at Extremis News.</p>
    </div>
</div>
HTML;
    }

    private function seedUsersAndComments(array $images, array $categoryIds, int $communityAuthorId, $now): void
    {
        $readers = [
            ['name' => 'Maya Chowdhury', 'email' => 'reader@example.com'],
            ['name' => 'Tanvir Ahmed', 'email' => 'tanvir.reader@example.com'],
        ];

        foreach ($readers as $reader) {
            $user = User::firstOrNew(['email' => $reader['email']]);
            $user->name = $reader['name'];
            $user->password = Hash::make('12345678');
            $user->email_verified_at = $now;
            $user->save();
        }

        $maya = User::where('email', 'reader@example.com')->first();
        $tanvir = User::where('email', 'tanvir.reader@example.com')->first();

        $englishNews = News::where('language', 'en')->activeEntries()->latest()->take(3)->get();
        $banglaNews = News::where('language', 'bn')->activeEntries()->latest()->take(2)->get();

        foreach ($englishNews as $index => $news) {
            DB::table('comments')->insert([
                'news_id' => $news->id,
                'user_id' => $index % 2 === 0 ? $maya->id : $tanvir->id,
                'parent_id' => null,
                'comment' => [
                    'Helpful update. The category pages are much easier to follow now.',
                    'Please keep covering the next phase of this story.',
                    'The context section made this report useful for readers outside Dhaka.',
                ][$index],
                'created_at' => $now->copy()->subHours($index + 1),
                'updated_at' => $now->copy()->subHours($index + 1),
            ]);
        }

        foreach ($banglaNews as $index => $news) {
            DB::table('comments')->insert([
                'news_id' => $news->id,
                'user_id' => $index % 2 === 0 ? $maya->id : $tanvir->id,
                'parent_id' => null,
                'comment' => [
                    'খবরটি পরিষ্কারভাবে উপস্থাপন করা হয়েছে।',
                    'এই বিষয়ে পরবর্তী আপডেটের অপেক্ষায় থাকলাম।',
                ][$index],
                'created_at' => $now->copy()->subHours($index + 4),
                'updated_at' => $now->copy()->subHours($index + 4),
            ]);
        }

        $submissionId = DB::table('news')->insertGetId([
            'language' => 'en',
            'category_id' => $categoryIds['en']['national'],
            'author_id' => $communityAuthorId,
            'submitted_by_user_id' => $maya->id,
            'image' => $images['government'],
            'title' => 'Readers Report New Footpath Repairs Near Mirpur Schools',
            'slug' => 'readers-report-new-footpath-repairs-near-mirpur-schools',
            'content' => '<p>Residents near several Mirpur schools reported that footpath repair work started this week after months of complaints from parents and shop owners.</p><p>The submission says workers began clearing broken tiles and placing safety barriers near the morning school entry points. The editorial team will verify the schedule with city officials before publication.</p>',
            'meta_title' => 'Readers Report New Footpath Repairs Near Mirpur Schools',
            'meta_description' => 'A reader-submitted local update waiting for editorial approval.',
            'is_breaking_news' => 0,
            'show_at_slider' => 0,
            'show_at_popular' => 0,
            'is_approved' => 0,
            'status' => 1,
            'views' => 0,
            'created_at' => $now->copy()->subMinutes(45),
            'updated_at' => $now->copy()->subMinutes(45),
        ]);

        $tagId = DB::table('tags')->insertGetId([
            'name' => 'reader submitted',
            'language' => 'en',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('news_tags')->insert([
            'news_id' => $submissionId,
            'tag_id' => $tagId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedPages(array $images, $now): void
    {
        $aboutEn = <<<HTML
<h2>About Extremis News</h2>
<p class="lead"><strong>Extremis News</strong> (published at <a href="https://extremis.top">https://extremis.top</a>) is an independent digital news and investigative media publication dedicated to delivering timely, verified, and high-impact coverage across National Affairs, Global Business, Emerging Technology, Climate Science, Public Health, and Contemporary Culture.</p>

<h3>Publisher & Ownership Transparency</h3>
<p>Extremis News is founded, owned, and published by <strong>Fahim Ahamed</strong> (<a href="https://fahimweb.com" target="_blank" rel="noopener">fahimweb.com</a>), a technology strategist and digital media publisher dedicated to ethical, objective, and evidence-based journalism. Editorial operations are maintained with full transparency, ensuring zero external commercial or political influence over our reporting.</p>

<h3>Our Journalistic Mission</h3>
<p>In an era of algorithmic noise and unverified rumors, our mission is to provide clear, actionable, and rigorously vetted news. We believe independent journalism is fundamental to democratic governance, economic resilience, and civic empowerment. Our newsroom upholds the highest standards of the Society of Professional Journalists (SPJ) Code of Ethics.</p>

<h3>Editorial Standards & Fact-Checking Policy</h3>
<ul>
    <li><strong>Primary Source Verification:</strong> Every factual claim is verified through primary documentation, government gazettes, peer-reviewed scientific journals, or on-the-record eyewitness accounts.</li>
    <li><strong>Unbiased Reporting:</strong> We maintain strict separation between objective news reporting, analytical investigative features, and editorial opinion pieces.</li>
    <li><strong>Transparent Corrections:</strong> When factual errors occur, we issue immediate, clearly labeled corrections within the original article, detailing what was altered and why.</li>
    <li><strong>Editorial Independence:</strong> Advertisers, commercial partners, and corporate sponsors have zero influence over news coverage or story selection.</li>
</ul>

<h3>Editorial Masthead & Leadership</h3>
<div class="row mt-3 mb-4">
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">Fahim Ahamed</h5>
            <p class="text-primary font-weight-bold mb-1">Founder, Publisher & Editorial Director</p>
            <p class="small text-muted mb-0">Oversees strategic editorial vision, digital architecture, and journalistic compliance.<br>
            Email: <a href="mailto:fahimahamedweb@gmail.com">fahimahamedweb@gmail.com</a> | Portfolio: <a href="https://fahimweb.com" target="_blank">fahimweb.com</a></p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">Nadia Rahman</h5>
            <p class="text-primary font-weight-bold mb-1">Senior Managing Editor</p>
            <p class="small text-muted mb-0">Leads daily newsroom workflow, investigative coverage, and editorial fact-checking.</p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">Arif Hasan</h5>
            <p class="text-primary font-weight-bold mb-1">Lead Technology & Markets Correspondent</p>
            <p class="small text-muted mb-0">Specializes in artificial intelligence, software developments, fintech, and macroeconomic policy.</p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">Editorial Verification Desk</h5>
            <p class="text-primary font-weight-bold mb-1">Fact-Checking & Wire Monitoring</p>
            <p class="small text-muted mb-0">Monitors live wire updates, cross-references sources, and coordinates community feedback.</p>
        </div>
    </div>
</div>

<h3>Contacting Our Newsroom</h3>
<p>We actively welcome tips, press releases, story leads, and corrections. You may contact our newsroom directly via email at <a href="mailto:fahimahamedweb@gmail.com">fahimahamedweb@gmail.com</a> or visit our dedicated <a href="/contact">Contact Page</a>.</p>
HTML;

        $aboutBn = <<<HTML
<h2>এক্সট্রিমিস নিউজ সম্পর্কে</h2>
<p class="lead"><strong>এক্সট্রিমিস নিউজ</strong> (অনলাইন ঠিকানা: <a href="https://extremis.top">https://extremis.top</a>) একটি নির্ভরযোগ্য, স্বাধীন ও অনুসন্ধানী ডিজিটাল সংবাদ মাধ্যম। আমরা জাতীয় বিষয়াবলি, বৈশ্বিক অর্থনীতি, উদীয়মান প্রযুক্তি, জলবায়ু বিজ্ঞান, জনস্বাস্থ্য এবং সংস্কৃতির উপর বস্তুনিষ্ঠ সংবাদ পরিবেশন করি।</p>

<h3>মালিকানা ও প্রকাশকের পরিচিতি</h3>
<p>এক্সট্রিমিস নিউজের প্রতিষ্ঠাতা, স্বত্বাধিকারী এবং প্রকাশক হলেন <strong>ফাহিম আহমেদ</strong> (<a href="https://fahimweb.com" target="_blank" rel="noopener">fahimweb.com</a>)। তিনি তথ্যপ্রযুক্তি ও ডিজিটাল গণমাধ্যম খাতের একজন পেশাদার ব্যক্তিত্ব। আমাদের সম্পাদকীয় নীতি সম্পূর্ণ স্বাধীন ও পক্ষপাতহীন।</p>

<h3>আমাদের সম্পাদকীয় নীতিমালা</h3>
<ul>
    <li><strong>প্রাথমিক উৎস যাচাই:</strong> যেকোনো তথ্য প্রকাশের পূর্বে সরকারি দলিল, প্রাতিষ্ঠানিক তথ্য ও সংশ্লিষ্টদের সরাসরি বক্তব্যের মাধ্যমে যাচাই করা হয়।</li>
    <li><strong>বস্তুনিষ্ঠতা:</strong> তথ্যের সাথে ব্যক্তিগত মতামতের মিশ্রণ পরিহার করে পেশাদারিত্ব বজায় রাখা হয়।</li>
    <li><strong>ভুল সংশোধন নীতি:</strong> কোনো তথ্যে অসঙ্গতি দৃষ্টিগোচর হলে তা অবিলম্বে স্পষ্ট সংশোধনী নোটিশ সহ আপডেট করা হয়।</li>
    <li><strong>সম্পাদকীয় স্বাধীনতা:</strong> বিজ্ঞাপনদাতা বা কোনো বাণিজ্যিক প্রতিষ্ঠানের দ্বারা সম্পাদকীয় সিদ্ধান্ত প্রভাবিত হয় না।</li>
</ul>

<h3>সম্পাদকীয় পর্ষদ (মাস্টহেড)</h3>
<div class="row mt-3 mb-4">
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">ফাহিম আহমেদ</h5>
            <p class="text-primary font-weight-bold mb-1">প্রতিষ্ঠাতা, প্রকাশক ও প্রধান পরিচালক</p>
            <p class="small text-muted mb-0">সার্বিক সম্পাদকীয় রূপরেখা ও প্রযুক্তিগত অবকাঠামো তত্ত্বাবধায়ক।<br>
            ইমেইল: <a href="mailto:fahimahamedweb@gmail.com">fahimahamedweb@gmail.com</a> | ওয়েবসাইট: <a href="https://fahimweb.com" target="_blank">fahimweb.com</a></p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">নাদিয়া রহমান</h5>
            <p class="text-primary font-weight-bold mb-1">সিনিয়র বার্তা সম্পাদক</p>
            <p class="small text-muted mb-0">দৈনিক সংবাদ প্রবাহ ও অনুসন্ধানী ডেস্ক পরিচালনা করেন।</p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">আরিফ হাসান</h5>
            <p class="text-primary font-weight-bold mb-1">প্রধান প্রযুক্তি ও বাণিজ্য প্রতিবেদক</p>
            <p class="small text-muted mb-0">কৃত্রিম বুদ্ধিমত্তা, অর্থনীতি ও বাজার বিশ্লেষণ নিয়ে নিয়মিত লেখেন।</p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="p-3 bg-light rounded border">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">সম্পাদকীয় যাচাই ডেস্ক</h5>
            <p class="text-primary font-weight-bold mb-1">তথ্য যাচাই ও পাঠক প্রতিক্রিয়া</p>
            <p class="small text-muted mb-0">সংবাদ উৎসের সত্যতা যাচাই এবং পাঠকের মতামত পর্যালোচনা করে।</p>
        </div>
    </div>
</div>

<h3>যোগাযোগ</h3>
<p>যেকোনো সংবাদ বিজ্ঞপ্তি, তথ্য বা সংশোধনের প্রয়োজনে সরাসরি যোগাযোগ করুন: <a href="mailto:fahimahamedweb@gmail.com">fahimahamedweb@gmail.com</a> অথবা আমাদের <a href="/contact">যোগাযোগ পেজ</a> দেখুন।</p>
HTML;

        DB::table('abouts')->insert([
            [
                'language' => 'en',
                'content' => $aboutEn,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'language' => 'bn',
                'content' => $aboutBn,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('contacts')->insert([
            [
                'language' => 'en',
                'address' => 'House 42, Road 7, Dhanmondi, Dhaka 1209, Bangladesh',
                'phone' => '+880 1700 123456',
                'email' => 'fahimahamedweb@gmail.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'language' => 'bn',
                'address' => 'বাড়ি ৪২, সড়ক ৭, ধানমন্ডি, ঢাকা ১২০৯, বাংলাদেশ',
                'phone' => '+৮৮০ ১৭০০ ১২৩৪৫৬',
                'email' => 'fahimahamedweb@gmail.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('received_mails')->insert([
            'email' => 'reader@example.com',
            'subject' => 'Correction request for a local story',
            'message' => 'Please review the spelling of a school name in the education update.',
            'seen' => 0,
            'replied' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedFooter(array $images, $now): void
    {
        foreach ([
            'en' => [
                'description' => 'Extremis News publishes verified updates for readers who need clear, useful reporting without noise.',
                'copyright' => 'Copyright '.date('Y').' Extremis News. All rights reserved.',
                'titles' => [
                    'grid_one_title' => 'Company',
                    'grid_two_title' => 'News Sections',
                    'grid_three_title' => 'Reader Services',
                ],
                'links' => [
                    'footer_grid_ones' => [
                        ['About Us', url('/about')],
                        ['Contact', url('/contact')],
                        ['Latest News', url('/news')],
                    ],
                    'footer_grid_twos' => [
                        ['National', url('/news?category=national')],
                        ['Business', url('/news?category=business')],
                        ['Technology', url('/news?category=technology')],
                        ['Sports', url('/news?category=sports')],
                    ],
                    'footer_grid_threes' => [
                        ['Advertise', url('/contact')],
                        ['Send a Tip', url('/contact')],
                        ['Newsletter', url('/')],
                    ],
                ],
            ],
            'bn' => [
                'description' => 'এক্সট্রিমিস নিউজ পাঠকের জন্য যাচাই করা, পরিষ্কার ও প্রয়োজনীয় সংবাদ প্রকাশ করে।',
                'copyright' => 'কপিরাইট '.date('Y').' এক্সট্রিমিস নিউজ। সর্বস্বত্ব সংরক্ষিত।',
                'titles' => [
                    'grid_one_title' => 'প্রতিষ্ঠান',
                    'grid_two_title' => 'সংবাদ বিভাগ',
                    'grid_three_title' => 'পাঠক সেবা',
                ],
                'links' => [
                    'footer_grid_ones' => [
                        ['আমাদের সম্পর্কে', url('/about')],
                        ['যোগাযোগ', url('/contact')],
                        ['সর্বশেষ সংবাদ', url('/news')],
                    ],
                    'footer_grid_twos' => [
                        ['জাতীয়', url('/news?category=national-bn')],
                        ['ব্যবসা', url('/news?category=business-bn')],
                        ['প্রযুক্তি', url('/news?category=technology-bn')],
                        ['খেলা', url('/news?category=sports-bn')],
                    ],
                    'footer_grid_threes' => [
                        ['বিজ্ঞাপন', url('/contact')],
                        ['সংবাদ পাঠান', url('/contact')],
                        ['নিউজলেটার', url('/')],
                    ],
                ],
            ],
        ] as $language => $footer) {
            DB::table('footer_infos')->insert([
                'language' => $language,
                'logo' => $images['logo'],
                'description' => $footer['description'],
                'copyright' => $footer['copyright'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($footer['titles'] as $key => $value) {
                DB::table('footer_titles')->insert([
                    'key' => $key,
                    'value' => $value,
                    'language' => $language,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($footer['links'] as $table => $links) {
                foreach ($links as [$name, $url]) {
                    DB::table($table)->insert([
                        'language' => $language,
                        'name' => $name,
                        'url' => $url,
                        'status' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function seedSocials($now): void
    {
        foreach ([
            ['fab fa-facebook-f', 'https://facebook.com/extremisnews'],
            ['fab fa-youtube', 'https://youtube.com/@extremisnews'],
            ['fab fa-instagram', 'https://instagram.com/extremisnews'],
            ['fab fa-linkedin-in', 'https://www.linkedin.com/in/fahimahamedweb'],
            ['fa fa-globe', 'https://fahimweb.com'],
        ] as [$icon, $url]) {
            DB::table('social_links')->insert([
                'icon' => $icon,
                'url' => $url,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            'en' => [
                ['fab fa-facebook-f', '125K', 'Followers', 'Follow', '#1877f2', 'https://facebook.com/extremisnews'],
                ['fab fa-youtube', '82K', 'Subscribers', 'Subscribe', '#ff0000', 'https://youtube.com/@extremisnews'],
                ['fab fa-linkedin-in', '35K', 'Connections', 'Connect', '#0077b5', 'https://www.linkedin.com/in/fahimahamedweb'],
                ['fab fa-instagram', '46K', 'Followers', 'Follow', '#c13584', 'https://instagram.com/extremisnews'],
            ],
            'bn' => [
                ['fab fa-facebook-f', '১২৫K', 'ফলোয়ার', 'ফলো করুন', '#1877f2', 'https://facebook.com/extremisnews'],
                ['fab fa-youtube', '৮২K', 'সাবস্ক্রাইবার', 'সাবস্ক্রাইব', '#ff0000', 'https://youtube.com/@extremisnews'],
                ['fab fa-linkedin-in', '৩৫K', 'কানেকশন', 'যুক্ত হোন', '#0077b5', 'https://www.linkedin.com/in/fahimahamedweb'],
                ['fab fa-instagram', '৪৬K', 'ফলোয়ার', 'ফলো করুন', '#c13584', 'https://instagram.com/extremisnews'],
            ],
        ] as $language => $socialCounts) {
            foreach ($socialCounts as [$icon, $count, $type, $button, $color, $url]) {
                DB::table('social_counts')->insert([
                    'language' => $language,
                    'icon' => $icon,
                    'fan_count' => $count,
                    'fan_type' => $type,
                    'button_text' => $button,
                    'color' => $color,
                    'url' => $url,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('subscribers')->insert([
            ['email' => 'morning.reader@example.com', 'created_at' => $now, 'updated_at' => $now],
            ['email' => 'weekly.digest@example.com', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
