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
        Schema::table('places', function (Blueprint $table) {
            // thumbnail: filename or URL to image
            $table->string('thumbnail')->nullable()->after('icon');
            // time: text describing opening hours or relevant time info
            $table->string('time')->nullable()->after('thumbnail');
            // description: longer text
            $table->text('description')->nullable()->after('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn(['thumbnail', 'time', 'description']);
        });
    }
};
