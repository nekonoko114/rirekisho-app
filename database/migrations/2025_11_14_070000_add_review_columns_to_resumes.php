<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
        });
    }

    public function down()
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropColumn(['reviewed_at','reviewed_by']);
        });
    }
};
