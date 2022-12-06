<?php

// LDraw Config Values

return [
  'officialdir' => realpath(storage_path('app/library/official')),
  'officialimagedir' => realpath(storage_path('app/images/library/official')),
  'unofficialdir' => realpath(storage_path('app/library/unofficial')),
  'unofficialimagedir' => realpath(storage_path('app/images/library/unofficial')),
  'ldview' => realpath(resource_path('bin/ldview')),
//  'ldviewini' => realpath(resource_path('bin/ldview.ini')),
  'defaultlic' => 'CC_BY_2',
];  