<?php

namespace App\Console\Commands;

use App\Models\Part;
use Illuminate\Console\Command;

class OfficialCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:official-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Official Library Cleanup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Part::official()->update([
            'uncertified_subpart_count' => 0,
            'vote_summary' => null,
            'vote_sort' => 1,
            'delete_flag' => 0,
            'minor_edit_data' => null,
            'missing_parts' => null,
            'manual_hold_flag' => 0,
            'marked_for_release' => false,
        ]);
        Part::official()->each(function (Part $p) {
            $p->votes()->delete();
            $p->notification_users()->sync([]);
        });
    }
}
