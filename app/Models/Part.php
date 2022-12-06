<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

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
    
    public function official_part() {
      return $this->belongsTo(self::class, 'official_part_id', 'id');
    }

    public function unofficial_part() {
      return $this->belongsTo(self::class, 'unofficial_part_id', 'id');
    }

    public function getUnofficialAttribute() {
      return $this->release->short == 'unof';
    }
    
    public function isTexmap() {
      return $this->type->format == 'png';
    }
    
    public function getVoteSummaryAttribute() {
      if (!Cache::has("part_" . $this->id . "_vs")) {
        $this->updateVoteSummaryCache();
      }  
      return unserialize(Cache::get("part_" . $this->id . "_vs"));
    }
    
    public function getVoteSortAttribute() {
      // 0 = Certified
      // 1 = Admin Review
      // 2 = Needs more votes
      // 3 = Uncert subparts
      // 4 = Hold
      $vote_sum = $this->vote_summary;
      if ($this->certified) {
        return 0;
      }
      elseif ($vote_sum['H'] > 0) {
        return 4;
      }
      elseif ($this->uncertified_subparts > 0) {
        return 3;
      }
      elseif ($vote_sum['C'] + $vote_sum['A'] >= 2) {
        return 1;
      }
      else {
        return 2;
      }
    }
    
    public function getUncertifiedSubpartsAttribute() {
      if (!Cache::has("part_" . $this->id . "_us")) {
        $this->updateUncertifiedSubpartsCache();
      }  
      return Cache::get("part_" . $this->id . "_us");
    }
    
    public function getCertifiedAttribute() {
      if (!$this->unofficial) return true;
      if ($this->uncertified_subparts > 0) return false; 
      $vote_sum = $this->vote_summary;
      return $vote_sum['H'] == 0 && 
            ($vote_sum['T'] >= 1 || 
            ($vote_sum['C'] >= 2 && $vote_sum['A'] >= 1)
            );
    }
    
    // This is a time consuming function. Do not call unless needed.
    public function updateUncertifiedSubpartsCache() {
      if (!$this->unofficial) return true;
      Cache::forget("part_" . $this->id . "_us");
      $us = 0;
      // Check subparts for certification
      foreach ($this->subparts as $subpart) {
        if ($subpart->updateUncertifiedSubpartsCache() === false) $us++;
      }
      Cache::forever("part_" . $this->id . "_us", $us);
      $this->updateVoteSummaryCache();

      // Report own certification status back to caller
      if ($us > 0) {
        return false;
      }
      else {
        return $this->certified;
      }
    }

    public function updateVoteSummaryCache() {
      Cache::forget("part_" . $this->id . "_vs");
      Cache::forever("part_" . $this->id . "_vs", serialize(array_merge(VoteType::defaultArray(), $this->votes->pluck('vote_type_code')->countBy()->all())));
      return true;
    }
    
    public function header() {
      return FileUtils::getHeader($this->getFileText());
    }
    
    public function nameString() {
      preg_match('#^(p/|parts/)(?<name>.*)$#u', $this->filename, $matches);
      return $matches['name'] ?? '';
    }
    
    public function getFileText() {
      if ($this->description == 'Missing') {
        return '';
      }  
      elseif (!$this->isTexmap()) {
        $folder = $this->release->short == 'unof' ? 'unofficial/' : 'official/';
        return Storage::disk('local')->get('library/' . $folder . $this->filename);
      }
      else {
        $filetext = "0 {$this->description}\n" .
                    "0 Name: " . $this->nameString() . "\n" .
                    $this->user->toString() . "\n" .
                    trim($this->type->toString() . " " . $this->release->toString()) . "\n" .
                    $this->license->toString() . "\n\n";
        foreach ($this->history as $hist) {
          $filetext .= $hist->toString() . "\n";
        }
        return $filetext;        
      }
    }
        
    public function get() {
      if ($this->description == 'Missing') {
        return '';
      }  
      elseif (!$this->isTexmap()) {
        return $this->getFileText();
      }      
      else {
        $folder = $this->release->short == 'unof' ? 'unofficial/' : 'official/';
        return Storage::disk('local')->get('library/' . $folder . $this->filename);
      }      
    }
    
    public function put($content) {
        $folder = $this->unofficial ? 'unofficial/' : 'official/';
        return Storage::disk('local')->put('library/' . $folder . $this->filename, $content);
    }
    
    public function updateSubparts($updateUncertified = false) {
      if ($this->isTexmap()) return;
      $file = $this->getFileText();
      $refs = FileUtils::getSubparts($file);
      $this->subparts()->sync([]);
      foreach(['subparts','textures'] as $type) {
        foreach($refs[$type] as $subpart) {
          if (empty(trim($subpart))) continue;
          $osubp = self::findByName($subpart, false, true);
          $usubp = self::findByName($subpart, true, true);
          if (isset($usubp) && $this->unofficial && $this->id <> $usubp->id) {
            $this->subparts()->attach($usubp);
          }  
          elseif (isset($osubp) && $this->id <> $osubp->id) { 
            $this->subparts()->attach($osubp);
          }
          elseif (!isset($usubp) && $this->unofficial) {
            $upart = self::createMissing($subpart);
            $this->subparts()->attach($upart);
          }    
        }  
      }  
      if ($updateUncertified) $this->updateUncertifiedSubpartsCache();
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
    public function fillFromText($text, $force_unofficial = false, $updateFile = false) {
      $author = FileUtils::getAuthor($text);
      $user = User::findByName($author['user'], $author['realname']);
      
      $name = FileUtils::getName($text);
      
      $pt = FileUtils::getPartType($text);
      $type = PartType::findByType($pt['type']);
      $qual = PartTypeQualifier::findByType($pt['qual']);

      if ($type->name == 'Shortcut' || $type->name == 'Part' || $type->name == 'Subpart' ||  $type->name == 'Helper') {
        $filename = "parts/" . str_replace('\\', '/', $name);
      }
      else {
        $filename = "p/" . str_replace('\\', '/', $name);
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
        $this->put($text);
      }  
    }
    
    public static function createFromText($text, $force_unofficial = false, $updateFile = false) {
      $part = new self;
      $part->fillFromText($text, $force_unofficial, $updateFile);
      return $part;
    }

    public static function updateOrCreateFromText($text, $force_unofficial = false, $updateFile = false) {
      $pt = FileUtils::getPartType($text);
      $part = self::findByName(FileUtils::getName($text), !empty($pt['unofficial']), true);
      
      if (!empty($part)) {
        $part->fillFromText($text, $force_unofficial, $updateFile);
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
      ]);       
    }
    
    public function updateImage($updateParents = false) {
      if (!$this->isTexmap()) RenderFile::dispatch($this);
      if ($updateParents) {
        foreach ($this->parents as $part) {
          $part->updateImage(true);
        }  
      }  
    }      
}
