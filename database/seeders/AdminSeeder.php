<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use App\Models\Category;
use App\Models\News;
use App\Models\Ad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use DB;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        $admin = new Admin();
        $admin->image ='uploads/EKIABPcZLJ0EvV2kLoMM4eST6n8whJ.jpg';
        $admin->name='Super Admin';
        $admin->email='admin@gmail.com';
        $admin->password=Hash::make('12345678');//password
        $admin->status =1;
        
        $admin->save();
        $admin = new Admin();
        $admin->image ='/test';
        $admin->name='Writer';
        $admin->email='writer@gmail.com';
        $admin->password=Hash::make('12345678');//password
        $admin->status =1;
        
        $admin->save();
       Ad::firstOrCreate(
            ['id' => 1],
            [
                'home_top_bar_ad' => 'uploads/09UF0tD3WF9xMt1EDoxnjYB7XXY5q8.png',
                'home_top_bar_ad_status' => 1,
                'home_top_bar_ad_url' => '#', // Add this field if missing
                'home_middle_ad' => 'uploads/m5IQnCsK9d3nO4nO6vKEK6rBbf7cgT.png',
                'home_middle_ad_status' => 1,
                'home_middle_ad_url' => '#',
                'view_page_ad' => 'uploads/ZzaCdWrej3oyDP5ABJ6eManq8LTRDY.png',
                'view_page_ad_status' => 1,
                'view_page_ad_url' => '#',
                'news_page_ad' => 'uploads/1MvD8y9Qoy3d9rBDd7nIkCDMWxdXVm.png',
                'news_page_ad_status' => 1,
                'news_page_ad_url' => '#',
                'side_bar_ad' => 'uploads/Gr5Mp1wLpmifc145IM01bFEVJzq9dC.png',
                'side_bar_ad_status' => 1,
                'side_bar_ad_url' => '#',
            ]
        );
        \DB::table('roles')->insert(
            [
                ['id' => 4, 'name' => 'Super Admin', 'guard_name' => 'admin'],
              
            ]
        );
        \DB::table('settings')->insert(
            [
                ['key'=>'site_name','value'=>'News'],
                ['key'=>'site_logo','value'=>'uploads/fcGC0SXmYvI0kepIBB1usluTLpoPms.png'],
                ['key'=>'site_favicon' ,'value'=>'uploads/8KfHnM4inZPpdfjzZ0zdRenKRRKLjK.png'],
                ['key'=>'site_seo_title' ,'value'=>'Top News'],
                ['key'=>'site_seo_description' ,'value'=>'Placeat commodo sus'],
                ['key'=> 'site_seo_keywords' ,'value'=>'Graham Howard'],
                ['key'=> 'site_color' ,'value'=> '#d31066'],
                ['key'=>  'site_microsoft_api_host' ,'value'=>  'microsoft-translator-text.p.rapidapi.com'],
                ['key'=>   'site_microsoft_api_key','value'=>  '8f9becca73msh0ee8ad5b8269c32p1b84b8jsn34d3719c2fe1'],
               
            ]
        );
        \DB::table('categories')->insert(
            [
                ['name' => 'Sports', 'language' => 'en', 'slug' => 'Sports','show_at_nav' => '1','status' => '1'],
                ['name' => 'Thể thao', 'language' => 'vi', 'slug' => 'Thể thao','show_at_nav' => '1','status' => '1'],
                ['name' => 'Entertainment', 'language' => 'en', 'slug' => 'Entertainment','show_at_nav' => '1','status' => '1'],
                ['name' => 'Society', 'language' => 'en', 'slug' => 'Society','show_at_nav' => '1','status' => '1'],
                ['name' => 'Economy', 'language' => 'en', 'slug' => 'Economy','show_at_nav' => '1','status' => '1'],
            ]
        );
        \DB::table('news')->insert(
            [
                ['title' => '1.6 million rural workers in Mekong Delta to be trained in agriculture','content' => '<h1 class="headline" style="margin-bottom: 10px; font-weight: 400; line-height: 4.6rem; font-size: 3.6rem; font-family: PlayfairDisplay, serif; color: rgb(51, 51, 51);">1.6 million rural workers in Mekong Delta to be trained in agriculture</h1>','image' =>'uploads/QEjizKuB3lVOdNhDmLDqDlQmXJFmUo.webp', 'language' => 'en', 'slug' => 'mikel-arteta-admits-he-didnt-know-martin-odegaard-was-going-to-take-arsenals-penalty-in-their-win-at-crystal-palace','is_breaking_news' => '1','status' => '1','is_approved' => '1','views' => '1','show_at_popular' => '1','category_id' => '1','author_id' => '1'],
             
            ]
        );

        \DB::table('languages')->insert(
            [
                ['name' => 'English', 'lang' => 'en', 'slug' => 'en','default' => '1','status' => '1'],
                ['name' => 'Vietnamese', 'lang' => 'vi', 'slug' => 'vi','default' => '0','status' => '0'],
            ]
        );
        \DB::table('tags')->insert(
            [
                ['name' => 'economy', 'language' => 'en',],
             
            ]
        );
        \DB::table('news_tags')->insert(
            [
                ['news_id' => '1', 'tag_id' => '1',],
             
            ]
        );
        \DB::table('model_has_roles')->insert(
            [
                ['role_id' => 4, 'model_type' => 'App\Models\Admin', 'model_id' => '1'],
                
            ]
        );
       
       
    }
}