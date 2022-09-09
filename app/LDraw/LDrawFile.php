<?php

namespace App\LDraw;

class LDrawFile
{
  public function __construct(string $file = '') {
    if (file_exists($file)) {
//      self.loadFile($file);
    }
  }
  
  public function loadFile(string $file) {
  }
}
