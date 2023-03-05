<?php

// LDraw Config Values

return [
  'dirs' => [
    'parts',
    'parts/s',
    'parts/textures',
    'parts/textures/s',
    'p',
    'p/48',
    'p/8',
    'p/textures/48',
    'p/textures/8',
    'parts/h',
    'parts/textures/h',
  ],
  'staging_dir' => [
    'disk' => 'local',
    'path' => 'tmp'
  ],
  'ldview' => [
    'path' => realpath(resource_path('bin/ldview')),
    'dir' => [
      'render' => [
        'disk' => 'local',
        'path' => 'render',
      ],
      'image' => [
        'official' => [
          'disk' => 'images',
          'path' => 'library/official',  
        ],
        'unofficial' => [
          'disk' => 'images',
          'path' => 'library/unofficial',  
        ]      
      ],
    ],  
    'commands' => [
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
