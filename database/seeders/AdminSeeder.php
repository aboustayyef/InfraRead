<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
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
        $admin = new User();
        $admin->name = env('ADMIN_NAME');
        $admin->email = env('ADMIN_EMAIL');
        $admin->password = bcrypt(env('ADMIN_PASSWORD'));
        $admin->email_verified_at = new Carbon();
        $admin->save();
    }
}
