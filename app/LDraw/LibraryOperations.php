<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;


use App\Models\Part;
use App\Models\User;

class LibraryOperations {
  
 
  public static function refreshNotifications(): void {
    foreach (User::all() as $user) {
      $parts = Part::whereHas('events', function (Builder $query) use ($user) {
        $query->where('user_id', $user->id);
      })->pluck('id');
      $user->notification_parts()->sync($parts);
    }
  }

  public static function makeMPD(Part $part, bool $unOfficialPriority = false): string {
    $parts = new Collection;
    $part->dependencies($parts, $unOfficialPriority);
    $parts = $parts->diff(new Collection([$part]));
    if ($part->isTexmap()) {
      $model = $part->getFileText();
    }
    else {
      $model = "0 FILE " . $part->name() . "\r\n" . $part->getFileText();
    }  
    foreach ($parts as $p) {
      if ($p->isTexmap()) {
        $model .= "\r\n" . $p->getFileText();
      }
      else {
        $model .= "\r\n0 FILE " . $p->name() . "\r\n" . $p->getFileText();
      }  
    }
    return $model;
  }

  public static function checkOrCreateStandardDirs(string $disk, string $path): void {
    if (!Storage::disk($disk)->exists($path))
      Storage::disk($disk)->makeDirectory($path);
    foreach (config('ldraw.dirs') as $dir) {
      if (!Storage::disk($disk)->exists($path . '/' . $dir))
        Storage::disk($disk)->makeDirectory($path . '/' . $dir);
      if (!Storage::disk($disk)->exists($path . '/' . $dir))
        Storage::disk($disk)->makeDirectory($path . '/' . $dir);
    }
  }

  public static function getAllParentIds($part, &$parents, $unofficialOnly = false) {
    if (empty($parents)) $parents = [];
    foreach($part->parents as $parent) {
      if ($unofficialOnly && !$parent->isUnofficial()) continue;
      if (!in_array($parent->id, $parents))
        $parents[] = $parent->id;
      self::getAllParentIds($parent, $parents, $unofficialOnly);
    }
  }

}
