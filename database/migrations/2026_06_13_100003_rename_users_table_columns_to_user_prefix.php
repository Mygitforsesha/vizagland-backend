<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        DB::statement('ALTER TABLE users CHANGE name user_full_name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE users CHANGE email user_email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users CHANGE phone user_phone VARCHAR(20) NULL');
        DB::statement('ALTER TABLE users CHANGE password user_password VARCHAR(255) NOT NULL');
        DB::statement("ALTER TABLE users CHANGE role user_role VARCHAR(255) NOT NULL DEFAULT 'agent'");

        Schema::table('users', function (Blueprint $table) {
            $table->index('user_role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_role']);
        });

        DB::statement('ALTER TABLE users CHANGE user_full_name name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE users CHANGE user_email email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users CHANGE user_phone phone VARCHAR(20) NULL');
        DB::statement('ALTER TABLE users CHANGE user_password password VARCHAR(255) NOT NULL');
        DB::statement("ALTER TABLE users CHANGE user_role role VARCHAR(255) NOT NULL DEFAULT 'agent'");

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }
};
