<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Register the user seeder
        $this->call(UsersTableSeeder::class);

        DB::table('syslogs')->delete();

        (new \App\Syslog)->register('database seeded');
    }
}
