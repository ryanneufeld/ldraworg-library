<?php

namespace App\LDraw;

class MetaData {
  private static $categories = [
    'Animal',
    'Antenna',
    'Arch',
    'Arm',
    'Bar',
    'Baseplate',
    'Belville',
    'Boat',
    'Bracket',
    'Brick',
    'Car',
    'Clikits',
    'Cockpit',
    'Cone',
    'Constraction',
    'Constraction Accessory',
    'Container',
    'Conveyor',
    'Crane',
    'Cylinder',
    'Dish',
    'Door',
    'Duplo',
    'Electric',
    'Exhaust',
    'Fence',
    'Figure',
    'Figure Accessory',
    'Flag',
    'Forklift',
    'Freestyle',
    'Garage',
    'Glass',
    'Grab',
    'Hinge',
    'Homemaker',
    'Hose',
    'Ladder',
    'Lever',
    'Magnet',
    'Minifig',
    'Minifig Accessory',
    'Minifig Footwear',
    'Minifig Headwear',
    'Minifig Hipwear',
    'Minifig Neckwear',
    'Monorail',
    'Obsolete',
    'Panel',
    'Plane',
    'Plant',
    'Plate',
    'Platform',
    'Propeller',
    'Rack',
    'Roadsign',
    'Rock',
    'Scala',
    'Screw',
    'Sheet Cardboard',
    'Sheet Fabric',
    'Sheet Plastic',
    'Slope',
    'Sphere',
    'Staircase',
    'Sticker',
    'Support',
    'Tail',
    'Tap',
    'Technic',
    'Tile',
    'Tipper',
    'Tractor',
    'Trailer',
    'Train',
    'Turntable',
    'Tyre',
    'Vehicle',
    'Wedge',
    'Wheel',
    'Winch',
    'Window',
    'Windscreen',
    'Wing',
    'Znap'
  ];
  private static $types = [
    'Part' => ['name' => 'Part', 'folder' => 'parts/', 'format' => 'dat'],
    'Subpart' => ['name' => 'Subpart', 'folder' => 'parts/s/', 'format' => 'dat'],
    'Primitive' => ['name' => 'Primitive', 'folder' => 'p/', 'format' => 'dat'],
    '8_Primitive' => ['name' => '8 Segment Primitive', 'folder' => 'p/48/', 'format' => 'dat'],
    '48_Primitive' => ['name' => '48 Segment Primitive', 'folder' => 'p/8/', 'format' => 'dat'],
    'Shortcut' => ['name' => 'Shortcut', 'folder' => 'parts/', 'format' => 'dat'],
    'Texmap' => ['name' => 'TEXMAP Image', 'folder' => 'parts/textures/', 'format' => 'png'],
    'Subpart_Texmap' => ['name' => 'Subpart TEXMAP Image', 'folder' => 'parts/textures/s/', 'format' => 'png'],
    'Primitive_Texmap' => ['name' => 'Primitve TEXMAP Image', 'folder' => 'p/textures/', 'format' => 'png'],
    'Helper' => ['name' => 'Helper', 'folder' => 'parts/h/', 'format' => 'dat']
  ];

  private static $qualifiers = [
    'Alias' => 'Alias',
    'Physical_Colour' => 'Physical Colour',
    'Flexible_Section' => 'Flexible Section'
  ];

  private static $library_licenses = [
    'CC_BY_2.0' => 'Licensed under CC BY 2.0 and CC BY 4.0 : see CAreadme.txt',
    'CC_BY_4.0' => 'Licensed under CC BY 4.0 : see CAreadme.txt',
    'CA' => 'Redistributable under CCAL version 2.0 : see CAreadme.txt',
    'NonCA' => 'Not redistributable : see NonCAreadme.txt'
  ];

  public static function getCategories() {
    return self::$categories;
  }
  public static function getPartTypes($keysonly = false) {
    if ($keysonly) {
      return array_keys(self::$types);
    }
    return self::$types;
  }
  public static function getPartTypeQualifiers($keysonly = false) {
    if ($keysonly) {
      return array_keys(self::$qualifiers);
    }
    return self::$qualifiers;
  }

  public static function getLibraryLicenses($valuesonly = false) {
    if ($valuesonly) {
      return array_values(self::$library_licenses);
    }
    return self::$library_licenses;
  }

}
