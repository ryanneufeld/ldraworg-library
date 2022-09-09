<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Part;

class File extends Model
{
    public function part()
    {
        return $this->belongsTo(Part::class);
    }
    
    public function getExtensionAttribute() {
      return pathinfo($this->path, PATHINFO_EXTENSION); 
    }
    
    public function exists() {
      return Storage::disk($this->disk)->exists($this->path);
    }
    
    public function getPartFile() {
      if ($this->extension == 'dat' && $this->exists()) {
        $filestring = Storage::disk($this->disk)->get($this->path); 
        return mb_convert_encoding($filestring, 'UTF-8',  mb_detect_encoding($filestring, 'UTF-8, ISO-8859-1', true));
      }
      else {
        return false;
      }
    }
    
}
