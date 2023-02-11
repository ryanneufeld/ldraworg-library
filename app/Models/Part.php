<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

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

use App\LDraw\FileUtils;
use RuntimeException;

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
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted() {
      static::addGlobalScope('missing', function (Builder $builder) {
        $builder->where('description', '<>', 'Missing');
      });
/*
      static::saving(function ($part) {
        $part->header = $part->getHeaderText();
      });
*/
    }

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

    public function isTexmap(): bool {
      return $this->type->format == 'png';
    }

    public function isUnofficial(): bool {
      return $this->release->short == 'unof';
    }

    public function hasPatterns(): bool {
      $number = basename($this->filename);
      return self::where('filename', 'like', "{$number}p??%.dat")->count() > 0;
    }

    public function hasComposites(): bool {
      $number = basename($this->filename);
      return self::where('filename', 'like', "{$number}c??%.dat")->count() > 0;
    }
    public function hasStickerShortcuts(): bool {
      $number = basename($this->filename);
      return self::where('filename', 'like', "{$number}d??%.dat")->count() > 0;
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
          $cat = FileUtils::getCategory($this->header);
          if ($cat['meta'] === true) {
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
        return $this->getHeaderText();
      }
      else {
        return $this->get();
      }
    }
        
    public function get(): string {
      if ($this->description == 'Missing') return '';
      return Storage::disk('library')->get($this->libFolder() . '/' . $this->filename);  
    }
    
    public static function findUnofficialByName(string $name, bool $withoutFolder = false): ?self {
      $filename = str_replace('\\', '/', $name);
      if ($withoutFolder) {
        if (pathinfo($name, PATHINFO_EXTENSION) == 'png') $filename = "textures/$filename";
        return self::withoutGlobalScope('missing')->unofficial()->where(function(Builder $query) use ($filename) {
            $query->where('filename', "p/$filename")
            ->orWhere('filename', "parts/$filename");
        })->first();
      }
      else {
        return self::withoutGlobalScope('missing')->unofficial()->where('filename', $filename)->first();
      }
    }

    public static function findOfficialByName(string $name, bool $withoutFolder = false): ?self {
      $filename = str_replace('\\', '/', $name);
      if ($withoutFolder) {
        if (pathinfo($name, PATHINFO_EXTENSION) == 'png') $filename = "textures/$filename";
        return self::withoutGlobalScope('missing')->official()->where(function(Builder $query) use ($filename) {
            $query->where('filename', "p/$filename")
            ->orWhere('filename', "parts/$filename");
        })->first();
      }
      else {
        return self::withoutGlobalScope('missing')->official()->where('filename', $filename)->first();
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

    public function releasable(): bool {
      // Is not certified
      if ($this->vote_sort !== 1) {
        return false;
      } 
      // Is a part or shorcut and certified
      if (($this->type->type == "Part" || $this->type->type == "Shortcut")) {
        return $this->vote_sort === 1;
      }
      // Has at least one releaseable Part or Shortcut in the parent chain
      elseif ($this->parents->count() > 0) {
        foreach($this->parents as $parent) {
          if ($parent->releasable() || $parent->release->short <> 'unof') return true;
        }
      }
      // Otherwise not releaseable
      else {
        return false;
      }
    }
  
    public function makeOfficial(PartRelease $release = null): void {
      if (is_null($release)) $release = PartRelease::current();
      
      if (!is_null($this->official_part_id)) {
        $op = Part::find($this->official_part_id);
        $this->created_at = $op->created_at;
        $this->official_part_id = null;
        $op->delete();
      }
  
      $this->updateLicense();
      $this->release()->associate($release);
      $this->header = $this->getHeaderText();
      $this->vote_sort = 1;
      $this->vote_summary = null;
      $this->uncertified_subpart_count = 0;
      $this->save();
      Storage::disk('library')->move('unofficial/' . $this->filename, 'official/' . $this->filename);
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
      if ($this->isUnofficial()) Storage::disk('library')->move('unofficial/' . $this->filename, 'backups/' . $this->filename . '.' . time());
  
      if (!$this->isTexmap()) $content = FileUtils::unix2dos($content);
      Storage::disk('library')->put($this->libFolder() . '/' . $this->filename, $content);
      
      // IDK whats going on with the Storage Facade and file permissions
      // but it's borked for me and this is the only solution that worked
      // If someone reads this and can explain what I'm doing wrong, submit
      // a report on github
      umask(000);
      chmod(storage_path('app/library/') . $this->libFolder() . '/' . $this->filename, 0664);
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

    public static function createMissing(string $file): self {
      $folder = pathinfo($file, PATHINFO_DIRNAME);
      if ($folder == '.') $folder = '';
      if (pathinfo($file, PATHINFO_EXTENSION) == 'png') $folder = "textures/$folder";
      $pt = PartType::where(function(Builder $query) use ($folder) {
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
        'vote_sort' => '5',
      ]);       
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
          "0 ~Moved To " . $newPart->name() . "\n" .
          "0 Name: " . $oldPart->name() . "\n" .
          Auth::user()->toString() . "\n" .
          $newPart->typeString() . "\n" . 
          $newPart->license->toString() . "\n\n" .
          "0 BFC CERTIFY CCW\n\n" .
          "0 1 16 0 0 0 1 0 0 0 1 0 0 0 1 " . $newPart->name(); 
        ;
        $p->fillFromText($text);
        $p->save();
        $p->updateImage();
        PartEvent::createFromType('submit', Auth::user(), $p, null, null, null, true);
        return $p;
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
      if (is_null($this->part_license_id)) $this->part_license_id = 1;

      $this->save();
      $this->refresh();
      
      if (!$headerOnly) {
        $body = FileUtils::setHeader($text, '');
        if (is_null($this->body)) {
          PartBody::create(['part_id' => $this->id, 'body' => $body]);
        }
        else {
          $this->body->body = $body;
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
      
      if ($headerOnly) $text = FileUtils::setHeader($this->get(), $text);
      $this->put($text);
    }
  
    // Note: this function assumes that the part text has been cleaned and validated
    public function fillFromFile(string $path, User $user = null, PartType $pt = null, PartRelease $rel = null): void {
      if (File::extension($path) == 'png' && File::mimeType($path) == 'image/png') {
        if (is_null($user) || is_null($pt)) throw new \RuntimeException('User and PartType must be supplied for Texmap');
        $fill = [
          'user_id' => $user->id,
          'part_release_id' => $rel->id ?? PartRelease::unofficial()->id,
          'part_license_id' => PartLicense::defaultLicense()->id,
          'filename' => $pt->folder . File::basename($path),
          'description' => $pt->name . ' ' . File::basename($path),
          'part_type_id' => $pt->id,
        ];
        $this->fill($fill);
        $this->refreshHeader();
        $this->put(File::get($path));
        $this->save();
      }
      elseif (File::extension($path) == 'dat' && File::mimeType($path) == 'text/plain') {
        $this->fillFromText(FileUtils::cleanFileText(File::get($path)), false, $rel);
      }
      else {
        throw new \RuntimeException('Supplied file must be either a png image or dat text file');
      }
    }
    
    public static function createFromFile(string $path, User $user = null, PartType $pt = null, PartRelease $rel = null): self {
      $part = new self;
      $part->fillFromFile($path, $user , $pt, $rel);
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
      $file = $this->get();
      $refs = FileUtils::getSubparts($file);
      $sids = [];
      foreach(['subparts','textures'] as $type) {
        foreach($refs[$type] as $subpart) {
          if (empty(trim($subpart))) continue;
          $osubp = self::findOfficialByName($subpart, true);
          $usubp = self::findUnofficialByName($subpart, true);
          if (isset($usubp) && $this->isUnofficial() && $this->id <> $usubp->id) {
            $sids[] = $usubp->id;
          }  
          elseif (isset($osubp) && $this->id <> $osubp->id) { 
            $sids[] = $osubp->id;
          }
          elseif (!isset($usubp) && $this->isUnofficial()) {
            $upart = self::createMissing($subpart);
            $sids[] = $upart->id;
          }    
        }  
      }  
      $this->subparts()->sync($sids);
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
      if (is_null($newName) && is_null($newType)) return;

      $folder = $newType->folder ?? $this->type->folder;
      $fname = $newName ?? basename($this->filename);
      $fname = $folder . $fname;
      $name = str_replace('/', '\\', str_replace(['p/', 'parts/'] , '', $fname));

      if (Storage::disk('library')->exists($this->libFolder() . '/' . $fname)) 
        throw new RuntimeException("Move failed: " . $this->libFolder() . '/' . $fname . " already exists");
      
      if ($this->isUnofficial()) {
        Storage::disk('library')->move('unofficial/' . $this->filename, 'unofficial/' . $fname);
        Storage::disk('images')->move('library/unofficial/' . substr($this->filename, 0, -4) . '.png', 'library/unofficial/' . substr($fname, 0, -4) . '.png');
        Storage::disk('images')->move('library/unofficial/' . substr($this->filename, 0, -4) . '_thumb.png', 'library/unofficial/' . substr($fname, 0, -4) . '_thumb.png');
        $oldname = $this->name();
        if (!is_null($newType)) $this->type()->associate($newType);
        $this->filename = $fname;
        $this->saveHeader();

        foreach($this->parents as $parent) {
          $text = str_replace($oldname, $name, $parent->get());
          $parent->fillFromText($text);
          $parent->updateImage(true);
        }
        PartEvent::createFromType('rename', Auth::user(), $this, "part $oldname was renamed to {$this->filename}");
      }
      else {
        if ($this->isTexmap()) {
          Storage::disk('library')->copy('official/' . $this->filename, 'unofficial/' . $fname);
          $p = self::createFromFile(storage_path('app/library/unofficial/' . $fname), $this->user, $newType ?? $this->type, PartRelease::unofficial());
          foreach ($this->history as $h) {
            $hist = $h->duplicate();
            $hist->part_id = $p->id;
            $hist->save();
          }
          PartEvent::createFromType('submit', Auth::user(), $p, null, null, null, true);
        }
        else {
          $p = new self;
          $oldName = $this->name();
          $text = str_replace($this->name(), $name, $this->get());
          $p->fillFromText($text, false, PartRelease::unofficial());
          if (!is_null($newType)) $p->type()->associate($newType);
          $p->filename = $fname;
          PartHistory::create([
            'part_id' => $p->id,
            'user_id' => Auth::user()->id,
            'comment' => "Moved from $oldName",
          ]);

          $p->saveHeader();
          $p->updateImage();
          PartEvent::createFromType('submit', Auth::user(), $p, null, null, null, true);
          $mt = self::createMovedTo($this, $p);
        }
      }  
    }

    public function refreshFromStorage() {
      if (!$this->isTexmap())
        $this->fillFromText(FileUtils::cleanFileText($this->get()));
    }
}
