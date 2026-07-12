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
            if (! Schema::hasColumn('resumes', 'address_postal')) {
                $table->string('address_postal')->nullable()->after('address');
            }
            if (! Schema::hasColumn('resumes', 'contact_postal')) {
                $table->string('contact_postal')->nullable()->after('contact_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            if (Schema::hasColumn('resumes', 'address_postal')) {
                $table->dropColumn('address_postal');
            }
            if (Schema::hasColumn('resumes', 'contact_postal')) {
                $table->dropColumn('contact_postal');
            }
        });
    }
};
