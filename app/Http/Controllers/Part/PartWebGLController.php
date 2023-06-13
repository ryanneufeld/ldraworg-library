<?php

namespace App\Http\Controllers\Part;

use App\Models\Part;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class PartWebGLController extends Controller
{
    public function __invoke(Part $part) {
        $parts = new Collection;
        $part->dependencies($parts, $part->isUnofficial());
        $webgl = [];
        $parts->each(function(Part $p) use (&$webgl) {
            if ($p->isTexmap()) {
                $pn = str_replace(["parts/textures/","p/textures/"], '', $p->filename);
                $webgl[$pn] = 'data:image/png;base64,' .  base64_encode($p->get());
            }
            else {
                $pn = str_replace(["parts/","p/"], '', $p->filename);
                $webgl[$pn] = 'data:text/plain;base64,' .  base64_encode($p->get());
            }
        });
        return $webgl;      
    }
}
