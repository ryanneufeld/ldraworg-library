<?php

namespace App\Jobs\Release;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

use App\Jobs\RenderFile;
use App\Models\Part;
use App\Models\PartRelease;
use App\LDraw\LibraryOperations;
use App\LDraw\ZipFiles;

class PostReleaseCleanup implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;
    public $timeout = 3600;

    protected $ids;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $ids)
    {
      $this->ids = $ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      if (!empty($this->batch()->id) && $this->batch()->cancelled()) {
        return;
      }

      // Zero/null out vote and flag data
      Part::official()->update([
        'uncertified_subpart_count' => 0, 
        'vote_summary' => null, 
        'vote_sort' => 1, 
        'delete_flag' => 0, 
        'minor_edit_data' => null,
        'missing_parts' => null]);
      Part::official()->each(function (Part $p) {
        $p->votes()->delete();
        $p->notification_users()->sync([]);
      });

      // Regenerate the images of affected parts
      foreach(Part::where('part_release_id', PartRelease::current()->id)->lazy() as $part) {
        if (!in_array($part->id, $this->ids)) $this->ids[] = $part->id;
        LibraryOperations::getAllParentIds($part, $this->ids);
      }
      foreach($this->ids as $id) {
        $this->batch()->add(new RenderFile(Part::find($id)));
      }

      // Save the new non-part files
      $sdisk = config('ldraw.staging_dir.disk');
      $spath = config('ldraw.staging_dir.path');

      foreach (Storage::disk($sdisk)->allFiles("$spath/ldraw") as $filename) {
        $content = Storage::disk($sdisk)->get($filename);
        $f = str_replace("$spath/ldraw/", 'official/', $filename);
        Storage::disk('library')->put($f, $content);
      }

      // Remove all the temp files
      Storage::disk($sdisk)->deleteDirectory($spath);
      Storage::disk($sdisk)->makeDirectory($spath);
      
      // Reset the unofficial zip file
      Storage::disk('library')->delete('unofficial/ldrawunf.zip');
      ZipFiles::unofficialZip(Part::unofficial()->first());
    }
}
