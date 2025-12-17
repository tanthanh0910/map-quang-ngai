<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->after('id')->index();
        });

        // If there are existing place_types, try to map existing 'type' string to place_types.id
        if (Schema::hasColumn('places', 'type') && Schema::hasTable('place_types')) {
            $places = DB::table('places')->select('id','type')->get();
            foreach ($places as $p) {
                $pt = DB::table('place_types')->where('name', $p->type)->first();
                if ($pt) {
                    DB::table('places')->where('id', $p->id)->update(['type_id' => $pt->id]);
                }
            }
        }

        // drop old 'type' column
        if (Schema::hasColumn('places', 'type')) {
            Schema::table('places', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // add foreign key if desired (optional)
        if (Schema::hasTable('place_types')) {
            Schema::table('places', function (Blueprint $table) {
                $table->foreign('type_id')->references('id')->on('place_types')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // remove foreign key
        if (Schema::hasTable('places') && Schema::hasColumn('places', 'type_id')) {
            Schema::table('places', function (Blueprint $table) {
                $table->dropForeign(['type_id']);
            });
        }

        Schema::table('places', function (Blueprint $table) {
            // add type back as string
            $table->string('type')->nullable()->after('id');
        });

        // try to map back type_id to type name
        if (Schema::hasColumn('places', 'type_id') && Schema::hasTable('place_types')) {
            $places = DB::table('places')->select('id','type_id')->get();
            foreach ($places as $p) {
                $pt = DB::table('place_types')->where('id', $p->type_id)->first();
                if ($pt) {
                    DB::table('places')->where('id', $p->id)->update(['type' => $pt->name]);
                }
            }
        }

        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn('type_id');
        });
    }
};
