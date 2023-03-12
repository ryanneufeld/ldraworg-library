<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Storage;

use App\Models\Part;
use App\LDraw\FileUtils;

class WebGL {
  public static function WebGLPart(Part $part, &$parts, $without_folder = false, $unOfficialPriority = false) {
    if (empty($parts)) $parts = [];
    $pn = $part->filename;
    if ($without_folder) {
      if ($part->isTexmap()) {
        $pn = str_replace(["parts/textures/","p/textures/"], '', $pn);
      }
      else {
        $pn = str_replace(["parts/","p/"], '', $pn);
      }
    } 
    if(!array_key_exists($pn, $parts)) {
      if ($part->isTexmap()) {
        $parts[$pn] = "/library/" . $part->libFolder() . $part->filename;
      }
      else {
        $parts[$pn] = 'data:text/plain;base64,' .  base64_encode($part->get());        
      }
    }
    foreach ($part->subparts as $spart) {
      if ($unOfficialPriority && !$spart->isUnofficial() && !is_null($spart->unofficial_part_id)) {
        self::WebGLPart(Part::find($spart->unofficial_part_id), $parts, $without_folder, $unOfficialPriority);
      }
      else {
        self::WebGLPart($spart, $parts, $without_folder, $unOfficialPriority);
      }
    } 
  }

  public static function WebGLModel($model, $without_folder = false) {
    $name = FileUtils::getName($model) ? FileUtils::getName($model) : 'model';
    $mparts[$name] = 'data:text/plain;base64,' .  base64_encode($model);
    if ($sp = FileUtils::getSubparts($model)) {
      foreach(['subparts','textures'] as $type) {
        foreach ($sp[$type] as $part) {
          $op = Part::findByName($part, true, true);
          $up = Part::findByName($part, false, true);
          self::WebGLPart($op ?? $up, $mparts, $without_folder);          
        }
      }
    }
    return $mparts;
  }  
}