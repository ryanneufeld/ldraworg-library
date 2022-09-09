<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\Excludable;

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
use App\Models\File;

use App\Helpers\PartsLibrary;

class Part extends Model
{
    use HasFactory, SoftDeletes;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id', 'id');
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
    
    public function files() {
      return $this->hasMany(File::class);
    }
    public function history()
    {
      return $this->hasMany(PartHistory::class);
    }
    
    public function officialPart() {
      return $this->belongsTo(Part::class, 'official_part_id', 'id');
    }

    public function unofficialPart() {
      return $this->hasOne(Part::class, 'official_part_id', 'id');
    }
    
    public function save(array $options = []) {
      parent::save($options);
      if ($this->type->name == 'Texmap') {
        $this->syncImageData();
      }
    }
    
    private function imageDataFilename() {
      return 'textures/data/' . $this->filename . "." . date_format(date_create($this->latestFile()->created_at), "U") . '.txt';
    }
    
    private function imageDataFile() {
      if (Storage::disk('local')->missing($this->imageDataFilename())) {
        $author = $this->user->authorString() ?? '[PTadmin]';
        $this->unofficial ? $u = "Unofficial_" : $u = "";
        $data = "0 TEXMAP Image {$this->filename}\r\n0 Author: $author\r\n0 !LDRAW_ORG {$u}Texmap";
      }
      else {
        return Storage::disk('local')->get($this->imageDataFilename());
      }  
    }
    
    private function saveImageData($file) {
      if (is_string($file)) {
        Storage::disk('local')->put($this->imageDataFilename(), $file);
      }
    }
    
    private function syncImageData() {
      $author = $this->user->authorString();
      $this->unofficial ? $u = "Unofficial_" : $u = "";
      $data = "0 TEXMAP Image {$this->filename}\r\n0 Author: $author\r\n0 !LDRAW_ORG {$u}Texmap";
      foreach ($this->history as $h) {
        $data .= $h->toString() . "\r\n";
      }
      $this->saveImageData($data);
    }
    
    private function refreshDataFile() {
      if ($this->type->name == 'Texmap') {
        return $this->imageDataFile();
      }
      else {
        return $this->file;
      }
    }
    
    private function latestFile() {
      return $this->hasOne(File::class)->latestOfMany()->first();
    }
    
    public function getFileAttribute() {
      return $this->latestFile()->getPartFile();
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
      if ($this->type->type == 'Texmap') return ""; 
      $file = explode("\r\n", $this->file);
      $i = 0;
      while (empty($file[$i]) || $file[$i][0] == '0' || $file[$i][0] == ' ') $i++;
      return implode("\n", array_slice($file, 0, $i));
    }
   
    public function refreshDescription($file = null) {
      $file = $file ?? $this->refreshDataFile();
      if ($description = PartsLibrary::descriptionFromFilestring($file)) $this->description = $description;
    }

    public function refreshAuthor($file = null) {
      $file = $file ?? $this->refreshDataFile();
      if ($author = PartsLibrary::authorFromFilestring($file)) {
        $user = User::firstWhere('name',$author['name']) ?? $user = User::firstWhere('realname',$author['realname']);
        if (isset($user)) $this->user()->associate($user);
      }      
    }

    public function refreshType($file = null) {
      $file = $file ?? $this->refreshDataFile();
      if ($type = PartsLibrary::typeFromFilestring($file)) {
        $t = PartType::firstWhere('type', $type['type']);
        $qual = PartTypeQualifier::firstWhere('type', $type['qual']);
        if (isset($opqual)) $this->type_qualifier()->associate($qual);
        if (isset($t)) $this->type()->associate($t);
      }
    }

    public function refreshRelease($file = null) {
      $file = $file ?? $this->refreshDataFile();
      if ($this->unofficial) {
         $this->release()->associate(PartRelease::firstWhere('short','unof'));
      }  
      elseif ($release = PartsLibrary::releaseFromFilestring($file)) {
        if ($release['releasetype'] == 'ORIGINAL') {
          $this->release()->associate(PartRelease::firstWhere('short','original'));
        }
        elseif ($release['releasetype'] == 'UPDATE') {
          $r = PartRelease::firstWhere('name', $release['release']);
          if (isset($r)) $this->release()->associate($r);
        }
      }
    }

    public function refreshCategory($file = null) {
      $file = $file ?? $this->refreshDataFile();
      $category = PartsLibrary::categoryFromFilestring($file);
      if ($this->type->type != 'Part' && $this->type->type != 'Shortcut') {
        if ($category) {
          $cat = PartCategory::firstWhere('category', $category);
          if (isset($cat)) $this->category()->associate($cat);
        }
        else {
          $cat_str = str_replace(['~','|','=','_'], '', mb_strstr($this->description, " ", true));
          $cat = PartCategory::firstWhere('category', $cat_str);
          if (isset($cat)) $this->category()->associate($cat);
        }
      }
    }
    
    public function refreshSubparts($file = null) {
      $file = $file ?? $this->refreshDataFile();
      $refs = PartsLibrary::subpartsFromFilestring($file);
      $this->subparts()->sync([]);
      foreach ($refs['subparts'] as $subpart) {
        $subpart = mb_strtolower(str_replace('\\', '/', $subpart));
        $subp = Part::where(function($query) use ($subpart) {
            $query->where('filename', 'p/' . $subpart)
            ->orWhere('filename', 'parts/' . $subpart);
        })
        ->where('unofficial', $this->unofficial)->first();
        // exclude circular references
        if (isset($subp) && $subp->id != $this->id) {
          $this->subparts()->attach($subp);
          unset($subp);
        }  
      }  
      foreach ($refs['textures'] as $texture) {
        $texture = mb_strtolower(str_replace('\\', '/', $texture));
        $subp = Part::where(function($query) use ($texture) {
            $query->where('filename', 'parts/textures/' . $texture)
            ->orWhere('filename', 'p/textures/' . $texture);
        })
        ->where('unofficial', $this->unofficial)->first();
        if (isset($subp)) {
          $this->subparts()->attach($subp);
          unset($subp);
        }  
      }  
      $this->updateUncertifiedSubpartsCache();
    }
    
    public function refreshHistory($file = null) {
      $file = $file ?? $this->refreshDataFile();
      foreach ($this->history as $history) {
        $history->delete();
      }
      if ($historyitems = PartsLibrary::historyFromFilestring($file)) {
        if (count($historyitems) < mb_substr_count($file, '!HISTORY')) {
          Log::debug("HISTORY count mismatch: {$this->filename}, expected: " . mb_substr_count($this->file, '!HISTORY') . ", actual: " . count($historyitems), ['hostoryitems' => $historyitems]);
        }  
        $aliases = PartsLibrary::$known_author_aliases;
        foreach($historyitems as $history) {
          if (array_key_exists($history['user'], $aliases)) $history['user'] = $aliases[$history['user']];
          $user = User::firstWhere('name',$history['user']) ?? User::firstWhere('realname',$history['user']);
          if (!isset($user)) continue;
          $h = new PartHistory;
          $h->comment = $history['comment'];
          $h->created_at = $history['date'];
          $h->part()->associate($this);
          $h->user()->associate($user);
          $h->save();
        }
      }
    }
    
    public function refreshAll($file = null) {
      $file = $file ?? $this->refreshDataFile();
      $this->refreshDescription($file);
      $this->refreshAuthor($file);
      $this->refreshType($file);
      $this->refreshRelease($file);
      $this->refreshCategory($file);
      $this->refreshSubparts($file);
      $this->refreshHistory($file);
    }
    
    public static function findByName($name, $officialOnly = false, $withoutFolder = false) {
      if ($withoutFolder) {
        $part = self::where(function($query) use ($name) {
            $query->where('filename', 'p/' . $name)
            ->orWhere('filename', 'parts/' . $name);
        });    
      }
      else {
        $part = self::where('filename', $name);
      }
      if ($officialOnly) {
        $part = $part->where('unofficial', false);
      }
      return $part->first();
    }

    public static function createFromFilestring($filestring, $unofficial = true, $folder = null) {
      $part = new self;
      $part->unofficial = $unofficial;
      $part->refreshDescription($filestring);
      $part->refreshAuthor($filestring);
      $part->refreshType($filestring);
      $part->refreshRelease($filestring);
      $part->refreshCategory($filestring);
      if ($part->type->type == 'Texture' && isset($folder)) {
        $part->filename = $folder . '/textures/' . pathinfo($file->path, PATHINFO_BASENAME);
      }  
      else {
        $name  = PartsLibrary::nameFromFilestring($filestring);
        if (isset($folder)) {
          $part->filename = $folder . '/' . $name;
          $part->syncImageData();
        }
        else {
          if ($part->type->type == 'Primitive' || $part->type->type == '8_Primitive' || $part->type->type == '48_Primitive') {
            $part->filename = 'p/' . $name;
          }
          else {
            $part->filename = 'parts/' . $name;
          }
        }
      }
      Log::debug('Prior to save', ['part' => $part]);
      $part->save();
      $part->refreshSubparts($filestring);
      $part->refreshHistory($filestring);
      return $part;
    }
}
