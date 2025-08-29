<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('individuals', function (Blueprint $table) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            if ($dbDriver === 'sqlite') {
                return;
            }

            $table->dropUnique(['username']);
            $table->dropUnique(['email']);
            $table->dropUnique(['phone']);
            $table->dropUnique(['kra_pin']);
            $table->dropUnique(['id_number']);
        });
    }

    public function down(): void
    {
        Schema::table('individuals', function (Blueprint $table) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            if ($dbDriver === 'sqlite') {
                return;
            }
            $table->unique('username');
            $table->unique('email');
            $table->unique('phone');
            $table->unique('kra_pin');
            $table->unique('id_number');
        });
    }
};
