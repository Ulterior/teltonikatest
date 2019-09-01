<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();
        DB::table('users')->delete();

        $admin_role = new App\Role();
        $admin_role->name = 'admin';
        $admin_role->save();

        $admin_user = new App\User();
        $admin_user->name = 'Admin';
        $admin_user->email = 'admin@teltonika.lt';
        $admin_user->password = Hash::make('teltonika_admin');
        $admin_user->confirmation_code = md5(uniqid(mt_rand(), true));
        $admin_user->confirmed = 1;
        $admin_user->save();

        $admin_user->attachRole($admin_role);

        $simple_role = new App\Role();
        $simple_role->name = 'user';
        $simple_role->save();

        $special_user = new App\User();
        $special_user->name = 'Leo';
        $special_user->email = 'leonardas.survila@gmail.com';
        $special_user->password = Hash::make('teltonika_user');
        $special_user->confirmation_code = md5(uniqid(mt_rand(), true));
        $special_user->confirmed = 1;
        $special_user->save();

        $special_user->attachRole($simple_role);

        factory(App\Todo::class, mt_rand(1,50))->create([
          'user_id' => $special_user->id,
        ]);

        // create 10 users using the user factory
        factory(App\User::class, 10)->create()->each(function ($user) use ($simple_role){
          $user->attachRole($simple_role);

          factory(App\Todo::class, mt_rand(1,5))->create([
            'user_id' => $user->id,
          ]);
        });
    }
}
