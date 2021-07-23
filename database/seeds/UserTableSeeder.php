<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Spatie\Permission\Models\Role::findOrCreate('super');
        $user = new \App\Models\User([
            'name' => 'adrian hardy',
            'email' => 'adrian@hardyfinch.co.uk',
            'password' => bcrypt('password'),
            'api_token' => \Illuminate\Support\Str::random(128)
        ]);
        $user->save();
        $user->assignRole(['super']);
        $user = new \App\Models\User([
            'name' => 'Will Strettle',
            'email' => 'will@orchardcrownmall.co.uk',
            'password' => bcrypt('password'),
            'api_token' => \Illuminate\Support\Str::random(128)
        ]);
        $user->save();
        $user->assignRole(['super']);
    }
}
