<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng users được tạo bằng SQL script thủ công (xem database/migrations/migrations/001_create_users.sql)
 * nhưng thiếu cột remember_token — cần thiết cho "Ghi nhớ đăng nhập" của Laravel Auth.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->rememberToken();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
