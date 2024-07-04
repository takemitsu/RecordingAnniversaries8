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
        Schema::create('days', function (Blueprint $table) {
            $table->id('id');
            // 要素(グループ)ID
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            // 名前
            $table->string('name',255);
            // 説明
            $table->text('desc')->nullable();
            // 日付
            $table->date('anniv_at');
            // 作成日、更新日
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
        Schema::dropIfExists('days');
    }
};
