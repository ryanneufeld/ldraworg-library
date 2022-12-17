<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Jobs\RenderFile;

use App\Models\User;
use App\Models\PartCategory;
use App\Models\Vote;
use App\Models\VoteType;
use App\Models\PartRelease;
use App\Models\PartEvent;
use App\Models\PartEventType;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\PartLicense;

use App\LDraw\FileUtils;

class Part extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
      'user_id',
      'part_category_id',
      'part_license_id',
      'part_type_id',
      'part_release_id',
      'part_type_qualifier_id',
      'description',
      'filename',
      'header',
    ];

    protected $with = ['release', 'type'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id', 'id');
    }

    public function license()
    {
        return $this->belongsTo(PartLicense::class, 'part_license_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(PartType::class, 'part_type_id', 'id');
    }

    public function type_qualifier()
    {
        return $this->belongsTo(PartType::class, 'part_type_qualifier_id', 'id');
    }

    public function subparts() {
      return $this->belongsToMany(self::class, 'related_parts', 'parent_id', 'subpart_id');
    }
    
    public function parents(){
      return $this->belongsToMany(self::class, 'related_parts', 'subpart_id', 'parent_id');
    }

    public function keywords() {
      return $this->belongsToMany(self::class, 'parts_part_keywords', 'part_id', 'part_keyword_id');
    }
    
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function events()
    {
        return $this->hasMany(PartEvent::class);
    }

    public function release()
    {
        return $this->belongsTo(PartRelease::class, 'part_release_id');
    }
    
    public function history()
    {
      return $this->hasMany(PartHistory::class);
    }

    public function getUnofficialAttribute() {
      return $this->release->short == 'unof';
    }
    
    public function isTexmap() {
      return $this->type->format == 'png';
    }
    
    public function libFolder() {
      return $this->unofficial ? 'unofficial' : 'official';
    }
    public function updateVoteSummary($forceUpdate = false) {
      if (!$this->unofficial) return;
      $data = array_merge(['A' => 0, 'C' =>0, 'H' => 0, 'T' => 0], $this->votes->pluck('vote_type_code')->countBy()->all());
      $data['S'] = $this->uncertified_subpart_count;
      $data['F'] = $this->official_part_id !== null;
      $this->vote_summary = serialize($data);
      $this->save();
      $this->updateVoteSort($forceUpdate);
    }
    
    public function updateVoteSort($forceUpdate = false) {
      if (!$this->unofficial) return;
      $vote = unserialize($this->vote_summary);
      $old_sort = $this->vote_sort;
      // Held
      if ($vote['H'] != 0) {
        $this->vote_sort = 5;
      }
      // Uncertified subparts      
      elseif ($vote['S'] != 0) {
        $this->vote_sort = 4;
      }
      // Certified      
      elseif ((($vote['A'] > 0) && (($vote['C'] + $vote['A']) >= 2)) || ($vote['T'] > 0)) {
        $this->vote_sort = 1;
      }
      // Awaiting Admin      
      elseif (($vote['C'] + $vote['A']) >= 2) {
        $this->vote_sort = 2;
      }
      // Needs votes      
      else {
        $this->vote_sort = 3;
      }  
      $this->save();
      if ($forceUpdate || ($old_sort == 1 && $this->vote_sort != 1) || ($old_sort != 1 && $this->vote_sort == 1)) {
        foreach ($this->parents as $p) {
          $p->updateUncertifiedSubpartCount($forceUpdate);
        }  
      }  
    }
    
    public function updateUncertifiedSubpartCount($forceUpdate = false) {
      if (!$this->unofficial) return;
      $us = 0;
      // Check subparts for certification
      foreach ($this->subparts as $subpart) {
        if ($subpart->unofficial && $subpart->vote_sort != 1) $us++;
      }
      $this->uncertified_subpart_count = $us;
      $this->save();
      // Report own certification status back to caller
      $this->updateVoteSummary($forceUpdate);
    }
    
    public function getHeaderText() {
      if ($this->isTexmap()) {
        $filetext = "0 {$this->description}\n" .
                    "0 Name: " . str_replace('/', '\\', $this->filename) . "\n" .
                    $this->user->toString() . "\n" .
                    trim($this->type->toString() . " " . $this->release->toString()) . "\n" .
                    $this->license->toString() . "\n\n";
        foreach ($this->history as $hist) {
          $filetext .= $hist->toString() . "\n";
        }
        return $filetext;        
      }
      else {
        return FileUtils::getHeader($this->getFileText());
      }
    }
    
    public function refreshHeader() {
      $this->header = $this->getHeaderText();
      $this->save();
    }
    public function nameString() {
      $name = FileUtils::getName($this->header);
      return $name ? $name : '';
    }
    
    public function getFileText() {
      if ($this->isTexmap()) {
        $data = str_split(base64_encode($this->get()), 80);
        $filetext = "0 !DATA " . str_replace(['parts/textures/', 'p/textures/'], '', $this->filename) . "\n";
        $filetext .= $this->header;
        $filetext .= "0 !:" . implode("\n0 !:", $data) . "\n";
        return $this->getHeaderText();
      }
      else {
        return $this->get();
      }
    }
        
    public function get() {
      if ($this->description == 'Missing') {
        return '';
      }  
      elseif (!$this->isTexmap()) {
        return FileUtils::cleanFileText(Storage::disk('library')->get($this->libFolder(). '/' . $this->filename));
      }      
      else {
        return Storage::disk('library')->get($this->libFolder(). '/' . $this->filename);
      }      
    }
    
    public function put($content) {
      if ($this->unofficial) {
        //for unofficial files, save a backup
        Storage::disk('library')->move('unofficial/' . $this->filename, 'backups/' . $this->filename . '.' . time());
      }
      Storage::disk('library')->put($this->libFolder(). '/' . $this->filename, $content);
      
      // IDK whats going on with the Storage Facade and file permissions
      // but it's borked for me and this is the only solution that worked
      // If someone reads this and can explain what I'm doing wrong, submit
      // a report on github
      umask(000);
      chmod(storage_path('app/library/') . $this->libFolder(). '/' . $this->filename, 0664);
    }
    
    public function renumber($newnumber) {
      $dir = dirname($this->filename);
      $noext = substr($this->filename, 0, -4);
      $newbase = $this->libFolder() . '/' . $dir . '/' . $noext;
      Storage::disk('library')->move();
    }
    
    public function updateSubparts($updateUncertified = false) {
      if ($this->isTexmap()) return;
      $file = $this->get();
      $refs = FileUtils::getSubparts($file);
      $sids = [];
      foreach(['subparts','textures'] as $type) {
        foreach($refs[$type] as $subpart) {
          if (empty(trim($subpart))) continue;
          $osubp = self::findByName($subpart, false, true);
          $usubp = self::findByName($subpart, true, true);
          if (isset($usubp) && $this->unofficial && $this->id <> $usubp->id) {
            $sids[] = $usubp->id;
          }  
          elseif (isset($osubp) && $this->id <> $osubp->id) { 
            $sids[] = $osubp->id;
          }
          elseif (!isset($usubp) && $this->unofficial) {
            $upart = self::createMissing($subpart);
            $sids[] = $upart->id;
          }    
        }  
      }  
      $this->subparts()->sync($sids);
      if ($updateUncertified) $this->updateUncertifiedSubpartCount();
    }
   
    public static function findByName($name, $unofficial = false, $withoutFolder = false) {
      $filename = str_replace('\\', '/', $name);
      if ($withoutFolder) {
        if (pathinfo($name, PATHINFO_EXTENSION) == 'png') $filename = "textures/$filename";
        $part = self::where(function($query) use ($filename) {
            $query->where('filename', "p/$filename")
            ->orWhere('filename', "parts/$filename");
        });    
      }
      else {
        $part = self::where('filename', $filename);
      }
      if ($unofficial) {
        return $part->whereRelation('release', 'short', 'unof')->first();       
      }
      else {
        return $part->whereRelation('release', 'short', '<>', 'unof')->first();
      }  
    }
    
    // Note: this function assumes that the part text has been cleaned and validated
    public function fillFromText($text, $force_unofficial = false, $updateFile = false, $header_only = false) {
      $author = FileUtils::getAuthor($text);
      $user = User::findByName($author['user'], $author['realname']);
      
      $name = FileUtils::getName($text);
      
      $pt = FileUtils::getPartType($text);
      $type = PartType::findByType($pt['type']);
      $qual = PartTypeQualifier::findByType($pt['qual']);
      
      if (strpos($type->type, 'Primitive') !== false || strpos($name, '8\\') !== false || strpos($name, '48\\') !== false) {
        $filename = "p/" . str_replace('\\', '/', $name);
      }
      else {
        $filename = "parts/" . str_replace('\\', '/', $name);
      }

      if (!$force_unofficial) {
        $rel = FileUtils::getRelease($text);
        $release = PartRelease::firstWhere('name', $rel['release']) ?? PartRelease::unofficial();
      }
      else {
        $release = PartRelease::unofficial();
      }  
      $license = PartLicense::firstWhere('text', FileUtils::getLicense($text));
      
      if ($type->name == 'Part' || ($type->name == 'Shortcut' && mb_strpos($name, "s\\") === false)) {
        $category = PartCategory::findByName(FileUtils::getCategory($text));
        $cid = $category->id;
      }
      else {
        $cid = null;
      }        
      
      $kw = FileUtils::getKeywords($text);
      
      $history = FileUtils::getHistory($text, true);
      
      $this->fill([
        'user_id' => $user->id,
        'part_category_id' => $cid,
        'part_release_id' => $release->id,
        'part_license_id' => $license->id,
        'filename' => $filename,
        'part_type_id' => $type->id,
        'part_type_qualifier_id' => $qual->id ?? null,
        'description' => FileUtils::getDescription($text),
        'header' => FileUtils::getHeader($text),
      ]);
      $this->save();

      $this->keywords()->sync([]);

      if (!empty($kw)) {
        foreach($kw as $word) {
          $keyword = PartKeyword::findByKeywordOrCreate($word);
          $this->keywords()->attach($keyword);
        }
      }
      
      foreach ($this->history as $hist) {
        $hist->delete();
      }
      
      if (!empty($history)) {
        foreach ($history as $hist) {
          PartHistory::create(['user_id' => $hist['user'], 'part_id' => $this->id, 'created_at' => $hist['date'], 'comment' => $hist['comment']]);
        }
      }
      
      if ($updateFile) {
        $file = $release->short == 'unof' ? 'library/unofficial/' . $filename : 'library/official/' . $filename;
        if ($header_only) {
          $text = FileUtils::setHeader($text, $this->get());
        }
        $this->put($text);
      }  
    }
    
    public static function createFromText($text, $force_unofficial = false, $updateFile = false) {
      $part = new self;
      $part->fillFromText($text, $force_unofficial, $updateFile);
      return $part;
    }

    public static function createTexmap($fill, $image = null) {
      $part = new self;
      $part->fill($fill);
      $part->header = '';
      $part->save();
      $part->refresh();
      $part->refreshHeader();
      if (!is_null($image)) {
        $part->put($image);
        $part->updateImage();
      }
      return $part;
    }

    public static function updateOrCreateFromText($text, $force_unofficial = false, $updateFile = false) {
      $pt = FileUtils::getPartType($text);
      $part = self::findByName(FileUtils::getName($text), !empty($pt['unofficial']), true);
      
      if (!empty($part)) {
        $part->fillFromText($text, $force_unofficial, $updateFile);
        $part->refresh();
      }
      else {
        $part = self::createFromText($text, $force_unofficial, $updateFile);
      }        
      return $part;
    }

    // This function assumes file is a Linetype 1 file reference (no parts or p)
    public static function createMissing($file) {
      $folder = pathinfo($file, PATHINFO_DIRNAME);
      if ($folder == '.') $folder = '';
      if (pathinfo($file, PATHINFO_EXTENSION) == 'png') $folder = "textures/$folder";
      $pt = PartType::where(function($query) use ($folder) {
        $query->where('folder', "parts/$folder")
        ->orWhere('folder', "p/$folder");
      })->first();
      return self::create([
        'user_id' => User::findByName('unknown')->id,
        'part_release_id' => PartRelease::unofficial()->id,
        'part_license_id' => PartLicense::defaultLicense()->id,
        'filename' => $pt->folder . str_replace('\\', '/', basename($file)),
        'description' => 'Missing',
        'part_type_id' => $pt->id,
        'header' => '',
      ]);       
    }
    
    public function updateImage($updateParents = false) {
      if (!$this->isTexmap()) {
        RenderFile::dispatch($this);
      }
      else {
        $tw = config('ldraw.image.thumb.width');
        $th = config('ldraw.image.thumb.height');
        if ($this->unofficial) {
          $filepath = storage_path('app/library/unofficial/') . $this->filename;
          $thumbpngfile = config('ldraw.unofficialimagedir') . '/' . substr($this->filename, 0, -4) . '_thumb.png';        
        }
        else {
          $filepath = storage_path('app/library/official/') . $this->filename;
          $thumbpngfile = config('ldraw.officialimagedir') . '/' . substr($this->filename, 0, -4) . '_thumb.png';        
        }
        list($width, $height) = getimagesize($filepath);
        $r = $width / $height;
        if ($tw/$th > $r) {
            $newwidth = $th*$r;
        } else {
            $newwidth = $tw;
        }
        $png = imagecreatefrompng($filepath);
        imagealphablending($png, false);
        $png = imagescale($png, $newwidth);
        imagesavealpha($png, true);
        imagepng($png, $thumbpngfile);
        exec("optipng $filepath");
        exec("optipng $thumbpngfile");
      }        
      if ($updateParents) {
        foreach ($this->parents as $part) {
          $part->updateImage(true);
        }  
      }  
    }
    // Returns a collection of the user who have edited this part
    public function editHistoryUsers() {
      $id = $this->id;
      $users = User::whereNotIn('name', ['OrionP', 'cwdee', 'sbliss', 'PTadmin'])->whereHas('part_histories', function (Builder $query) use ($id) {
        $query->where('part_id', $id);
      })->get();
      return $users;
    }
}
