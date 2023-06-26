<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class PartRelease extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'short', 'notes', 'created_at', 'part_data'];

    protected $casts = [
      'part_list' => AsArrayObject::class,
      'part_data' => AsArrayObject::class,
    ];

    public function parts() {
      return $this->hasMany(Parts::class);
    }
    
    public function toString() {
      return $this->short == 'original' ? " ORIGINAL" : " UPDATE {$this->name}";
    }

    public static function unofficial() {
      return null; // self::firstWhere('short','unof');
    }

    public static function current() {
      return self::latest()->first();
    }      
    
    // Note this is best called as a queued process
    public static function createRelease(Collection $parts, User $user): void {
      //Figure out next update number
      $current = self::current();
      $year = date_create();
      if (date_format($year, 'Y') != date_format(date_create($current->created_at), 'Y')) {
        $name = date_format($year, 'Y') . "-01";
        $short = date_format($year, 'y') . '01';
      }
      else {
        $num = (int) substr($current->name,-2) + 1;
        if ($num <= 9) {
          $num = "0$num";
        }
        $name = date_format($year, 'Y') . "-$num";
        $short = date_format($year, 'y') . $num;
      }

      // create release
      $release = PartRelease::create(['name' => $name, 'short' => $short, 'part_data' => self::getReleaseData($parts->pluck('id')->all())]);

      $sdisk = config('ldraw.staging_dir.disk');
      $spath = config('ldraw.staging_dir.path');
      if (!Storage::disk($sdisk)->exists($spath))
        Storage::disk($sdisk)->makeDirectory($spath);
      Storage::disk($sdisk)->put("$spath/ldraw/models/Note{$release->short}CA.txt", $release->notes());

      $partslist = [];
      foreach ($parts as $part) {
        // Update parts list for new parts
        if (is_null($part->official_part_id) && $part->type->folder == 'parts/') {
          $partslist[] = [$part->description, $part->filename];
          $f = substr($part->filename, 0, -4);
          if ($part->isTexmap()) {
            Storage::disk('images')->put("library/updates/view{$release->short}/" . $part->filename, $part->get());
          }
          elseif (Storage::disk('images')->exists("library/unofficial/$f.png")) {
            Storage::disk('images')->copy("library/unofficial/$f.png", "library/updates/view{$release->short}/$f.png");
          }
          if (Storage::disk('images')->exists("library/unofficial/{$f}_thumb.png"))
            Storage::disk('images')->copy("library/unofficial/{$f}_thumb.png", "library/updates/view{$release->short}/{$f}_thumb.png");
        }
        $part->releasePart($release, $user);
      }
      usort($partslist, function(array $a, array $b) { return $a[0] <=> $b[0]; });
      $release->part_list = $partslist;
      $release->save();
      $release->makeZip();  
    }

    protected static function getReleaseData(array $ids): array {
      $data = [];
      $data['total_files'] = Part::whereIn('id', $ids)->count();
      $data['new_files'] = Part::whereIn('id', $ids)->whereNull('official_part_id')->count();
      $data['new_types'] = [];
      foreach (PartType::all() as $type) {
        if ($type->type == "Part") {
          $count = Part::whereIn('id', $ids)->whereNull('official_part_id')->where(function (Builder $query) use ($type) {
            $query->orWhere('part_type_id', $type->id)->orWhere('part_type_id', PartType::firstWhere('type', 'Shortcut')->id);
          })->count();
        }
        elseif ($type->type == "Shortcut") {
          continue;
        }
        else {
          $count = Part::whereIn('id', $ids)->whereNull('official_part_id')->where('part_type_id', $type->id)->count();
        } 
        if ($count > 0) $data['new_types'][] = ['name' => $type->name, 'count' => $count];
      }
      $data['moved_parts'] = [];
      foreach (Part::whereIn('id', $ids)->whereRelation('category', 'category', 'Moved')->get() as $part) {
        $data['moved_parts'][] = ['name' => $part->name(),  'movedto' => $part->description]; 
      }
      $data['fixes'] = [];
      $data['rename'] = [];
      foreach (Part::whereIn('id', $ids)->whereNotNull('official_part_id')->whereRelation('category', 'category', '<>', 'Moved')->get() as $part) {
        $op = Part::find($part->official_part_id);
        if ($part->description != $op->description) {
          $data['rename'][] = ['name' => $part->name(), 'decription' => $part->description, 'old_description' => $op->description];
        }
        else {
          $data['fixed'][] = ['name' => $part->name(), 'decription' => $part->description];
        }
      }
      $data['minor_edits']['license'] = Part::official()->whereJsonLength('minor_edit_data->license', '>', 0)->count();
      $data['minor_edits']['name'] = Part::official()->where(function($q) {
        $q->orWhereJsonLength('minor_edit_data->name', '>', 0)->orWhereJsonLength('minor_edit_data->realname', '>', 0);
      })->count();
      $data['minor_edits']['keywords'] = Part::official()->whereJsonLength('minor_edit_data->keywords', '>', 0)->count();
      foreach(Part::official()->whereJsonLength('minor_edit_data->description', '>', 0)->get() as $p) {
        $data['rename'][] = ['name' => $p->name(), 'decription' => $part->description, 'old_description' => $p->minor_edit_data['description']];
      }
      return $data;
    }

    protected function notes(): string {
      $data = $this->part_data;
      $notes = "ldraw.org Parts Update " . $this->name . "\n" . 
        str_repeat('-', 76) . "\n\n" .
        "Redistributable Parts Library - Core Library\n" . 
        str_repeat('-', 76) . "\n\n" .
        "Notes created " . date_format($this->created_at, "r"). " by the Parts Tracker\n\n" .
        "Release statistics:\n" . 
        "   Total files: " . $data['total_files'] . "\n" . 
        "   New files: " . $data['new_files'] . "\n";
      foreach ($data['new_types'] as $t) {
        $notes .= "   New " . $t['name'] . "s: " . $t['count'] . "\n";
      }
      $notes .= "\n" . 
        "Moved Parts\n";
      foreach ($data['moved_parts'] as $m) {
        $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['movedto']. "\n"; 
      }
      $notes .= "\n" . 
        "Renamed Parts\n";
      foreach ($data['rename'] as $m) {
        $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['old_description'] . "\n" .
        "   changed to    ". $m['decription'] ."\n";    }  
      $notes .= "\n" . 
        "Other Fixed Parts\n";
      foreach ($data['fixes'] as $m) {
        $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['decription'] . "\n";
      }
      if ($data['minor_edits']['license'] > 0 || $data['minor_edits']['name'] > 0 || $data['minor_edits']['keywords'] > 0) {
        $notes .= "\n" . "Minor Edits\n";
        if ($data['minor_edits']['license'] > 0) {
          $notes .=  '   ' . $data['minor_edits']['license'] . " Part licenses changed\n";
        }
        if ($data['minor_edits']['name'] > 0) {
          $notes .=  '   ' . $data['minor_edits']['license'] . " Part licenses changed\n";
        }
        if ($data['minor_edits']['keywords'] > 0) {
          $notes .=  '   ' . $data['minor_edits']['license'] . " Part licenses changed\n";
        }
      }
      return $notes;      
    }

    public function makeZip(): void {
      $sdisk = config('ldraw.staging_dir.disk');
      $spath = config('ldraw.staging_dir.path');
      if (!Storage::disk($sdisk)->exists($spath))
        Storage::disk($sdisk)->makeDirectory($spath);
      $sfullpath = realpath(config("filesystems.disks.$sdisk.root") . "/$spath");
      $uzipname = "$sfullpath/lcad{$this->short}.zip";
      $zipname = "$sfullpath/complete.zip";
      $uzip = new \ZipArchive;
      $uzip->open($uzipname, \ZipArchive::CREATE);
  
      $zip = new \ZipArchive;
      $zip->open($zipname, \ZipArchive::CREATE);
  
      foreach (Storage::disk('library')->allFiles('official') as $filename) {
        $zipfilename = str_replace('official/', '', $filename);
        $content = Storage::disk('library')->get($filename);
        $zip->addFromString('ldraw/' . $zipfilename, $content);
      }
      $zip->close();
  
      $zip->open($zipname);
      foreach (Storage::disk($sdisk)->allFiles("$spath/ldraw") as $filename) {
        $zipfilename = str_replace("$spath/", '', $filename);
        $content = Storage::disk($sdisk)->get($filename);
        $uzip->addFromString($zipfilename, $content);
        if ($zip->getFromName($zipfilename) !== false)
          $zip->deleteName($zipfilename);
        $zip->addFromString($zipfilename, $content);
      }
      $zip->close();
      $uzip->close();
      //dd($uzipname, $zipname);
      $rid = $this->id;
      // These have to be chunked because php doesn't write the file to disk immediately
      // Trying to hold the entire library in memory will cause an OOM error
      Part::official()->chunk(500, function (Collection $parts) use ($zip, $zipname, $uzip, $uzipname, $rid) {
        $zip->open($zipname);
        $uzip->open($uzipname);
        foreach($parts as $part) {
          $content = $part->get();
          $zip->addFromString('ldraw/' . $part->filename, $content);
          if ($part->part_release_id == $rid || ($part->part_release_id != $rid && !is_null($part->minor_edit_data))) 
            $uzip->addFromString('ldraw/' . $part->filename, $content);
        }
        $zip->close();
        $uzip->close();
      });

      Storage::disk('library')->copy('updates/complete.zip', "updates/complete-{$this->short}.zip");
      Storage::disk('library')->writeStream("updates/lcad{$this->short}.zip", Storage::disk($sdisk)->readStream("$spath/lcad{$this->short}.zip"));
      Storage::disk('library')->writeStream("updates/complete.zip", Storage::disk($sdisk)->readStream("$spath/complete.zip"));
    }
      
}
