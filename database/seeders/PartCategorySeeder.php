<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartCategory;

class PartCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $categories = [
        'Animal','Antenna','Arch','Arm','Bar','Baseplate','Belville','Boat','Bracket','Brick','Car','Clikits','Cockpit','Cone','Constraction',
        'Constraction Accessory','Container','Conveyor','Crane','Cylinder','Dish','Door', 'Duplo', 'Electric','Exhaust','Fence','Figure','Figure Accessory','Flag',
        'Forklift','Freestyle','Garage','Glass','Grab','Hinge','Homemaker','Hose','Ladder','Lever','Magnet','Minifig','Minifig Accessory','Minifig Footwear',
        'Minifig Headwear','Minifig Hipwear','Minifig Neckwear','Monorail','Obsolete','Panel','Plane','Plant','Plate','Platform','Propeller','Rack','Roadsign',
        'Rock','Scala','Screw','Sheet Cardboard','Sheet Fabric','Sheet Plastic','Slope','Sphere','Staircase','Sticker','Support','Tail','Tap','Technic','Tile',
        'Tipper','Tractor','Trailer','Train','Turntable','Tyre','Vehicle','Wedge','Wheel','Winch','Window','Windscreen','Wing','Znap'
      ];
      foreach ($categories as $category) {
        PartCategory::create(['category' => $category]);
      }
    }
}
