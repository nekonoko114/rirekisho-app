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
        Schema::table('resumes', function (Blueprint $table) {
            if (! Schema::hasColumn('resumes', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            if (Schema::hasColumn('resumes', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }
        });
    }
};
