<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // メールアドレスは使わず、DB 側で自動発行する UUID を識別子に用いる。
            // MySQL 8.0.13+ の式デフォルトを利用（INSERT 時に UUID() が自動採番される）。
            $table->uuid('uuid')->unique()->default(DB::raw('(UUID())'));
            $table->string('name');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // email を使わないため、リセットトークンは users.uuid を主キーとして管理する。
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
