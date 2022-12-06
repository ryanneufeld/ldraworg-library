<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;

class PartsLibrary
{
  public static $default_texture_data = "0 TEXMAP Image <FILENAME>\r\n0 Author: [PTadmin]\r\n0 !LDRAW_ORG Unofficial_Texture\r\n";

  public static $known_author_aliases = [
    'unknown' => 'CA User',
    'LEGO Universe Team' => 'The LEGO Universe Team',
    'simlego' => 'Tore_Eriksson',
    'Valemar' => 'rhsexton',
  ];

  public static function officialParts($fresh = false) {
    if ($fresh) Cache::forget('official-parts-list');
    return Cache::remember('official-parts-list', 3600, function () {
      return Part::with(['type', 'officialPart'])->where('unofficial', false);
    });
  }

  public static function unofficialParts($fresh = false) {
    if ($fresh) Cache::forget('unofficial-parts-list');
    return Cache::remember('unofficial-parts-list', 3600, function () {
      return Part::with(['type', 'officialPart'])->where('unofficial', true)->orderBy('filename')->get();
    });
  }

  public static function unofficialStatusSummary($fresh = false) {
    if ($fresh) Cache::forget('unofficial-status-summary');
    return Cache::remember('unofficial-status-summary', 3600, function () {
      return Part::where('unofficial', true)->get()->pluck('vote_sort')->countBy()->sortKeys()->all();
    });
  }    
}
