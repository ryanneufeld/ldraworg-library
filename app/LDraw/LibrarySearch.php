<?php

namespace App\LDraw;

use App\Models\Part;

class LibrarySearch {
  
  public static function partSearch($scope, $input, $json = false, $jsonLimit = 7) {
    
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
    $ids = ['u' => [], 'o' => []];
    foreach(['u', 'o'] as $r) {
      $p = $r == 'u' ? Part::unofficial() : Part::official();
      $p->chunk('1000', function (\Illuminate\Database\Eloquent\Collection $pts) use ($r, &$ids, $scope, $pattern) {
        foreach($pts as $part) {
          switch ($scope) {
            case 'filename':
              $term = basename($part->filename);
              break;
            case 'description':
              $term = basename($part->filename) . ' ' . $part->description;
              break;
            case 'header':
              $term = $part->header;
              break;
            case 'file':
              $term = $part->isTexmap() ? $part->header : $part->get();
              break;
          }
          if (preg_match($pattern, $term, $matches)) {
            $ids[$r][] = $part->id;
          }
        }         
      });
    }
    $uparts = Part::whereIn('id', $ids['u'])->orderBy('filename')->lazy();
    $oparts = Part::whereIn('id', $ids['o'])->orderBy('filename')->lazy();

    if ($json == true) {
      $results = ['results' => [
          'oparts' => ['name' => "Official\nParts", 'results' => []],
          'uparts' => ['name' => "Unofficial\nParts", 'results' => []],
        ]
      ];
      foreach($uparts->slice(0, $jsonLimit)->all() as $part) {
        $results['results']['uparts']['results'][] = [
          'title' => $part->name(),
          'description' => $part->description,
          'url' => route('tracker.show', $part),
        ];
      }  
      foreach($oparts->slice(0, $jsonLimit)->all() as $part) {
        $results['results']['oparts']['results'][] = [
          'title' => $part->name(),
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

  public static function suffixSearch($name, $scope) {
    if (strpos($name, '.dat') === false) $name .= '.dat';
    $basepart = Part::findOfficialByName($name, true) ?? Part::findUnofficialByName($name, true);
    if (!isset($basepart)) return ['results' => ['basepart' => null, 'parts' => null]];
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
        $op = Part::findOfficialByName($search);            
        $up = Part::findUnofficialByName($search);
        if (empty($op) && empty($up)) {
          $ps[] = null;
        }
        else {
          $ps[] = $up ?? $op;
        }                         
      }
      
      if (!empty(array_filter($ps, function ($a) { return $a !== null;}))) {
        $parts[$searchcode] = ['description' => "{$searchcode}0 - $searchcode" . $c[$char_limit] . ": $desc", 'parts' => $ps]; 
      }
    }
    return ['results' => ['basepart' => $basepart, 'parts' => $parts]];
  }  
    
}