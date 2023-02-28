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
    'Helper',
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
    'Moved',
    'Obsolete',
    'Panel',
    'Plane',
    'Plant',
    'Plate',
    'Platform',
    'Pov-RAY',
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
    'String',
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
    '8_Primitive' => ['name' => '8 Segment Primitive', 'folder' => 'p/8/', 'format' => 'dat'],
    '48_Primitive' => ['name' => '48 Segment Primitive', 'folder' => 'p/48/', 'format' => 'dat'],
    'Shortcut' => ['name' => 'Shortcut', 'folder' => 'parts/', 'format' => 'dat'],
    'Helper' => ['name' => 'Helper', 'folder' => 'parts/h/', 'format' => 'dat'],
    'Texmap' => ['name' => 'TEXMAP Image', 'folder' => 'parts/textures/', 'format' => 'png'],
    'Subpart_Texmap' => ['name' => 'Subpart TEXMAP Image', 'folder' => 'parts/textures/s/', 'format' => 'png'],
    'Primitive_Texmap' => ['name' => 'Primitive TEXMAP Image', 'folder' => 'p/textures/', 'format' => 'png'],
    '8_Primitive_Texmap' => ['name' => '8 Segment Primitive TEXMAP Image', 'folder' => 'p/textures/8/', 'format' => 'png'],
    '48_Primitive_Texmap' => ['name' => '48 Segment Primitive TEXMAP Image', 'folder' => 'p/textures/48/', 'format' => 'png'],
  ];

  private static $qualifiers = [
    'Alias' => 'Alias',
    'Physical_Colour' => 'Physical Colour',
    'Flexible_Section' => 'Flexible Section'
  ];

  private static $library_licenses = [
    'CC_BY_2' => 'Licensed under CC BY 2.0 and CC BY 4.0 : see CAreadme.txt',
    'CC_BY_4' => 'Licensed under CC BY 4.0 : see CAreadme.txt',
    'CA' => 'Redistributable under CCAL version 2.0 : see CAreadme.txt',
    'NonCA' => 'Not redistributable : see NonCAreadme.txt'
  ];
  
  private static $known_author_aliases = [
    'The LEGO Universe Team' => 'LEGO Universe Team',
    'simlego' => 'Tore_Eriksson',
    'Valemar' => 'rhsexton',
  ];

  private static $pattern_codes = [
    '0' => 'General/Miscellaneous and Town',
    '1' => 'Town, including Paradisa',
    '2' => 'Town, including Paradisa',
    '3' => 'Pirates, Soldiers, Islanders',
    '4' => 'Castle',
    '5' => 'Space',
    '6' => 'Space',
    '7' => 'Modern Town',
    '8' => 'Modern Town',
    '9' => 'Modern Town',
    'a' => 'Action (Adventurers, Aquazone, Alpha Team, Rock Raiders)',
    'b' => 'Superheroes',
    'c' => 'Control Panels, dials, gauges, keyboards, readouts, etc. or Superheroes for Minifig Parts)',
    'c0$h' => 'Collectable Minifigures from accessory packs',
    'c1$h' => 'Collectable Minifigures Series 1',
    'c2$h' => 'Collectable Minifigures Series 2',
    'c3$h' => 'Collectable Minifigures Series 3',
    'c4$h' => 'Collectable Minifigures Series 4',
    'c5$h' => 'Collectable Minifigures Series 5',
    'c6$h' => 'Collectable Minifigures Series 6',
    'c7$h' => 'Collectable Minifigures Series 7',
    'c8$h' => 'Collectable Minifigures Series 8',
    'c9$h' => 'Collectable Minifigures Series 9',
    'ca$h' => 'Collectable Minifigures Series 10',
    'cb$h' => 'Collectable Minifigures Series 11',
    'cc$h' => 'Collectable Minifigures Series 12',
    'cd$h' => 'Collectable Minifigures Series 13',
    'ce$h' => 'Collectable Minifigures Series 14',
    'cf$h' => 'Collectable Minifigures Series 15',
    'cg$h' => 'Collectable Minifigures Series 16',
    'ch$h' => 'Collectable Minifigures Series 17',
    'ci$h' => 'Collectable Minifigures Series 18',
    'cj$h' => 'Collectable Minifigures Series 19',
    'ck$h' => 'Collectable Minifigures Series 20',
    'cl$h' => 'Collectable Minifigures Series 21',
    'cm$h' => 'Collectable Minifigures Series 22',
    'cn$h' => 'Collectable Minifigures Series 23',
    'co$h' => 'Collectable Minifigures Series 24',
    'cp$h' => 'Collectable Minifigures Series 25',
    'cq$h' => 'Collectable Minifigures Series 26',
    'cr$h' => 'Collectable Minifigures Series 27',
    'cs$h' => 'Collectable Minifigures Series 28',
    'ct$h' => 'Collectable Minifigures Series 29',
    'cu$h' => 'Collectable Minifigures Series 30',
    'cv$h' => 'Collectable Minifigures Series 31',
    'cw$h' => 'Collectable Minifigures Series 32',
    'cx$h' => 'Collectable Minifigures Series 33',
    'cy$h' => 'Collectable Minifigures Series 34',
    'cz$h' => 'Collectable Minifigures Series 35',
    'd' => 'Studios',
    'd0$9' => 'Collectable Minifigures 2012 Team GB',
    'd1$g' => 'Collectable Minifigures Simpsons Series 1',
    'd2$g' => 'Collectable Minifigures The LEGO Movie',
    'd3$g' => 'Collectable Minifigures Simpsons Series 2',
    'd4$i' => 'Collectable Minifigures Disney Series 1',
    'd5$h' => 'Collectable Minifigures 2016 German Football Team',
    'd6$k' => 'Collectable The LEGO Batman Movie Series 1',
    'd7$k' => 'Collectable The LEGO Ninjago Movie',
    'd8$k' => 'Collectable The LEGO Batman Movie Series 2',
    'd9$m' => 'Collectable Minifigures Wizarding World',
    'da$k' => 'Collectable Minifigures The LEGO Movie 2',
    'db$i' => 'Collectable Minifigures Disney Series 2',
    'e' => 'Nexo Knights',
    'f' => 'Fabuland, Scala, or Castle (minifig parts)',
    'g' => 'Soccer, Basketball',
    'h' => 'Harry Potter',
    'j' => 'Indiana Jones',
    'k' => 'Cars (Disney Pixar)',
    'l' => 'Unused',
    'm' => 'Middle Earth (Lord of the Rings), Elves',
    'n' => 'Ninja',
    'o' => 'Unused',
    'p' => 'Reserved',
    'q' => 'Pharaoh\'s Quest',
    'r' => 'Star Wars',
    's' => 'Star Wars',
    't' => 'General Textual Patterns (lettering and numbers) and Trademark items (Corporate Logos, etc)',
    'u' => 'Extended textual patterns or Modern Town/City (minifig parts)',
    'v' => 'Extended textual patterns',
    'w' => 'Extended textual patterns or Western (minifig parts)',
    'x' => 'Miscellaneous Licenses (SpongeBob SquarePants, Ideas)',
    'y' => 'Racing (Racers, Tiny Turbos, Speed Champions)',
    'z' => 'Brickheadz',
  ];  
  
  public static function getCategories() {
    return self::$categories;
  }

  public static function getAuthorAliases() {
    return self::$known_author_aliases;
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

  public static function getPatternCodes() {
    return self::$pattern_codes;            
  }

}
