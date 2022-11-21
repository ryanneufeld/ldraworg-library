<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use App\LDraw\FileUtils;

class FileUtilsTest extends TestCase
{
    private $testfile;

    protected function setUp() : void {
      parent::setUp();
      $this->testfile = Storage::disk('local')->get('tests/good.dat');
    }

    public function test_storage_file_text()
    {
      $goodtext = "0 Minifig Bauble\n0 Name: test.dat\n0 Author: Orion Pobursky [OrionP]\n0 !LDRAW_ORG Unofficial_Part\n0 !LICENSE Redistributable under CCAL version 2.0 : see CAreadme.txt\n\n0 !HELP Test help\n0 !HELP Test help line 2\n\n0 BFC CERTIFY CCW\n\n0 !CATEGORY Minifig Accessory\n0 !KEYWORDS Set 1001, Set 1002, Set 1003\n0 !KEYWORDS Bricklink 1001a, Rebrickable 1002a\n\n0 !CMDLINE -c9\n\n0 !HISTORY 2002-04-25 [PTadmin] Official update 2002-02\n0 !HISTORY 2004-05-18 {LEGO Digital Designer} Made BFC compliant\n\n1 16 0 0 0 1 0 0 0 1 0 0 0 1 4-4cyli.dat\n2 24 0 0 0 1 1 0\n3 16 0 0 0 1 0 0 1 1 0\n4 16 0 0 0 1 0 0 1 1 0 0 1 0\n5 24 0 0 0 0 1 0 1 1 1 -1 1 1\n";
      $this->assertEquals($goodtext, FileUtils::storageFileText($this->testfile));
    }

    public function test_get_author()
    {
      $author = ['user' => 'OrionP', 'realname' => 'Orion Pobursky'];
      $this->assertEquals($author, FileUtils::getAuthor($this->testfile));
    }
}
