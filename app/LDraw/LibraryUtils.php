<?php

namespace App\LDraw;

use App\Models\Part;
use App\LDraw\FileUtils;

class LibraryUtils {
  
  public static function dopartsearch($scope, $input, $json = false, $limit = 7) {
    
    //Pull the terms out of the search string
    $pattern = '#([^\s"]+)|"([^"]*)"#u';
    preg_match_all($pattern, $input, $matches, PREG_SET_ORDER);
    
    // Prep the trems for regex searches
    $regterms = [];
    foreach($matches as $m) {
      $regterms[] = preg_quote($m[count($m)-1]);
    }  
    $search = '(?=.*' . implode(')(?=.*', $regterms) . ')';
    $pattern = "#{$search}#iu";

    if ($scope == 'file') {
      $dirs = 'parts/*.dat parts/s/*.dat p/*.dat p/8/*.dat p/48/*.dat';
      $libdir = storage_path('app/library/');
      $search = str_replace("'", "'\''", $search);
      exec("cd $libdir/official && grep -liP '" . $search . "' $dirs", $of);
      exec("cd $libdir/unofficial && grep -liP '" . $search . "' $dirs", $uf);
      $uparts = Part::whereRelation('release', 'short', 'unof')->where('description', '<>', 'Missing')->whereIn('filename', $uf)->orderBy('filename')->lazy();
      $oparts = Part::whereRelation('release', 'short', '<>', 'unof')->where('description', '<>', 'Missing')->whereIn('filename', $of)->orderBy('filename')->lazy();
    }
    else {
      $ids = ['u' => [], 'o' => []];
      foreach(['u' => '=','o' => '<>'] as $r => $opr) {
        $p = Part::whereRelation('release', 'short', $opr, 'unof')->where('description', '<>', 'Missing');
        $parts = $p->pluck($scope, 'id');
        if ($scope == 'description') {
          $desc = $parts;
          $parts = $p->pluck('filename', 'id');
        }  
        foreach($parts as $id => $pt) {
          switch ($scope) {
            case 'filename':
              $term = basename($pt);
              break;
            case 'description':
              $term = basename($pt) . ' ' . $desc[$id];
              break;
            case 'header':
              $term = $pt;
              break;
          }
          if (preg_match($pattern, $term, $matches)) $ids[$r][] = $id;
        }
      }
      $uparts = Part::whereIn('id', $ids['u'])->orderBy('filename')->lazy();
      $oparts = Part::whereIn('id', $ids['o'])->orderBy('filename')->lazy();
    }
    if ($json == true) {
      $results = ['results' => [
          'oparts' => ['name' => "Official\nParts", 'results' => []],
          'uparts' => ['name' => "Unofficial\nParts", 'results' => []],
        ]
      ];
      foreach($uparts->slice(0, $limit)->all() as $part) {
        $results['results']['uparts']['results'][] = [
          'title' => $part->nameString(),
          'description' => $part->description,
          'url' => route('tracker.show', $part),
        ];
      }  
      foreach($oparts->slice(0, $limit)->all() as $part) {
        $results['results']['oparts']['results'][] = [
          'title' => $part->nameString(),
          'description' => $part->description,
          'url' => route('official.show', $part),
        ];
      }
      return $results;
    }
    else {
      return ['results' => ['oparts' => $oparts, 'uparts' => $uparts]];      
    }
  }

  public static function dosuffixsearch($name, $scope) {
    if (strpos($name, '.dat') === false) $name .= '.dat';
    $basepart = Part::findByName($name, false, true) ?? Part::findByName($name, true, true);
    if (!isset($basepart)) return ['results' => ['basepart' => null, 'parts' => null]];
    if (isset($basepart->unofficial_part_id)) $basepart = Part::find($basepart->unofficial_part_id);
    $c = '0123456789abcdefghijklmnopqrstuvwxyz';
    $codes = MetaData::getPatternCodes();
    $parts = [];
    foreach($codes as $code => $desc) {
      if (is_integer($code) || strlen($code) == 1) {
        $searchcode = $code;
        $char_limit = strpos($c, 'z');
      }
      elseif (strpos($code, '$') !== false) {
       $searchcode = $code[0] . $code[1];
       $char_limit = strpos($c, $code[strpos($code, '$') + 1]);
      }
      $ps = [];
      for($i=0;$i <= $char_limit; $i++) {
        $search = $basepart->type->folder . basename($basepart->filename, '.dat') . $scope . $searchcode . $c[$i] . ".dat";
        $op = Part::findByName($search);            
        $up = Part::findByName($search, true);
        if (empty($op) && empty($up)) {
          $ps[] = null;
        }
        else {
          $ps[] = $up ?? $op;
        }                         
      }
      
      //if ($searchcode == 'd8') dd($ps, $char_limit);
      if (!empty(array_filter($ps, function ($a) { return $a !== null;}))) {
        $parts[$searchcode] = ['description' => "{$searchcode}0 - $searchcode" . $c[$char_limit] . ": $desc", 'parts' => $ps]; 
      }
    }
    return ['results' => ['basepart' => $basepart, 'parts' => $parts]];
  }  
  
  public static function WebGLPart(Part $part, &$parts, $without_folder = false) {
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
        $parts[$pn] = "/library/" . $part->libFolder() . "/" . $part->filename;
      }
      else {
        $parts[$pn] = 'data:text/plain;base64,' .  base64_encode($part->get());        
      }
    } 
    if ($part->unofficial) {
      foreach ($part->subparts()->whereRelation('release', 'short', 'unof')->get() as $spart) {
        self::WebGLPart($spart, $parts, $without_folder);
      }  
      foreach ($part->subparts()->whereRelation('release', 'short', '<>', 'unof')->get() as $spart) {
        self::WebGLPart($spart, $parts, $without_folder);
      }  
    }
    else {
      foreach ($part->subparts as $spart) {
        self::WebGLPart($spart, $parts, $without_folder);
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