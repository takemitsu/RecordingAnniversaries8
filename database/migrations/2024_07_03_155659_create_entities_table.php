<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            // ユーザ名ID
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // 要素名(グループ名)
            $table->string('name',255);
            // 説明
            $table->text('desc')->nullable();
            // ステータス(listに出すか)
            $table->unsignedTinyInteger('status')->default(0);
            // 作成日
            $table->timestamps();
            // 論理削除
            $table->softDeletes();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
