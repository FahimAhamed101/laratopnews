<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE news MODIFY views BIGINT UNSIGNED NOT NULL DEFAULT 0');

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE news ADD COLUMN views_new INTEGER NOT NULL DEFAULT 0');
            DB::statement('UPDATE news SET views_new = views');
            DB::statement('ALTER TABLE news DROP COLUMN views');
            DB::statement('ALTER TABLE news RENAME COLUMN views_new TO views');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE news MODIFY views TINYINT(1) NOT NULL DEFAULT 0');
        }
    }
};
