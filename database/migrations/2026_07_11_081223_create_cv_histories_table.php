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
        // 旧タイムスタンプ(081222)で cvs より先に実行され外部キー作成に失敗した
        // 環境には、制約なしの空テーブルが残っているため作り直す
        Schema::dropIfExists('cv_histories');

        Schema::create('cv_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable(); // 会社名
            $table->integer('start_year')->nullable();
            $table->integer('start_month')->nullable();
            $table->integer('end_year')->nullable();
            $table->integer('end_month')->nullable();
            $table->text('job_description')->nullable(); // 業務内容
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_histories');
    }
};
