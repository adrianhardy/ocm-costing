<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProtectedToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false);
        });

        foreach(['super', 'admin', 'supplier'] as $role) {
            $dbRole = \Spatie\Permission\Models\Role::findOrCreate($role);
            $dbRole->is_protected = true;
            $dbRole->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_protected');
        });
    }
}
