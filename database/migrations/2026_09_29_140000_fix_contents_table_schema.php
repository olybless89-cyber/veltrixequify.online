<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/*
 * Repairs the `contents` table when it's missing the `name` column
 * (and the rows it should hold). The raw SQL import in
 * database/matrix_database.sql normally creates this table, but on a
 * shared database an interrupted or partially-conflicting import can
 * leave it absent or short a column. This migration is idempotent
 * and additive only -- it never drops or truncates anything, so it's
 * safe to run repeatedly and safe on a database also used by another
 * app.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('contents')) {
            Schema::create('contents', function (Blueprint $table) {
                $table->unsignedInteger('id')->primary();
                $table->string('name', 191)->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('contents', 'name')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->string('name', 191)->nullable()->after('id');
            });
        }

        $rows = [
            [7, 'counter'], [8, 'counter'], [9, 'counter'], [10, 'counter'],
            [15, 'service'], [16, 'service'], [17, 'service'],
            [18, 'testimonial'], [19, 'testimonial'],
            [33, 'support'], [34, 'support'],
            [37, 'how-it-work'], [38, 'how-it-work'], [39, 'how-it-work'], [40, 'how-it-work'],
            [56, 'social'], [58, 'social'], [59, 'social'], [60, 'social'],
            [61, 'blog'], [62, 'blog'], [63, 'blog'],
            [64, 'feature'], [65, 'feature'], [66, 'feature'],
            [67, 'why-chose-us'], [68, 'why-chose-us'], [69, 'why-chose-us'],
            [70, 'why-chose-us'], [71, 'why-chose-us'], [72, 'why-chose-us'],
            [74, 'testimonial'],
            [76, 'how-we-work'], [77, 'how-we-work'], [78, 'how-we-work'],
            [83, 'know-more-us'], [84, 'know-more-us'], [85, 'know-more-us'], [86, 'know-more-us'],
            [88, 'faq'],
        ];

        $now = now();
        foreach ($rows as [$id, $name]) {
            DB::table('contents')->updateOrInsert(
                ['id' => $id],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down()
    {
        // Intentionally no-op: this migration only repairs missing
        // schema/data, so there is nothing safe to roll back.
    }
};
