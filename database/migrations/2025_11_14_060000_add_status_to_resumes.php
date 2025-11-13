<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('public_token');
        });
    }

    public function down()
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
