<?php

// LDraw Config Values

return [
  'officialdir' => realpath(storage_path('app/library/official')),
  'officialimagedir' => realpath(storage_path('app/images/library/official')),
  'unofficialdir' => realpath(storage_path('app/library/unofficial')),
  'unofficialimagedir' => realpath(storage_path('app/images/library/unofficial')),
  'ldview' => realpath(resource_path('bin/ldview')),
  'ldview_commands' => [
    'Texmaps' => '1',
    'AutoCrop' => '1',
    'BackgroundColor3' => '0xFFFFFF',
    'BFC' => '0', 
    'ConditionalHighlights' => '1',
    'FOV' => '0.1',
    'LineSmoothing' => '1',
    'MemoryUsage' => '0',
    'ProcessLDConfig' => '1',
    'SaveAlpha' => '1',
    'SaveZoomToFit' => '1', 
    'SeamWidth' => '0',
    'ShowHighlightLines' => '1',
    'SubduedLighting' => '1',
    'UseQualityStuds' => '1',
    'UseSpecular' => '0',
    'DebugLevel' => '0',
    'CheckPartTracker' => '0',
    'LightVector' => '-1,1,1', 
    'TextureStuds' => '0',
  ],
  'image' => [
    'normal' => [
      'width' => '300',
      'height' => '300',
    ],
    'thumb' => [
      'width' => '35',
      'height' => '75',
    ],
  ],
  'defaultlic' => 'CC_BY_2',
];  