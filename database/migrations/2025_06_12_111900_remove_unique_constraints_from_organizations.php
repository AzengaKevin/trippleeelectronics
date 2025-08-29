<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            if ($dbDriver === 'sqlite') {
                return;
            }

            $table->dropUnique(['name']);
            $table->dropUnique(['email']);
            $table->dropUnique(['kra_pin']);
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            if ($dbDriver === 'sqlite') {
                return;
            }

            $table->unique('name');
            $table->unique('email');
            $table->unique('kra_pin');
        });
    }
};
