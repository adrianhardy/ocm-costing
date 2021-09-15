<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token', 128)->unique()->nullable();
        });



        $super = \Spatie\Permission\Models\Role::findOrCreate('super');
        $supplier = \Spatie\Permission\Models\Role::findOrCreate('supplier');
        $admin = \Spatie\Permission\Models\Role::findOrCreate('admin');
        \App\Models\User::all()->each(function(\App\Models\User $user) use ($super, $admin) {
            $user->api_token = uuid_create();
            $user->save();
            $user->syncRoles([$super, $admin]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
}
