<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;

use App\Models\Part;

class LibrarySearch {
  
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