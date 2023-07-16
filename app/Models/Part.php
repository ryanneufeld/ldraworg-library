<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;

use App\Models\Traits\HasPartRelease;
use App\Models\Traits\HasLicense;

use App\Jobs\RenderFile;
use App\Jobs\UpdateZip;

use App\LDraw\FileUtils;
use App\LDraw\LibraryOperations;

class Part extends Model
{
    use HasLicense, HasPartRelease;

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
      'official_part_id',
      'unofficial_part_id',
    ];

    protected $with = ['release', 'type'];

    protected $casts = [
      'vote_summary' => AsArrayObject::class,
      'delete_flag' => 'boolean',
      'manual_hold_flag' => 'boolean',
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
      return $this->belongsToMany(PartKeyword::class, 'parts_part_keywords', 'part_id', 'part_keyword_id')->orderBy('keyword');
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

    public function history() {
      return $this->hasMany(PartHistory::class, 'part_id', 'id');
    }

    public function help() {
      return $this->hasMany(PartHelp::class, 'part_id', 'id')->orderBy('order');
    }

    public function body() {
      return $this->hasOne(PartBody::class, 'part_id', 'id');
    }
    
    public function scopeName($query, string $name) {
      $name = str_replace('\\', '/', $name);
      if (pathinfo($name, PATHINFO_EXTENSION) == "png") {
        $name = "textures/$name";
      }

      return $query->where(function ($q) use ($name) {
        $q->orWhere('filename', "p/$name")->orWhere('filename', "parts/$name");
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
        case 'held':
          $query->where('vote_sort', 5);
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
      return is_null($this->part_release_id);
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
      preg_match(config('ldraw.patterns.basepart'), $number, $matches);
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
    
    // Returns a collection of the users who have edited this part
    public function editHistoryUsers() {
      $id = $this->id;
      $users = User::whereNotIn('name', ['OrionP', 'cwdee', 'sbliss', 'PTadmin'])->whereHas('part_histories', function (Builder $query) use ($id) {
        $query->where('part_id', $id);
      })->get();
      return $users;
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
    
    public function updateVoteData(bool $forceUpdate = false): void 
    {
      if (!$this->isUnofficial()) return;
      $data = array_merge(['A' => 0, 'C' =>0, 'H' => 0, 'T' => 0], $this->votes->pluck('vote_type_code')->countBy()->all());
      $data['F'] = is_null($this->official_part_id) ? 'N' : 'F';
      $old_sort = $this->vote_sort;
      if ($data['H'] != 0) {
        $this->vote_sort = 5;
      }
      // Needs votes      
      elseif (($data['C'] + $data['A'] < 2) && $data['T'] == 0) {
        $this->vote_sort = 3;
      }  
      // Awaiting Admin      
      elseif ($data['T'] == 0 && $data['A'] == 0 && $data['C'] >= 2) {
        $this->vote_sort = 2;
      }
      // Certified      
      elseif ((($data['A'] > 0) && (($data['C'] + $data['A']) > 2)) || ($data['T'] > 0)) {
        $this->vote_sort = 1;
      }

      $this->vote_summary = $data; 
      $this->saveQuietly();

      if ($forceUpdate || ($old_sort == 1 && $this->vote_sort != 1) || ($old_sort != 1 && $this->vote_sort == 1)) {
        $this->parents()->unofficial()->each(function (self $p) use ($forceUpdate) {
          $p->updateVoteData($forceUpdate);
        });
      }  
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
          'part_release_id' => null,
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
  
      $kws = [];
      if (!empty($kw)) {
        foreach($kw as $word) {
          $kws[] = PartKeyword::findByKeywordOrCreate(trim($word))->id;
        }
      }  
      $this->keywords()->sync($kws);
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
/*
      if (!is_null($rel)) {
        $release = FileUtils::getRelease($text);
        $rel = PartRelease::firstWhere('name', $release['release']);
      }
*/      
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
        'part_release_id' => $rel->id ?? null,
        'filename' => $filename,
        'part_type_id' => $type->id,
        'part_type_qualifier_id' => $qual->id ?? null,
        'description' => FileUtils::getDescription($text),
        'header' => FileUtils::getHeader($text),
        'cmdline' => $cmdline === false ? NULL : $cmdline,
        'bfc' => $bfc === false ? NULL : $bfc['certwinding'],
        'part_license_id' => $user->license->id,
      ]);

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
        $this->save();  
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
      $this->save();
      $this->refresh();
      $this->refreshHeader();
      $this->updateSubparts(true);
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
          'part_release_id' => $rel->id ?? null,
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
        $this->save();
        $this->updateVoteData();  
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
      $this->subparts()->sync($sids);
      $this->missing_parts = empty(array_filter($missing_parts)) ? null : array_unique($missing_parts);
      $this->save();
      $this->updateVoteData();
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
          $upart->fillFromText($this->get(), false, null);
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
            'part_release_id' => null,
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
      PartEvent::unofficial()->where('part_id', $this->id)->update(['part_release_id' => $release->id]);

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
        \App\Models\ReviewSummaryItem::where('part_id', $this->id)->delete();
        $this->deleteQuietly();
      }
      else {
        $this->release()->associate($release);
        $this->refreshHeader();
      }
    }

    public function diff(self $part2): string {
      $lines = collect(explode("\n", $this->body->body))->filter(function (string $value) {
        return !empty($value) && $value[0] != "0";
      });
      $lines2 = collect(explode("\n", $part2->body->body))->filter(function (string $value) {
        return !empty($value) && $value[0] != "0";
      });
      $pattern = '#^([12345]) (\d+)#';
      $delcolor   = ['1' => '36', '2' => '12', '3' => '36', '4' => '36', '5' => '12'];
      $addcolor   = ['1' =>  '2', '2' => '10', '3' =>  '2', '4' =>  '2', '5' => '10'];
      $matchcolor = ['1' => '15', '2' =>  '8', '3' => '15', '4' => '15', '5' =>  '8'];
      $same = $lines->intersect($lines2)->transform(function (string $item) use ($pattern, $matchcolor) {
        return preg_replace($pattern, '$1 '. $matchcolor[$item[0]], $item);
      });
      $added = $lines2->diff($lines)->transform(function (string $item) use ($pattern, $addcolor) {
        return preg_replace($pattern, '$1 '. $addcolor[$item[0]], $item);
      });
      $removed = $lines->diff($lines2)->transform(function (string $item) use ($pattern, $delcolor) {
        return preg_replace($pattern, '$1 '. $delcolor[$item[0]], $item);
      });
      return implode("\n", array_merge($same->toArray(), $added->toArray(), $removed->toArray()));
    }
}
