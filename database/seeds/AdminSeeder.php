<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    
    public function run()
    {
        // Erase old Data
        DB::table('users')->truncate();
        
        $admin = new User;
        $admin->name = env('ADMIN_NAME');
        $admin->email = env('ADMIN_EMAIL');
        $admin->password = bcrypt(env('ADMIN_PASSWORD'));
        $admin->save();
    }
}
