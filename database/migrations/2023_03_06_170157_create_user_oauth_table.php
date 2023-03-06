<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_oauth', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id')->comment('用户ID');
            $table->string('uuid')->comment('UUID');
            $table->string('username')->nullable()->comment('用户名');
            $table->string('password')->nullable()->comment('密码');
            $table->string('nickname')->nullable()->comment('昵称');
            $table->string('avatar')->nullable()->comment('头像');
            $table->string('source')->comment('来源');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_oauth');
    }
};
