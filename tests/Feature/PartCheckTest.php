<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\LDraw\PartCheck;

class PartCheckTest extends TestCase
{
    public static function validLineProvider(): array
    {
        return [
            ["0 Free for comment 112341904.sfsfkajf", true],
            ["1  1  0.01 -0.01 1  0.23456789 -.12341234 1  0 0 0  0 0 0  test.dat", true],
            ["2  0x2123456  1 0.01 -0.01  1 0.23456789 -.12341234", true],
            ["3  12  1 0.01 -0.01  1 0.23456789 -.12341234  1 0 0", true],
            ["4  10001  1 0.01 -0.01  1 0.23456789 -.12341234  1 0 0  0 0 0", true],
            ["5  1  1 0.01 -0.01  1 0.23456789 -.12341234  1 0 0  0 0 0", true],
            ["", true],
            ["0", true],
            ["1    0.01 -0.01 1  0.23456789 -.12341234 1  0 0 0  0 0 0  test.dat", false],
            ["2  1  1 0.01 -0.01  1e10 0.23456789 -.12341234", false],
            ["3  1.2  1 0.01 -0.01  1 0.23456789 -.12341234  1 0 0", false],
            ["4  1  1 a -0.01  1 0.23456789 -.12341234  1 0 0  0 0 0", false],
            ["6  1  1 0.01 -0.01  1 0.23456789 -.12341234  1 0 0  0 0 0", false],
        ];
    }

    #[DataProvider('validLineProvider')]
    public function test_validLine(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::validLine($input));
    }

    public static function checkDescriptionProvider(): array
    {
        return [
            ["0 Test", true],
            ["0 Test Description", true],
            ["0 Test Description\n", true],
            ["0 Test Description\r\n", true],
            ["0 Test Description\r\nghklafdjkadgklj", true],
            ["0 Test Description\r\n2 0 0 0 0 1 1 1 0 0 0", true],
            ["0 Test Description\r\n0 Name: 12345.dat", true],
            ["0 Test Description\r\n0 Name: 12345.dat\r\n", true],
            ["0 Tile 1 x 8 with Chinese \"长城\" (Great Wall) Pattern\n0 Name: 12345.dat\n", true],
            ["Test", false],
            ["0\n0 Name: 12345.dat", false],
            ["Test\n0 Name: 12345.dat", false],
            ["0", false],
            ["", false],
         ];
    }

    #[DataProvider('checkDescriptionProvider')]
    public function test_checkDescription(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkDescription($input));
    }    

    public static function checkLibraryApprovedDescriptionProvider(): array
    {
        return [
            ["0 This Is A Test Decription", true],
            ["0 Some Chars are ෴ Approved ", true],
            ["0 Some Chars are \xE2\x80\xA9 not Approved ", false],
            ["2 0 1 1 1 2 2 2", false],
            ["0 ", false],
            ["", false],
         ];
    }
    
    #[DataProvider('checkLibraryApprovedDescriptionProvider')]
    public function test_checkLibraryApprovedDescription(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkLibraryApprovedDescription($input));
    }    

    public static function checkNameProvider(): array
    {
        return [
            ["0 Name: 123.dat", true],
            ["0 Name: s\\123.dat", true],
            ["0 Name: 48\\123.dat", true],
            ["0 Name: 123.dat\n", true],
            ["0 Name: 123.dat\r\n", true],
            ["0 Description\r\n0 Name: 123.dat", true],
            ["0 Description\n0 Name: 123.dat", true],
            ["0 Description\n0 Name: 123.dat\n", true],
            ["0 Description\n0 Name: 123.dat\n0 Author: Test Author", true],
            ["0 Description\n0 Name:\n0 Author: Test Author", false],
            ["0 Description\nName: 123.dat\n", false],
            ["0 Description\nName:\n", false],
            ["0", false],
            ["", false],
         ];
    }
    
    #[DataProvider('checkNameProvider')]
    public function test_checkName(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkName($input));
    }    

    public static function checkLibraryApprovedNameProvider(): array
    {
        return [
            ["0 Name: test.dat", true],
            ["0 Name: test.png", true],
            ["0 Name: s\\1001.dat", true],
            ["0 Nae: 1001.dat", false],
            ["0 Name: s/1001.dat", false],
            ["0 Name: 2002 1001.dat", false],
            ["0 Name: !!.dat", false],
            ["0 Name: \r\n.dat", false],
            ["0 Name:", false],
            ["0 ", false],
            ["", false],
         ];
    }
    
    #[DataProvider('checkLibraryApprovedNameProvider')]
    public function test_checkLibraryApprovedName(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkLibraryApprovedName($input));
    }    
    
    public static function checkNameAndPartTypeProvider(): array
    {
        return [
            ["0 Name: test.dat\n0 !LDRAW_ORG Unofficial_Part", true],
            ["0 Name: test.dat\n0 !LDRAW_ORG Part UPDATE 2023-01", true],
            ["0 Name: test.dat\n0 !LDRAW_ORG Unofficial_Primitive", true],
            ["0 Name: 8\\test.dat\n0 !LDRAW_ORG Unofficial_8_Primitive", true],
            ["0 Name: s\\test.dat\n0 !LDRAW_ORG Unofficial_Subpart", true],
            ["0 Nme: s\\test.dat\n0 !LDRAW_ORG Unofficial_Subpart", false],
            ["0 Name: s\\test.dat\n0 !LDRAW_OR Unofficial_Subpart", false],
            ["0 Name: s\\test.dat\n0 !LDRAW_ORG Unofficial_Subpar", false],
            ["0 Name: s\\test.dat\n0 !LDRAW_ORG Unofficial_Part", false],
            ["0 Name: 8\\test.dat\n0 !LDRAW_ORG Unofficial_Primitive", false],
         ];
    }
    
    #[DataProvider('checkNameAndPartTypeProvider')]
    public function test_checkNameAndPartType(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkNameAndPartType($input));
    }    

    public static function checkAuthorProvider(): array
    {
        return [
            ["0 Author: Test", true],
            ["0 Author: Test Test2 von Testington", true],
            ["0 Author: [Test]", true],
            ["0 Author: [Test Test2 von Testington]", false],
            ["0 Author: Test Test2 von Testington [Test]", true],
            ["0 Author: Test Test2 von Testington [Test]\n", true],
            ["0 Author: Test Test2 von Testington [Test Jr]", false],
            ["0 Test\n0 Name: 123.dat\n0 Author: Test Test2 von Testington [Test]", true],
            ["0 Test\r\n0 Name: 123.dat\r\n0 Author: Test Test2 von Testington [Test]", true],
            ["0 Test\r\n0 Name: 123.dat\r\n0 Author: Test Test2 von Testington [Test]\r\n", true],
            ["0 Test\n0 Name: 123.dat\n0 Author: Test Test2 von Testington [Test]\n0 !LDRAW_ORG Test", true],
            ["0 Author:", false],
            ["Test", false],
            ["0", false],
            ["", false],
         ];
    }
    
    #[DataProvider('checkAuthorProvider')]
    public function test_checkAuthor(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkAuthor($input));
    }    

    public static function checkAuthorInUsersProvider(): array
    {
        return [
            ["0 Author: James Jessiman", true],
            ["0 Author: James Jessiman [DaOGLDraw]", true],
            ["0 Author: [PTadmin]", true],
            ["0 Author: Chris Dee [cwdee]", true],
            ["0 Author: Chris Dee [cdee]", true],
            ["0 Author: Chris De [cwdee]", true],
            ["0 Author: Ole Kirk Christiansen [DaOGLego]", false],
            ["0 ", false],
            ["", false],
         ];
    }
    
    #[DataProvider('checkAuthorInUsersProvider')]
    public function test_checkAuthorInUsers(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkAuthorInUsers($input));
    }    

    public static function checkPartTypeProvider(): array
    {
        return [
            ["0 !LDRAW_ORG Unofficial_Part", true],
            ["0 !LDRAW_ORG Unofficial_Part Flexible_Section", true],
            ["0 !LDRAW_ORG Part UPDATE 2022-01", true],
            ["0 !LDRAW_ORG Part Alias UPDATE 2022-01", true],
            ["0 !LDRAW_ORG Part Alias ORIGINAL", true],
            ["0 Test\n0 !LDRAW_ORG Part UPDATE 2022-01", true],
            ["0 Test\n0 !LDRAW_ORG Part UPDATE 2022-01\n", true],
            ["0 Test\r\n0 !LDRAW_ORG Part UPDATE 2022-01\r\n", true],
            ["0 Test\r\n0 !LDRAW_ORG Part UPDATE 2022-01\r\n0 Test", true],
            ["0 !LDRAW_ORG", false],
            ["0 !LDRAW_ORG Test", false],
            ["0 !LDRAW_ORG Unofficial_Test", false],
            ["0 !LDRAW_ORG Part UPDATE aaaa-bb", false],
            ["0 !LDRAW_ORG Part Test UPDATE 2022-01", false],
            ["0 !LDRAW_ORG Unofficial_PART RELEASE 2023-01", false],
            ["0 !LDRAW_ORG Unofficial_PART UPDATE 2023", false],
            ["Test", false],
            ["0", false],
            ["", false],
          ];
    }
    
    #[DataProvider('checkPartTypeProvider')]
    public function test_checkPartTypeUsers(string $input, bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkPartType($input));
    }    

    public static function checkLicenseProvider(): array
    {
        return [
            ["0 !LICENSE abcde", true],
            ["0 !LICENSE Licensed under CC BY 4.0 : see CAreadme.txt", true],
            ["0 !LICENSE", false],
            ["0", false],
            ["", false],
            ["0 Test\n0 !LICENSE abcde", true],
            ["0 Test\r\n0 !LICENSE abcde", true],
            ["0 Test\n0 !LICENSE abcde\n", true],
        ];
    }

    #[DataProvider('checkLicenseProvider')]
    public function test_checkLicense(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkLicense($input));
    }    

    public static function checkLibraryApprovedLicenseProvider(): array
    {
        return [
            ["0 !LICENSE abcde", false],
            ["0 !LICENSE Licensed under CC BY 4.0 : see CAreadme.txt", true],
            ["0 !LICENSE Not redistributable : see NonCAreadme.txt", false],
        ];
    }

    #[DataProvider('checkLibraryApprovedLicenseProvider')]
    public function test_checkLibraryApprovedLicense(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkLibraryApprovedLicense($input));
    }    

    public static function checkLibraryBFCCertifyProvider(): array
    {
        return [
            ["0 BFC CERTIFY CCW", true],
            ["0 BFC CERTIFY CW", false],
            ["0 BFC NOCERTIFY", false],
            ["0 BFC", false],
        ];
    }

    #[DataProvider('checkLibraryBFCCertifyProvider')]
    public function test_checkLibraryBFCCertify(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkLibraryBFCCertify($input));
    }    

    public static function checkCategoryProvider(): array
    {
        return [
            ["0 Test", false],
            ["0 Brick\n0 !CATEGORY Test2", false],
            ["0 |Test", false],
            ["0 Brick", true],
            ["0 Test\n0 !CATEGORY Brick", true],
            ["0 |Brick", true],
            ["0 Test\n0 !CATEGORY", false],
            ["Test", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('checkCategoryProvider')]
    public function test_checkCategory(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, PartCheck::checkCategory($input));
    }    

}
