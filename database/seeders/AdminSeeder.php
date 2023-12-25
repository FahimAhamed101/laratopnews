<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
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
        $admin->image ='/test';
        $admin->name='Super Admin';
        $admin->email='admin@admin.com';
        $admin->password=Hash::make('admin');//password
        $admin->status =1;
        
        $admin->save();
        \DB::table('roles')->insert(
            [
                ['id' => 4, 'name' => 'Super Admin', 'guard_name' => 'admin'],
                
            ]
        );
        \DB::table('model_has_roles')->insert(
            [
                ['role_id' => 4, 'model_type' => 'App\Models\Admin', 'model_id' => '1'],
                
            ]
        );
        
       
    }
}