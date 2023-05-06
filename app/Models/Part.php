<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\PartCategory;
use App\Models\Vote;
use App\Models\PartRelease;
use App\Models\PartEvent;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\PartLicense;
use App\Models\PartHelp;
use App\Models\PartBody;

use App\Jobs\RenderFile;
use App\Jobs\UpdateZip;

use App\LDraw\FileUtils;
use App\LDraw\LibraryOperations;

class Part extends Model
{
    use HasFactory;

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
      'cmdline',
      'bfc',
    ];

    protected $with = ['release', 'type'];

    protected $casts = [
      'vote_summary' => AsArrayObject::class,
      'delete_flag' => 'boolean',
      'minor_edit_data' => AsArrayObject::class,
      'missing_parts' => AsArrayObject::class,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category() {
        return $this->belongsTo(PartCategory::class, 'part_category_id', 'id');
    }

    public function license() {
        return $this->belongsTo(PartLicense::class, 'part_license_id', 'id');
    }

    public function type() {
        return $this->belongsTo(PartType::class, 'part_type_id', 'id');
    }

    public function type_qualifier() {
        return $this->belongsTo(PartTypeQualifier::class, 'part_type_qualifier_id', 'id');
    }

    public function subparts() {
      return $this->belongsToMany(self::class, 'related_parts', 'parent_id', 'subpart_id');
    }
    
    public function parents() {
      return $this->belongsToMany(self::class, 'related_parts', 'subpart_id', 'parent_id');
    }

    public function keywords() {
      return $this->belongsToMany(PartKeyword::class, 'parts_part_keywords', 'part_id', 'part_keyword_id');
    }

    public function notification_users() {
      return $this->belongsToMany(User::class, 'user_part_notifications', 'part_id', 'user_id');
    }

    public function votes() {
        return $this->hasMany(Vote::class, 'part_id', 'id');
    }

    public function events() {
        return $this->hasMany(PartEvent::class, 'part_id', 'id');
    }

    public function release() {
        return $this->belongsTo(PartRelease::class, 'part_release_id', 'id');
    }
    
    public function history() {
      return $this->hasMany(PartHistory::class, 'part_id', 'id');
    }

    public function help() {
      return $this->hasMany(PartHelp::class, 'part_id', 'id');
    }

    public function body() {
      return $this->hasOne(PartBody::class, 'part_id', 'id');
    }
    
    public function scopeOfficial($query) {
        return $query->whereRelation('release', 'short', '<>', 'unof');
    }

    public function scopeUnofficial($query) {
        return $query->whereRelation('release', 'short', 'unof');
    }

    public function scopeFilenameWithoutFolder($query, string $filename) {
      $filename = str_replace('\\', '/', $filename);
      if (pathinfo($filename, PATHINFO_EXTENSION) == "png") {
        $filename = "textures/$filename";
      }

      return $query->where(function ($q) use ($filename) {
        $q->orWhere('filename', "p/$filename")->orWhere('filename', "parts/$filename");
      });
    }

    public function scopeUserSubmits($query, User $user) {
      return $query->whereHas('events', function (Builder $query) use ($user) {
        $query->whereRelation('part_event_type', 'slug', 'submit')->where('user_id', $user->id);
      });
    }

    public function scopePartStatus($query, string $status) {
      switch ($status) {
        case 'certified':
          $query->where('vote_sort', 1);
          break; 
        case 'adminreview':
          $query->where('vote_sort', 2);
          break; 
        case 'memberreview':
          $query->where('vote_sort', 3);
          break; 
        case 'needsubfile':
          $query->where('vote_sort', 4);
          break; 
        case 'held':
          $query->where('vote_sort', 5);
          break; 
        case '2certvotes':
          $query->where('vote_sort', '<>', 5)->where('vote_sort', '<>', 1)->whereHas('votes', function ($q) {
              $q->where('vote_type_code', 'C');
          }, '>=', 2);
          break; 
        case '1certvote':
          $query->where('vote_sort', '<>', 5)->where('vote_sort', '<>', 1)->whereHas('votes', function ($q) {
              $q->where('vote_type_code', 'C');
          }, '=', 1);
          break;
      } 
    }
    public function scopeSearchPart ($query, string $search, string $scope) {
      if (!empty($search)) {
      //Pull the terms out of the search string
      $pattern = '#([^\s"]+)|"([^"]*)"#u';
      preg_match_all($pattern, $search, $matches, PREG_SET_ORDER);

      foreach($matches as $m) {
        $char = '\\';
        $term = str_replace(
          [$char, '%', '_'],
          [$char.$char, $char.'%', $char.'_'],
          $m[count($m)-1]
        );
        switch ($scope) {
          case 'description':
            $query->where(function($q) use ($term) {
              $q->orWhere('filename', 'LIKE', "%$term%")->orWhere('description', 'LIKE', "%$term%");
            });
            break;
          case 'filename':
          case 'header':
            $query->where($scope, 'LIKE', "%$term%");
            break;
          case 'file':
            $query->where(function($q) use ($term) {
              $q->orWhere('header', 'LIKE', "%$term%")->orWhereRelation('body', 'body', 'LIKE', "%$term%");
            });
            break;
          default:  
            $query->where('header', 'LIKE', "%$term%");
            break;
        }
      }
      }
      else {
       $query->where('filename', '');
      }
      return $query;
    }

    public function scopePatterns($query, $basepart) {
      return $query->where(function($q) use ($basepart) {
        $q->where('filename', 'like', "parts/{$basepart}p__.dat")->orWhere('filename', 'like', "parts/{$basepart}p___.dat");
      });
    }

    public function scopeComposites($query, $basepart) {
      return $query->where(function($q) use ($basepart) {
        $q->where('filename', 'like', "parts/{$basepart}c__.dat")->orWhere('filename', 'like', "parts/{$basepart}c___.dat");
      });
    }

    public function scopeStickerShortcuts($query, $basepart) {
      return $query->where(function($q) use ($basepart) {
        $q->where('filename', 'like', "parts/{$basepart}d__.dat")->orWhere('filename', 'like', "parts/{$basepart}d___.dat");
      });
    }

    public function isTexmap(): bool {
      return $this->type->format == 'png';
    }

    public function isUnofficial(): bool {
      return $this->release->short == 'unof';
    }

    public function hasPatterns(): bool {
      $basepart = $this->basePart();
      return $this->type->folder == 'parts/' && self::where(function($q) use ($basepart) {
        $q->where('filename', 'like', "parts/{$basepart}p__.dat")->orWhere('filename', 'like', "parts/{$basepart}p___.dat");
      })->count() > 0;
    }

    public function hasComposites(): bool {
      $basepart = $this->basePart();
      return $this->type->folder == 'parts/' && self::where(function($q) use ($basepart) {
        $q->where('filename', 'like', "parts/{$basepart}c__.dat")->orWhere('filename', 'like', "parts/{$basepart}c___.dat");
      })->count() > 0;
    }
    public function hasStickerShortcuts(): bool {
      $basepart = $this->basePart();
      return $this->type->folder == 'parts/' && self::where(function($q) use ($basepart) {
        $q->where('filename', 'like', "parts/{$basepart}d__.dat")->orWhere('filename', 'like', "parts/{$basepart}d___.dat");
      })->count() > 0;
    }

    public function basePart(): string {
      $number = basename($this->filename);
      preg_match('#^([uts]?\d+[a-z]?)(p[0-9a-z]{2,3}|c[0-9a-z]{2}|d[0-9a-z]{2}|k[0-9a-z]{2}|-f[0-9a-z])?\.(dat|png)#u', $number, $matches);
      return $matches[1] ?? '';
    }
    public function libFolder(): string {
      return $this->isUnofficial() ? 'unofficial/' : 'official/';
    }

    public function typeString(): string {
      $s = $this->type->toString($this->isUnofficial());
      if (!is_null($this->type_qualifier)) $s .= ' ' . $this->type_qualifier->type;
      if (!$this->isUnofficial()) $s .=  ' ' . $this->release->toString();
      return $s;
    }
        
    public function name(): string {
      return str_replace('/', '\\', str_replace(["parts/", "p/"], '', $this->filename));
    }

    public function getHeaderText(): string {
      if ($this->isTexmap()) {
        $filetext = "0 {$this->description}\n" .
                    "0 Name: " . $this->name() . "\n" .
                    $this->user->toString() . "\n" .
                    $this->typeString() . "\n" .
                    $this->license->toString() . "\n\n";
        foreach ($this->history as $hist) {
          $filetext .= $hist->toString() . "\n";
        }
        return $filetext;        
      }
      else {
        $header = 
          "0 {$this->description}\n" .
          "0 Name: ". $this->name() . "\n" .
          $this->user->toString() . "\n" .
          $this->typeString() . "\n" . 
          $this->license->toString() . "\n";
        
        if ($this->help->count() > 0) {
          foreach($this->help()->orderBy('order')->get() as $h) {
            $header .= "0 !HELP {$h->text}\n";
          }
        }

        if (!is_null($this->bfc)) {
            $header .= '0 BFC CERTIFY ' . $this->bfc . "\n\n";
        }
        if (!is_null($this->category)) {
          $cat = str_replace(['~','|','=','_'], '', mb_strstr($this->description, " ", true));
          if ($cat != $this->category->category) {
            $header .= "0 !CATEGORY {$this->category->category}\n";
          }
        }

        if ($this->keywords->count() > 0) {
          $header .= "0 !KEYWORDS " . implode(',', $this->keywords()->orderBy('keyword')->pluck('keyword')->all()) . "\n";
        }
        if (!is_null($this->cmdline)) {
          $header .= "0 !CMDLINE $this->cmdline\n";
        }
        if ($this->history->count() > 0) {
          foreach($this->history as $h) {
            $header .= $h->toString() . "\n";
          }
        }
        return FileUtils::cleanHeader($header);
      }
    }

    public function getFileText(): string {
      if ($this->isTexmap()) {
        $data = str_split(base64_encode($this->get()), 80);
        $filetext = "0 !DATA " . str_replace(['parts/textures/', 'p/textures/'], '', $this->filename) . "\n";
        $filetext .= $this->header;
        $filetext .= "0 !:" . implode("\n0 !:", $data) . "\n";
        return FileUtils::unix2dos($this->getHeaderText());
      }
      else {
        return $this->get();
      }
    }
        
    public function get(): string {
      if ($this->isTexmap()) {
        return base64_decode($this->body->body);
      }
      else {
        return FileUtils::unix2dos(rtrim($this->header) . "\r\n\r\n" . $this->body->body);
      }
    }
    
    public static function findUnofficialByName(string $name, bool $withoutFolder = false): ?self {
      $q = self::unofficial();
      if ($withoutFolder) {
        $q = $q->filenameWithoutFolder($name);
      }
      else {
        $q = $q->where('filename', str_replace('\\', '/', $name));
      }
      return $q->first();
    }

    public static function findOfficialByName(string $name, bool $withoutFolder = false): ?self {
      $q = self::official();
      if ($withoutFolder) {
        $q = $q->filenameWithoutFolder($name);
      }
      else {
        $q = $q->where('filename', str_replace('\\', '/', $name));
      }
      return $q->first();
    }

    // Returns a collection of the users who have edited this part
    public function editHistoryUsers() {
      $id = $this->id;
      $users = User::whereNotIn('name', ['OrionP', 'cwdee', 'sbliss', 'PTadmin'])->whereHas('part_histories', function (Builder $query) use ($id) {
        $query->where('part_id', $id);
      })->get();
      return $users;
    }

    public function releasable(): bool {
      // Already official
      if (!$this->isUnofficial()) return true;
      
      
      if ($this->vote_sort == 1) {
        if ($this->vote_summary['S'] != 0) {
          return false;
        }
        // Part, Shortcut, or part fix
        elseif ($this->type->type == "Part" || $this->type->type == "Shortcut" || !is_null($this->official_part_id)) {
          return true;
        }
        // Has a releasable part in the part chain
        elseif ($this->parents->count() > 0) {
          foreach($this->parents as $parent) {
            if ($parent->releasable()) return true;
          }
        }
      }
      return false;
    }
  
    public function refreshHeader(): void {
      $this->header = $this->getHeaderText();
      $this->save();
    }
  
    public function saveHeader(): void {
      $this->refreshHeader();
      if (!$this->isTexmap()) $this->fillFromText($this->header, true);
    }
    
    public function put(string $content): void {
      // Nothing yet...
    }
    
    public function updateVoteData(): void {
      if (!$this->isUnofficial()) return;
      $data = array_merge(['A' => 0, 'C' =>0, 'H' => 0, 'T' => 0], $this->votes->pluck('vote_type_code')->countBy()->all());
      $data['F'] = $this->official_part_id !== null;
      $data['S'] = 0;

      // Check subparts for certification
      foreach ($this->subparts as $subpart) {
        if ($subpart->vote_sort != 1) $data['S']++;
      }
      
      $old_sort = $this->vote_sort;
      
      // Held
      if ($data['H'] != 0) {
        $this->vote_sort = 5;
      }
      // Needs votes      
      elseif ($data['C'] + $data['A'] < 2) {
        $this->vote_sort = 3;
      }  
      // Awaiting Admin      
      elseif ($data['A'] == 0 && $data['C'] >= 2) {
        $this->vote_sort = 2;
      }
      // Certified      
      elseif ((($data['A'] > 0) && (($data['C'] + $data['A']) > 2)) || ($data['T'] > 0)) {
        $this->vote_sort = 1;
      }
      
      $this->vote_summary = $data;

      $this->saveQuietly();
      
      if ($old_sort != $this->vote_sort) {
        foreach ($this->parents()->unofficial()->get() as $p) {
          $p->updateVoteData();
        }  
        foreach ($this->subfiles()->unofficial()->get() as $p) {
          $p->updateVoteData();
        }  
      }
    }

    public function updateVoteSummary(bool $forceUpdate = false): void {
      if (!$this->isUnofficial()) return;
      $data = array_merge(['A' => 0, 'C' =>0, 'H' => 0, 'T' => 0], $this->votes->pluck('vote_type_code')->countBy()->all());
      $data['S'] = $this->uncertified_subpart_count;
      $data['F'] = $this->official_part_id !== null;
      $this->vote_summary = $data; 
      $this->saveQuietly();
      $this->updateVoteSort($forceUpdate);
    }
    
    public function updateVoteSort(bool $forceUpdate = false): void {
      if (!$this->isUnofficial()) return;
      $vote = $this->vote_summary;
      $old_sort = $this->vote_sort;
      // Held
      if ($vote['H'] != 0) {
        $this->vote_sort = 5;
      }
      // Uncertified subparts      
      elseif ($vote['S'] != 0) {
        $this->vote_sort = 4;
      }
      // Needs votes      
      elseif (($vote['C'] + $vote['A'] < 2) && $vote['T'] == 0) {
        $this->vote_sort = 3;
      }  
      // Awaiting Admin      
      elseif ($vote['T'] == 0 && $vote['A'] == 0 && $vote['C'] >= 2) {
        $this->vote_sort = 2;
      }
      // Certified      
      elseif ((($vote['A'] > 0) && (($vote['C'] + $vote['A']) > 2)) || ($vote['T'] > 0)) {
        $this->vote_sort = 1;
      }
      $this->saveQuietly();
      if ($forceUpdate || ($old_sort == 1 && $this->vote_sort != 1) || ($old_sort != 1 && $this->vote_sort == 1)) {
        foreach ($this->parents()->unofficial()->get() as $p) {
          $p->updateUncertifiedSubpartCount($forceUpdate);
        }  
      }  
    }
    
    public function updateUncertifiedSubpartCount(bool $forceUpdate = false): void {
      if (!$this->isUnofficial()) return;
      $us = 0;
      // Check subparts for certification
      foreach ($this->subparts as $subpart) {
        if ($subpart->vote_sort != 1) $us++;
      }
      $this->uncertified_subpart_count = $us;
      $this->saveQuietly();
      // Report own certification status back to caller
      $this->updateVoteSummary($forceUpdate);
    }

    public static function createMovedTo(Part $oldPart, Part $newPart): ?self {
      if ($oldPart->isUnofficial() || !$newPart->isUnofficial() || 
          !is_null($oldPart->unofficial_part_id) || 
          ($oldPart->type->type != 'Part' && $oldPart->type->type != 'Shortcut')) {
        return null;
      }
      else {
        $p = new self;
        $text =
          "0 ~Moved To " . str_replace(['.dat', '.png'], '', $newPart->name()) . "\n" .
          "0 Name: " . $oldPart->name() . "\n" .
          Auth::user()->toString() . "\n" .
          $newPart->typeString() . "\n" . 
          Auth::user()->license->toString() . "\n\n" .
          "0 BFC CERTIFY CCW\n\n" .
          "1 16 0 0 0 1 0 0 0 1 0 0 0 1 " . $newPart->name(); 
        
        $p->fillFromText($text);
        $p->official_part_id = $oldPart->id;
        $p->save();
        $p->updateImage();
        PartEvent::create([
          'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'submit')->id,
          'user_id' => Auth::user()->id,
          'comment' => "part {$oldPart->name()} moved to {$newPart->name()}",
          'part_release_id' => \App\Models\PartRelease::unofficial()->id,
          'part_id' => $p->id,
        ]);
      UpdateZip::dispatch($p);
        return $p;
      }
    }
  
    public function setHelp(string $text, bool $withoutMeta = false): void {
      $text = FileUtils::dos2unix($text);

      $help = $withoutMeta === true ? explode("\n", $text) : FileUtils::getHelp($text);

      $this->help()->delete();
      
      if (!empty($help)) {
        $order = 0;
        foreach ($help as $h) {
          if (!empty($h)) {
            PartHelp::create(['part_id' => $this->id, 'order' => $order, 'text' => $h]);
            $order++;  
          }
        }
      }
    }

    public function setKeywords(string $text, bool $withoutMeta = false): void {
      $text = FileUtils::dos2unix($text);
      $kw = $withoutMeta === true ? explode(",", str_replace("\n", ",", $text)) : FileUtils::getKeywords($text);

      $this->keywords()->sync([]);
  
      if (!empty($kw)) {
        foreach($kw as $word) {
          $keyword = PartKeyword::findByKeywordOrCreate($word);
          $this->keywords()->attach($keyword);
        }
      }  
    }

    public function setHistory(string $text): void {
      $history = FileUtils::getHistory(FileUtils::dos2unix($text), true);

      $this->history()->delete();
      
      if (!empty($history)) {
        foreach ($history as $hist) {
          PartHistory::create(['user_id' => $hist['user'], 'part_id' => $this->id, 'created_at' => $hist['date'], 'comment' => $hist['comment']]);
        }
      }      
    }

    public function fillFromText(string $text, bool $headerOnly = false, PartRelease $rel = null): void {
      
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

      if (is_null($rel)) {
        $release = FileUtils::getRelease($text);
        $rel = PartRelease::firstWhere('name', $release['release']) ?? PartRelease::unofficial();
      }
      
      if ($type->name == 'Part' || ($type->name == 'Shortcut' && mb_strpos($name, "s\\") === false)) {
        $category = PartCategory::findByName(FileUtils::getCategory($text));
        $cid = $category->id;
      }
      else {
        $cid = null;
      }        
      
      $kw = FileUtils::getKeywords($text);
      
      $history = FileUtils::getHistory($text, true);
      $help = FileUtils::getHelp($text);
      $cmdline = FileUtils::getCmdLine($text);
      $bfc = FileUtils::getBFC($text);

      $this->fill([
        'user_id' => $user->id,
        'part_category_id' => $cid,
        'part_release_id' => $rel->id,
        'filename' => $filename,
        'part_type_id' => $type->id,
        'part_type_qualifier_id' => $qual->id ?? null,
        'description' => FileUtils::getDescription($text),
        'header' => FileUtils::getHeader($text),
        'cmdline' => $cmdline === false ? NULL : $cmdline,
        'bfc' => $bfc === false ? NULL : $bfc['certwinding'],
      ]);
      if (is_null($this->part_license_id)) {
        $this->part_license_id = PartLicense::default()->id;
      }

      $this->save();
      $this->refresh();
      
      if (!$headerOnly) {
        $body = FileUtils::setHeader($text, '');
        if (is_null($this->body)) {
          PartBody::create(['part_id' => $this->id, 'body' => $body]);
        }
        else {
          $this->body->body = $body;
          $this->body->save();
        }  
      } 
        
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

      foreach ($this->help as $h) {
        $h->delete();
      }
      
      if (!empty($help)) {
        $order = 0;
        foreach ($help as $h) {
          PartHelp::create(['part_id' => $this->id, 'order' => $order, 'text' => $h]);
          $order++;
        }
      }

      $this->updateLicense();
      $this->refreshHeader();
      $this->updateSubparts(true);
      $this->save();
    }
  
    // Note: this function assumes that the part text has been cleaned and validated
    public function fillFromFile($file, User $user = null, PartType $pt = null, PartRelease $rel = null): void {
      $filename = basename(strtolower($file->getClientOriginalName()));
      $contents = $file->get();
      $mimeType = $file->getMimeType();
      if (pathinfo($filename, PATHINFO_EXTENSION) == 'png' && $mimeType == 'image/png') {
        if (is_null($user) || is_null($pt)) throw new \RuntimeException('User and PartType must be supplied for Texmap');
        $this->fill([
          'user_id' => $user->id,
          'part_release_id' => $rel->id ?? PartRelease::unofficial()->id,
          'part_license_id' => $user->license->id,
          'filename' => $pt->folder . basename($filename),
          'description' => $pt->name . ' ' . basename($filename),
          'part_type_id' => $pt->id,
        ]);
        $this->refreshHeader();
        if (is_null($this->body)) {
          PartBody::create(['part_id' => $this->id, 'body' => base64_encode($contents)]);
        }
        else {
          $this->body->body = base64_encode($contents);
          $this->body->save();
        }  
        //$this->put(File::get($path));
        $this->save();
      }
      elseif (pathinfo($filename, PATHINFO_EXTENSION) == 'dat' && $mimeType == 'text/plain') {
        $this->fillFromText(FileUtils::cleanFileText($contents), false, $rel);
      }
      else {
        throw new \RuntimeException('Supplied file must be either a png image or dat text file');
      }
    }
    
    public static function createFromFile($file, User $user = null, PartType $pt = null, PartRelease $rel = null): self {
      $part = new self;
      $part->fillFromFile($file, $user, $pt, $rel);
      return $part;
    }
    
    public function updateLicense(): void {
      $users = $this->editHistoryUsers()->add($this->user);
      $lid = PartLicense::findByName('CC_BY_4')->id;
      foreach($users as $user) {
        if ($user->license->id <> $lid) {
          $lid = $user->license->id;
          break;
        }
      }
      $this->part_license_id = $lid;
      $this->save();
      $this->refresh();
    }

    public function updateSubparts($updateUncertified = false): void {
      if ($this->isTexmap()) return;

      $file = $this->body->body;

      $tex_pattern = '#^\s*0\s+!TEXMAP\s+(START|NEXT)\s+(PLANAR|CYLINDRICAL|SPHERICAL)\s+([-\.\d]+\s+){9,11}(?P<texture1>.*?\.png)(\s+GLOSSMAP\s+(?P<texture2>.*?\.png))?\s*$#um';
      $part_pattern = '#^\s*(0\s+!\:\s+)?1\s+((0x)?\d+\s+){1}([-\.\d]+\s+){12}(?P<subpart>.*?\.dat)\s*$#um';
      preg_match_all($part_pattern, $file, $subs);
      preg_match_all($tex_pattern, $file, $tex);
      $subs = array_merge($subs['subpart'] ?? [], $tex['texture1'] ?? [], $tex['texture2'] ?? []);
      $sids = [];
      $missing_parts = [];
      if (!empty($subs)) {
        $parts = self::where(function ($q) use (&$subs) {
          foreach ($subs as &$sub) {
            $sub = str_replace('\\', '/', trim(mb_strtolower($sub)));
            if (pathinfo($sub, PATHINFO_EXTENSION) == "png") {
              $sub = "textures/$sub";
            }
            $q->orWhere('filename', "parts/$sub")->orWhere('filename', "p/$sub");
          }
        })->get();
        $sids = [];
        foreach($parts as $part) {
          if (($this->isUnofficial() && $part->id != $this->id) ||
            (!$this->isUnofficial() && !$part->isUnofficial() && $part->id != $this->id)) {
            $sids[] = $part->id;
          }
        }
        $missing_parts = [];
        foreach ($subs as $sub) {
          if (!empty(rtrim($sub)) && $parts->whereIn('filename', ["parts/$sub", "p/$sub"])->count() == 0) {
            $missing_parts[] = $sub;
          }
        }  
      }
      //dd($missing_parts);
      $this->subparts()->sync($sids);
      $this->missing_parts = empty(array_filter($missing_parts)) ? null : array_unique($missing_parts);
      $this->save();
      if ($updateUncertified) $this->updateUncertifiedSubpartCount();
    }

    public function updateImage($updateParents = false): void {
      RenderFile::dispatch($this);
      if ($updateParents) {
        foreach ($this->parents as $part) {
          $part->updateImage(true);
        }  
      }  
    }

    public function move(string $newName = null, PartType $newType = null): void {
      $oldname = $this->filename;
      $oldnamestr = $this->name();
      if (is_null($newName)) $newName = $this->filename;
      if ($this->isUnofficial()) {
        if (!is_null($newType)) $this->type()->associate($newType);
        $this->filename = $this->type->folder . $newName;
        $this->refreshHeader();
        $this->save();
        $this->updateImage();
        foreach ($this->parents()->unofficial()->get() as $p) {
          $p->body->body = str_replace($oldnamestr, $this->name(), $p->body->body);
          $p->body->save();
          $p->updateSubparts(true);
        }
        Part::unofficial()->whereJsonContains('missing_parts', str_replace(['p/textures/', 'parts/textures/', 'p/', 'parts/'], '', $this->filename))->each(function($p) {
          $p->updateSubparts(true);
          $p->updateImage(true);
        });
        UpdateZip::dispatch($this, $oldname);
      }
      else {
        if (!is_null($this->unofficial_part_id)) return;
        if (!is_null($newType)) {
          $newName = $newType->folder . $newName;
        }
        else {
          $newName = $this->type->folder . $newName;
        } 
        $upart = self::findUnofficialByName($newName);
        if (is_null($upart)) {
          $upart = new self;
          $upart->fillFromText($this->get(), false, PartRelease::unofficial());
          if (!is_null($newType)) $upart->type()->associate($newType);
          $upart->filename = $newName;
          PartHistory::create([
            'user_id' => Auth::user()->id,
            'part_id' => $upart->id,
            'comment' => 'Moved from ' . $oldnamestr,
          ]);
          $upart->save();
          $upart->refresh();
          $upart->refreshHeader();
          $upart->updateImage();
          PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'submit')->id,
            'user_id' => Auth::user()->id,
            'comment' => "part $oldnamestr was moved to {$upart->name()}",
            'part_release_id' => \App\Models\PartRelease::unofficial()->id,
            'part_id' => $upart->id,
          ]);
          UpdateZip::dispatch($upart);
        }
        if ($this->type->folder == 'parts/') {
          $mpart = self::createMovedTo($this, $upart);
          $this->unofficial_part_id = $mpart->id;
          $this->save();
        }     
      }
    }

    public function deleteRelationships(): void {
      $this->history()->delete();
      $this->votes()->delete();
      $this->events()->delete();
      $this->help()->delete();
      $this->body->delete();
      $this->keywords()->sync([]);
      $this->subparts()->sync([]);
      $this->notification_users()->sync([]);
      if (!is_null($this->unofficial_part_id)) {
        $p = self::find($this->unofficial_part_id);
        $p->official_part_id = null;
        $p->save();
      }
      if (!is_null($this->official_part_id)) {
        $p = self::find($this->official_part_id);
        $p->unofficial_part_id = null;
        $p->save();
      }
    }

    public function putDeletedBackup(): void {
      Storage::disk('local')->put('deleted/library/' . $this->filename . '.' . time(), $this->get());
    }

    public function dependencies(Collection $parts, bool $unOfficialPriority = false): void {
      if(!$parts->contains($this)) {
        $parts->add($this);
      }
      foreach ($this->subparts as $spart) {
        if ($unOfficialPriority && !$spart->isUnofficial() && !is_null($spart->unofficial_part_id)) {
          Part::find($spart->unofficial_part_id)->dependencies($parts, $unOfficialPriority);
        }
        else {
          $spart->dependencies($parts, $unOfficialPriority);
        }
      }
    }

    public function allParents(Collection $parents, $unofficialOnly = false) {
      foreach($this->parents as $parent) {
        if ($unofficialOnly && !$parent->isUnofficial()) continue;
        if (!$parents->contains($parent)) {
          $parents->add($parent);
        }
        $parent->allParents($parents, $unofficialOnly);
      }
    }
      
    public function releasePart(PartRelease $release, User $user): void {
      if (!$this->isUnofficial()) {
        return;
      }
      // Update release for event released parts
      PartEvent::whereRelation('release', 'short', 'unof')->where('part_id', $this->id)->update(['part_release_id' => $release->id]);

      // Post a release event     
      PartEvent::create([
        'part_event_type_id' => PartEventType::firstWhere('slug', 'release')->id,
        'user_id' => $user->id,
        'part_id' => $this->id,
        'part_release_id' => $release->id,
        'comment' =>'Release ' . $release->name
      ]);

      // Add history line
      PartHistory::create(['user_id' => $user->id, 'part_id' => $this->id, 'comment' => 'Official Update ' . $release->name]);
      
      $this->refreshHeader();

      if (!is_null($this->official_part_id)) {
        $opart = Part::find($this->official_part_id);
        $contents = $this->get();
        // Update the official part
        if ($opart->isTexmap()) {
          $opart->body->body = $contents;
          $opart->body->save();
          $opart->history()->delete();
          foreach($this->history()->latest()->get() as $h) {
            PartHistory::create(['created_at' => $h->created_at, 'user_id' => $h->user_id, 'part_id' => $opart->id, 'comment' => $h->comment]);
          }
        } 
        else {
          $opart->fillFromText($contents, false, $release);
        }
        $opart->unofficial_part_id = null;
        $opart->save();

        // Update events with official part id
        PartEvent::where('part_release_id', $release->id)->where('part_id', $this->id)->update(['part_id' => $opart->id]);
        $this->deleteRelationships();
        $this->deleteQuietly();
      }
      else {
        $this->release()->associate($release);
        $this->refreshHeader();
      }
    }

    public function render(): void {
      $renderdisk = config('ldraw.ldview.dir.render.disk');
      $renderpath = config('ldraw.ldview.dir.render.path');
      $renderfullpath = realpath(config("filesystems.disks.$renderdisk.root") . '/' . $renderpath);
      $officialimagedisk = config('ldraw.ldview.dir.image.official.disk');
      $officialimagepath = config('ldraw.ldview.dir.image.official.path');
      $officialimagefullpath = realpath(config("filesystems.disks.$officialimagedisk.root") . '/' . $officialimagepath);
      $unofficialimagedisk = config('ldraw.ldview.dir.image.unofficial.disk');
      $unofficialimagepath = config('ldraw.ldview.dir.image.unofficial.path');
      $unofficialimagefullpath = realpath(config("filesystems.disks.$unofficialimagedisk.root") . '/' . $unofficialimagepath);
  
      // Image saving will fail if these directories do not exist
      LibraryOperations::checkOrCreateStandardDirs($officialimagedisk, $officialimagepath);
      LibraryOperations::checkOrCreateStandardDirs($unofficialimagedisk, $unofficialimagepath);
  
      $file = $renderpath . '/' . basename($this->filename);
      $contents = $this->get();
      // Fix an LDView quirk with non-part folder parts
      if (!$this->isTexmap() && $this->type->folder != 'parts/') {
        $contents = str_replace(['Unofficial_Subpart', 'Unofficial_Primitive', 'Unofficial_8_Primitive', 'Unofficial_48_Primitive'], 'Unofficial_Part', $contents);
      }
      Storage::disk($renderdisk)->put($file, $contents);
      $filepath = Storage::disk($renderdisk)->path($file);
      if ($this->isTexmap()) {
        $tw = config('ldraw.image.thumb.width');
        $th = config('ldraw.image.thumb.height');
        if ($this->isUnofficial()) {
          $thumbpngfile = $unofficialimagefullpath . '/' . substr($this->filename, 0, -4) . '_thumb.png';        
        }
        else {
          $thumbpngfile = $officialimagefullpath . '/' . substr($this->filename, 0, -4) . '_thumb.png';        
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
        $this->body->body = base64_encode(Storage::disk($renderdisk)->get($file));
        $this->body->save();
        Storage::disk($renderdisk)->delete($file);
      }
      else {
        // LDview requires a p and a parts directory even if empty
        LibraryOperations::checkOrCreateStandardDirs($renderdisk, "$renderpath/ldraw");
  
        $parts = new Collection;
        $this->dependencies($parts, $this->isUnofficial());
        $parts = $parts->diff(new Collection([$this]));
        foreach ($parts as $p) {
          Storage::disk($renderdisk)->put($renderpath . '/ldraw/' . $p->filename, $p->get());
        }
  
        if ($this->isUnofficial()) {
          $pngfile = $unofficialimagefullpath . '/' . substr($this->filename, 0, -4) . '.png';
        }
        else {
          $pngfile = $officialimagefullpath . '/' . substr($this->filename, 0, -4) . '.png';
        }
        
        $ldrawdir = $renderfullpath . '/ldraw';
        $ldconfig = realpath(config('filesystems.disks.library.root') . '/official/LDConfig.ldr');
        $ldview = config('ldraw.ldview.path');
  
        $normal_size = "-SaveWidth=" . config('ldraw.image.normal.width') . " -SaveHeight=" . config('ldraw.image.normal.height');
        $thumb_size = "-SaveWidth=" . config('ldraw.image.thumb.width') . " -SaveHeight=" . config('ldraw.image.thumb.height');
        $thumbfile = substr($pngfile, 0, -4) . '_thumb.png';
        
        $cmds = ['[General]'];
        foreach(config('ldraw.ldview.commands') as $command => $value) {
          $cmds[] = "$command=$value";
        }  
        
        if (array_key_exists($this->basePart(), config('ldraw.ldview.alt-camera'))) {
          $ac = config('ldraw.ldview.alt-camera');
          $cmds[] = " -DefaultLatLong=" . $ac[$this->basePart()];
        }
        Storage::disk($renderdisk)->put("$renderpath/ldview.ini", implode("\n", $cmds));
        $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -IniFile=$renderfullpath/ldview.ini $normal_size -SaveSnapshot=$pngfile";
        exec($ldviewcmd);
        exec("optipng $pngfile");
        $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -IniFile=$renderfullpath/ldview.ini $thumb_size -SaveSnapshot=$thumbfile";
        exec($ldviewcmd);
        exec("optipng $thumbfile");
        Storage::disk($renderdisk)->deleteDirectory("$renderpath/ldraw");
        Storage::disk($renderdisk)->delete($file);
        Storage::disk($renderdisk)->delete("$renderpath/ldview.ini");
      }
    }

    public static function updateOrCreateFromFile($file, User $user, PartType $pt, string $comment = null): self {
      $filename = basename(strtolower($file->getClientOriginalName()));
      $contents = $file->get();
      $upart = Part::findUnofficialByName($pt->folder . $filename);
      $opart = Part::findOfficialByName($pt->folder . $filename);
      // Unofficial file exists
      if (isset($upart)) {
        $init_submit = false;
        if ($upart->isTexmap()) {
          // If the submitter is not the author and has not edited the file before, add a history line
          if ($upart->user_id <> $user->id && empty($upart->history()->whereFirst('user_id', $user->id)))
            PartHistory::create(['user_id' => $user->id, 'part_id' => $upart->id, 'comment' => 'edited']);
          if (is_null($upart->body)) {
            PartBody::create(['part_id' => $upart->id, 'body' => base64_encode($contents)]);
          }
          else {
            $upart->body->body = base64_encode($contents);
            $upart->body->save();
          }            
          $upart->put($contents);
        }
        else {
          // Update existing part
          $contents = FileUtils::cleanFileText($contents, true, true);
          $upart->fillFromText($contents, false, PartRelease::unofficial());
        }
        $upart->votes()->delete();
        $upart->refresh();
      }
      // Create a new part
      else {
        $init_submit = true;
        $upart = Part::createFromFile($file, $user, $pt, PartRelease::unofficial());
      }
      
      $upart->updateSubparts(true);
      $upart->updateImage(true);
      $upart->saveHeader();
      Part::unofficial()->whereJsonContains('missing_parts', str_replace(['p/textures/', 'parts/textures/', 'p/', 'parts/'], '', $upart->filename))->each(function($p) {
        $p->updateSubparts(true);
        $p->updateImage(true);
      });
      if (!empty($opart)) {
        $upart->official_part_id = $opart->id;
        $upart->save();
        $opart->unofficial_part_id = $upart->id;
        $opart->save();
        Part::unofficial()->whereHas('subparts', function (Builder $query) use ($opart) {
          return $query->where('id', $opart->id);
        })->each(function (Part $part) {
          $part->updateSubparts(true);
        });
      }
      $user->notification_parts()->syncWithoutDetaching([$upart->id]);
      PartEvent::create([
        'part_event_type_id' => PartEventType::firstWhere('slug', 'submit')->id,
        'user_id' => $user->id,
        'part_id' => $upart->id,
        'part_release_id' => PartRelease::unofficial()->id,
        'comment' => $comment,
        'initial_submit' => $init_submit,
      ]);        

      UpdateZip::dispatch($upart);
      
      return $upart;
    }
}
