<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copy existing soop_streamers rows into the unified streamers table
     * with platform='soop'. Idempotent — safe to re-run if rollback/re-run
     * happens mid-deploy.
     */
    public function up(): void
    {
        // Defensive: skip silently if the old table was already dropped
        // (e.g. fresh install, or this migration ran twice).
        if (! Schema::hasTable('soop_streamers')) {
            return;
        }

        $rows = DB::table('soop_streamers')->get();

        foreach ($rows as $row) {
            DB::table('streamers')->updateOrInsert(
                ['platform' => 'soop', 'user_id' => $row->user_id],
                [
                    'label'      => $row->label,
                    'race'       => $row->race,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
            );
        }
    }

    /**
     * Reverse path — wipe migrated rows so a re-run of `up()` is clean.
     * We only delete the soop platform rows; never touch twitch.
     */
    public function down(): void
    {
        DB::table('streamers')->where('platform', 'soop')->delete();
    }
};