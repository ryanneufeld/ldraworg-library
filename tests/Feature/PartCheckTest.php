<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\LDraw\PartCheck;

class PartCheckTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_part_check()
    {
      $names = [
        ['0 Name: test.dat', true],
        ['0 Name: s\\test.dat', true],
        ['0   Name:  s\\test.dat   ', true],
        ['0 Nme: s\\test.dat', false],
        ['0 Name: ', false],
        ['0 Name: s\\ test.dat', true],
        ["0 Minifig\r\n0 Name: test.dat\r\n0 !LDRAW_ORG", true],
      ];
      foreach ($names as $name) {
        if ($name[1]) {
         $this->assertTrue(PartCheck::checkName($name[0]), $name[0]);
        }
        else {
         $this->assertFalse(PartCheck::checkName($name[0]), $name[0]);
        }
      }

      $this->assertTrue(PartCheck::checkLibraryApprovedName('0 Name: 1234p_-aw2wed.dat'));
      $this->assertTrue(PartCheck::checkLibraryApprovedName("0 Minifig\r\n0 Name: test.dat\r\n0 !LDRAW_ORG"));
      $this->assertFalse(PartCheck::checkLibraryApprovedName('0 Name: 1234$.dat'));
      $this->assertFalse(PartCheck::checkLibraryApprovedName('0 Name: 1234Ö.dat'));
      $this->assertFalse(PartCheck::checkLibraryApprovedName('0 Name: 1234Ö.pdf'));

      $this->assertTrue(PartCheck::checkAuthor('0 Author: Ole Kirk Christiansen'));
      $this->assertTrue(PartCheck::checkAuthor('0 Author: Ole Kirk Christiansen [LegGodt]'));
      $this->assertTrue(PartCheck::checkAuthor('0 Author: Öle Kirk Christiansen [LegGodt]'));
      $this->assertFalse(PartCheck::checkAuthor('0 Author: Ole Kirk Christiansen [LegGödt]'));
      $this->assertTrue(PartCheck::checkAuthor('  0  Author:  Ole Kirk Christiansen  [LegGodt]   '));
      $this->assertTrue(PartCheck::checkAuthor('0 Author: [LegGodt]'));
      $this->assertFalse(PartCheck::checkAuthor("0 Minifig\r\n0 Author: \r\n0 !LDRAW_ORG"));
      $this->assertFalse(PartCheck::checkAuthor('0 Athor: Ole Kirk Christiansen [LegGodt]'));
      $this->assertFalse(PartCheck::checkAuthor('0 Author: Ole Kirk Christiansen [LegGodt] [DaOGLego]'));
      $this->assertFalse(PartCheck::checkAuthor('0 Author: [LegGodt] Ole Kirk Christiansen'));

      $this->assertTrue(PartCheck::checkLicense('0 !LICENSE Blah Blah Balh'));
      $this->assertFalse(PartCheck::checkLicense('0 !LICENCE Blah Blah Blah'));
      $this->assertFalse(PartCheck::checkLicense('0 !LICENSE '));

      $this->assertTrue(PartCheck::checkLibraryApprovedLicense('0 !LICENSE Licensed under CC BY 4.0 : see CAreadme.txt'));
      $this->assertTrue(PartCheck::checkLibraryApprovedLicense('0 !LICENSE Licensed under CC BY 2.0 and CC BY 4.0 : see CAreadme.txt'));
      $this->assertTrue(PartCheck::checkLibraryApprovedLicense('0 !LICENSE Redistributable under CCAL version 2.0 : see CAreadme.txt'));
      $this->assertFalse(PartCheck::checkLibraryApprovedLicense('0 !LICENSE Blah Blah Blah'));
      $this->assertFalse(PartCheck::checkLibraryApprovedLicense('0 !LICENSE Not redistributable : see NonCAreadme.txt'));
    }
}
