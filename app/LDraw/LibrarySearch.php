<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;

use App\Models\Part;

class LibrarySearch {
  
  public static function partSearch($scope, $input, $json = false, $jsonLimit = 7) {
    //Pull the terms out of the search string
    $pattern = '#([^\s"]+)|"([^"]*)"#u';
    preg_match_all($pattern, $input, $matches, PREG_SET_ORDER);

    $terms = [];
    foreach($matches as $m) {
      $terms[] = $m[count($m)-1];
    }  
    
    $uparts = new Collection;
    $oparts = new Collection;
    
    $first = true;
    foreach($terms as $term) {     
      switch($scope) {
        case 'filename':
        case 'description':
        case 'header';  
           if ($first) {
            $uparts = Part::unofficial()->where($scope, 'LIKE', "%$term%")->get();
            $oparts = Part::official()->where($scope, 'LIKE', "%$term%")->get();
            $first = false;
          }
          else {
            $uparts = $uparts->intersect(Part::unofficial()->where($scope, 'LIKE', "%$term%")->get());
            $oparts = $oparts->intersect(Part::official()->where($scope, 'LIKE', "%$term%")->get());
          }
        break;
        case 'file':
          if ($first) {
            $uparts = Part::unofficial()->where(function($q) use ($scope, $term) {
              $q->orWhere('header', 'LIKE', "%$term%")->orWhereRelation('body', 'body', 'LIKE', "%$term%");
            })->get();
            $oparts = Part::official()->where(function($q) use ($scope, $term) {
              $q->orWhere('header', 'LIKE', "%$term%")->orWhereRelation('body', 'body', 'LIKE', "%$term%");
            })->get();
            $first = false;
          }
          else {
            $uparts = $uparts->intersect(Part::unofficial()->where(function($q) use ($scope, $term) {
              $q->orWhere('header', 'LIKE', "%$term%")->orWhereRelation('body', 'body', 'LIKE', "%$term%");
            })->get());
            $oparts = $oparts->intersect(Part::official()->where(function($q) use ($scope, $term) {
              $q->orWhere('header', 'LIKE', "%$term%")->orWhereRelation('body', 'body', 'LIKE', "%$term%");
            })->get());
          }
        break;  
      }
    }
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