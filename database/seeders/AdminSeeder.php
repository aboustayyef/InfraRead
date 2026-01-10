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
        $admin->name = config('infraread.admin.name');
        $admin->email = config('infraread.admin.email');
        $admin->password = bcrypt((string) config('infraread.admin.password'));
        $admin->email_verified_at = new Carbon();
        $admin->save();
    }
}
