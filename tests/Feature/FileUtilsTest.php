<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use App\LDraw\FileUtils;

class FileUtilsTest extends TestCase
{
    public static function unix2dosProvider(): array
    {
        return [
            'unix style' => ["a\nb\nc\n", "a\r\nb\r\nc\r\n"],
            'mac style' => ["a\rb\rc\r", "a\r\nb\r\nc\r\n"],
            'windows style' => ["a\r\nb\r\nc\r\n", "a\r\nb\r\nc\r\n"],
            'mix of styles' => ["a\nb\rc\r\n", "a\r\nb\r\nc\r\n"],
        ];
    }
    /**
     * Test unix2dos
     */
    #[DataProvider('unix2dosProvider')]
    public function test_unix2dos(string $input, string $expected): void
    {
        $this->assertSame($expected, FileUtils::unix2dos($input));
    }    

    public static function dos2unixProvider(): array
    {
        return [
            'unix style' => ["a\nb\nc\n", "a\nb\nc\n"],
            'mac style' => ["a\rb\rc\r", "a\nb\nc\n"],
            'windows style' => ["a\r\nb\r\nc\r\n", "a\nb\nc\n"],
            'mix of styles' => ["a\nb\rc\r\n", "a\nb\nc\n"],
        ];
    }
    /**
     * Test dos2unix
     */
    #[DataProvider('dos2unixProvider')]
    public function test_dos2unix(string $input, string $expected): void
    {
        $this->assertSame($expected, FileUtils::dos2unix($input));
    }

    public static function releaseProvider(): array
    {
        return [
            ["0 !LDRAW_ORG Unofficial_Part", ['releasetype' => '', 'release' => '']],
            ["0 !LDRAW_ORG Unofficial_Part Flexible_Section", ['releasetype' => '', 'release' => '']],
            ["0 !LDRAW_ORG Part UPDATE 2022-01", ['releasetype' => 'UPDATE', 'release' => '2022-01']],
            ["0 !LDRAW_ORG Part Alias UPDATE 2022-01", ['releasetype' => 'UPDATE', 'release' => '2022-01']],
            ["0 !LDRAW_ORG Part Alias ORIGINAL", ['releasetype' => 'ORIGINAL', 'release' => 'original']],
            ["0 Test\n0 !LDRAW_ORG Part UPDATE 2022-01", ['releasetype' => 'UPDATE', 'release' => '2022-01']],
            ["0 Test\n0 !LDRAW_ORG Part UPDATE 2022-01\n", ['releasetype' => 'UPDATE', 'release' => '2022-01']],
            ["0 Test\r\n0 !LDRAW_ORG Part UPDATE 2022-01\r\n", ['releasetype' => 'UPDATE', 'release' => '2022-01']],
            ["0 Test\r\n0 !LDRAW_ORG Part UPDATE 2022-01\r\n0 Test", ['releasetype' => 'UPDATE', 'release' => '2022-01']],
            ["0 !LDRAW_ORG", false],
            ["0 !LDRAW_ORG Test", false],
            ["0 !LDRAW_ORG Unofficial_Test", false],
            ["0 !LDRAW_ORG Part Test UPDATE 2022-01", false],
            ["0 !LDRAW_ORG Part UPDATE aaaa-bb", false],
            ["0 !LDRAW_ORG Unofficial_PART RELEASE 2023-01", false],
            ["0 !LDRAW_ORG Unofficial_PART UPDATE 2023", false],
            ["Test", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('releaseProvider')]
    public function test_getRelease(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getRelease($input));
    }    

    public static function authorProvider(): array
    {
        return [
            ["0 Author: Test", ['realname' => 'Test', 'user' => '']],
            ["0 Author: Test Test2 von Testington", ['realname' => 'Test Test2 von Testington', 'user' => '']],
            ["0 Author: [Test]", ['realname' => '', 'user' => 'Test']],
            ["0 Author: [Test Test2 von Testington]", false],
            ["0 Author: Test Test2 von Testington [Test]", ['realname' => 'Test Test2 von Testington', 'user' => 'Test']],
            ["0 Author: Test Test2 von Testington [Test]\n", ['realname' => 'Test Test2 von Testington', 'user' => 'Test']],
            ["0 Author: Test Test2 von Testington [Test Jr]", false],
            ["0 Test\n0 Name: 123.dat\n0 Author: Test Test2 von Testington [Test]", ['realname' => 'Test Test2 von Testington', 'user' => 'Test']],
            ["0 Test\r\n0 Name: 123.dat\r\n0 Author: Test Test2 von Testington [Test]", ['realname' => 'Test Test2 von Testington', 'user' => 'Test']],
            ["0 Test\r\n0 Name: 123.dat\r\n0 Author: Test Test2 von Testington [Test]\r\n", ['realname' => 'Test Test2 von Testington', 'user' => 'Test']],
            ["0 Test\n0 Name: 123.dat\n0 Author: Test Test2 von Testington [Test]\n0 !LDRAW_ORG Test", ['realname' => 'Test Test2 von Testington', 'user' => 'Test']],
            ["0 Author:", false],
            ["Test", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('authorProvider')]
    public function test_getAuthor(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getAuthor($input));
    }    

    public static function partTypeProvider(): array
    {
        return [
            ["0 !LDRAW_ORG Unofficial_Part", ['unofficial' => 'Unofficial_', 'type' => 'Part', 'qual' => '']],
            ["0 !LDRAW_ORG Unofficial_Part Flexible_Section", ['unofficial' => 'Unofficial_', 'type' => 'Part', 'qual' => 'Flexible_Section']],
            ["0 !LDRAW_ORG Part UPDATE 2022-01", ['unofficial' => '', 'type' => 'Part', 'qual' => '']],
            ["0 !LDRAW_ORG Part Alias UPDATE 2022-01", ['unofficial' => '', 'type' => 'Part', 'qual' => 'Alias']],
            ["0 !LDRAW_ORG Part Alias ORIGINAL", ['unofficial' => '', 'type' => 'Part', 'qual' => 'Alias']],
            ["0 Test\n0 !LDRAW_ORG Part UPDATE 2022-01", ['unofficial' => '', 'type' => 'Part', 'qual' => '']],
            ["0 Test\n0 !LDRAW_ORG Part UPDATE 2022-01\n", ['unofficial' => '', 'type' => 'Part', 'qual' => '']],
            ["0 Test\r\n0 !LDRAW_ORG Part UPDATE 2022-01\r\n", ['unofficial' => '', 'type' => 'Part', 'qual' => '']],
            ["0 Test\r\n0 !LDRAW_ORG Part UPDATE 2022-01\r\n0 Test", ['unofficial' => '', 'type' => 'Part', 'qual' => '']],
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
    /**
     * Test getDescription
     */
    #[DataProvider('partTypeProvider')]
    public function test_getPartType(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getPartType($input));
    }    

    public static function nameProvider(): array
    {
        return [
            ["0 Name: 123.dat", "123.dat"],
            ["0 Name: s\\123.dat", "s\\123.dat"],
            ["0 Name: 48\\123.dat", "48\\123.dat"],
            ["0 Name: 123.dat\n", "123.dat"],
            ["0 Name: 123.dat\r\n", "123.dat"],
            ["0 Description\r\n0 Name: 123.dat", "123.dat"],
            ["0 Description\n0 Name: 123.dat", "123.dat"],
            ["0 Description\n0 Name: 123.dat\n", "123.dat"],
            ["0 Description\n0 Name: 123.dat\n0 Author: Test Author", "123.dat"],
            ["0 Description\n0 Name:\n0 Author: Test Author", false],
            ["0 Description\nName: 123.dat\n", false],
            ["0 Description\nName:\n", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('nameProvider')]
    public function test_getName(string $input, string|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getName($input));
    }    

    public static function licenseProvider(): array
    {
        return [
            ["0 !LICENSE abcde", "abcde"],
            ["0 !LICENSE Licensed under CC BY 4.0 : see CAreadme.txt", "Licensed under CC BY 4.0 : see CAreadme.txt"],
            ["0 !LICENSE", false],
            ["0", false],
            ["", false],
            ["0 Test\n0 !LICENSE abcde", "abcde"],
            ["0 Test\r\n0 !LICENSE abcde", "abcde"],
            ["0 Test\n0 !LICENSE abcde\n", "abcde"],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('licenseProvider')]
    public function test_getLicense(string $input, string|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getLicense($input));
    }    

    public static function descriptionProvider(): array
    {
        return [
            ["0 Test", "Test"],
            ["0 Test Description", "Test Description"],
            ["0 Test Description\n", "Test Description"],
            ["0 Test Description\r\n", "Test Description"],
            ["0 Test Description\r\nghklafdjkadgklj", "Test Description"],
            ["0 Test Description\r\n2 0 0 0 0 1 1 1 0 0 0", "Test Description"],
            ["0 Test Description\r\n0 Name: 12345.dat", "Test Description"],
            ["0 Test Description\r\n0 Name: 12345.dat\r\n", "Test Description"],
            ["0 Tile 1 x 8 with Chinese \"长城\" (Great Wall) Pattern\n0 Name: 12345.dat\n", "Tile 1 x 8 with Chinese \"长城\" (Great Wall) Pattern"],
            ["Test", false],
            ["0\n0 Name: 12345.dat", false],
            ["Test\n0 Name: 12345.dat", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('descriptionProvider')]
    public function test_getDescription(string $input, string|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getDescription($input));
    }    

    public static function cmdLineProvider(): array
    {
        return [
            ["0 !CMDLINE abcde", "abcde"],
            ["0 !CMDLINE -c39", '-c39'],
            ["0 !CMDLINE", false],
            ["0", false],
            ["", false],
            ["0 Test\n0 !CMDLINE abcde", "abcde"],
            ["0 Test\r\n0 !CMDLINE abcde", "abcde"],
            ["0 Test\n0 !CMDLINE abcde\n", "abcde"],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('cmdLineProvider')]
    public function test_getCmdLine(string $input, string|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getCmdLine($input));
    }    

    public static function categoryProvider(): array
    {
        return [
            ["0 Test", ['category' => 'Test', 'meta' => false]],
            ["0 Test\n0 !CATEGORY Test2", ['category' => 'Test2', 'meta' => true]],
            ["0 |Test", ['category' => 'Test', 'meta' => false]],
            ["0 | Test\n0 !CATEGORY Test2", ['category' => 'Test2', 'meta' => true]],
            ["0 Test\n0 !CATEGORY", false],
            ["Test", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('categoryProvider')]
    public function test_getCategory(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getCategory($input));
    }    

    public static function historyProvider(): array
    {
        return [
            ["0 !HISTORY 2023-03-03 [Test] Comment", [['date' => '2023-03-03', 'user' => 'Test', 'comment' => 'Comment']]],
            ["0 Test\n0 !HISTORY 2023-03-03 [Test] Comment\n0 BFC CCW", [['date' => '2023-03-03', 'user' => 'Test', 'comment' => 'Comment']]],
            ["0   !HISTORY\t2023-03-03    [Test] \t Comment", [['date' => '2023-03-03', 'user' => 'Test', 'comment' => 'Comment']]],
            ["0 !HISTORY 2023-03-03 {Test} Comment", [['date' => '2023-03-03', 'user' => 'Test', 'comment' => 'Comment']]],
            ["0 !HISTORY 2023-03-03 {Test User} Comment", [['date' => '2023-03-03', 'user' => 'Test User', 'comment' => 'Comment']]],
            ["0 !HISTORY 2023-03-03 [Test] Comment\n0 !HISTORY 2023-03-04 [Test2] Comment2", [
                ['date' => '2023-03-03', 'user' => 'Test', 'comment' => 'Comment'],
                ['date' => '2023-03-04', 'user' => 'Test2', 'comment' => 'Comment2']
            ]],
            ["0 !HISTORY 2023-0303 [Test] Comment", false],
            ["0 !HISTORY 2023-03-03 Test] Comment", false],
            ["0 !HISTORY 2023-0303 [Test] ", false],
            ["0 !HISTORY 2023-03 [Test] Comment\n0 !HISTORY 2023-03-04 [Test2] Comment2", [
                ['date' => '2023-03-04', 'user' => 'Test2', 'comment' => 'Comment2']
            ]],
            ["0 !HISTORY 2023-03-03 [Test] Comment\n0 !HISTORY 2023-03-04 (Test2) Comment2", [
                ['date' => '2023-03-03', 'user' => 'Test', 'comment' => 'Comment'],
            ]],
            ["0 !HISTORY\n0 !HISTORY", false],
            ["0 !HISTORY", false],
            ["!HISTORY", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('historyProvider')]
    public function test_getHistory(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getHistory($input));
    }    

    public static function helpProvider(): array
    {
        return [
            ["0 !HELP Comment", ['Comment']],
            ["0 !HELP Comment\n0 !HELP Comment2", ['Comment', 'Comment2']],
            ["0 !HELP \n0 !HELP Comment2", ['Comment2']],
            ["0 !HELP Comment\n0 !HELP", ['Comment']],
            ["0 !HELP\n!HELP", false],
            ["0 !HELP", false],
            ["!HELP", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('helpProvider')]
    public function test_getHelp(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getHelp($input));
    }    

    public static function keywordsProvider(): array
    {
        return [
            ["0 !KEYWORDS Comment", ['Comment']],
            ["0 !KEYWORDS Comment, Comment2", ['Comment', 'Comment2']],
            ["0 !CATEGORY Test\n0 !KEYWORDS Comment, Comment2\n", ['Comment', 'Comment2']],
            ["0 !KEYWORDS Comment With A Space, Comment2", ['Comment With A Space', 'Comment2']],
            ["0 !KEYWORDS Comment, Comment", ['Comment']],
            ["0 !KEYWORDS Comment, Comment2\n0 !KEYWORDS Comment, Comment2", ['Comment', 'Comment2']],
            ["0 !KEYWORDS Comment, Comment2\n0 !KEYWORDS Comment3, Comment4", ['Comment', 'Comment2', 'Comment3', 'Comment4']],
            ["0 !KEYWORDS\n!KEYWORDS", false],
            ["0 !KEYWORDS", false],
            ["!KEYWORDS", false],
            ["0", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('keywordsProvider')]
    public function test_getKeywords(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getKeywords($input));
    }    

    public static function subpartsProvider(): array
    {
        return [
            ["0 Test\n1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.dat", ['subparts' => ['test.dat'], 'textures' => []]],
            ["1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.dat", ['subparts' => ['test.dat'], 'textures' => []]],
            ["0 !TEXMAP START PLANAR 1 2 3 1 2 3 1 2 3 test.png", ['subparts' => [], 'textures' => ['test.png']]],
            ["0 !TEXMAP START CYLINDRICAL 1 2 3 1 2 3 1 2 3 4 test.png", ['subparts' => [], 'textures' => ['test.png']]],
            ["0 !TEXMAP START SPHERICAL 1 2 3 1 2 3 1 2 3 4 5 test.png", ['subparts' => [], 'textures' => ['test.png']]],
            ["0 !TEXMAP START SPHERICAL 1 2 3 1 2 3 1 2 3 test.png GLOSSMAP test2.png", ['subparts' => [], 'textures' => ['test.png', 'test2.png']]],
            [
                "1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.dat\n1 0 0 0 0 1 0 0 0 1 0 0 0 1 test2.dat\n0 !TEXMAP START PLANAR 1 2 3 1 2 3 1 2 3 test.png GLOSSMAP test2.png", 
                ['subparts' => ['test.dat', 'test2.dat'], 'textures' => ['test.png', 'test2.png']]
            ],
            ["1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.dat\n1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.dat", ['subparts' => ['test.dat'], 'textures' => []]],
            ["1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.dat\n1 0 0 0 0 1 0 0 0 1 0 0 0 1 test2.dat", ['subparts' => ['test.dat', 'test2.dat'], 'textures' => []]],
            ["1 0 0 0 0 1 0 0 0 1 0 0 0 1 test.ldr\n1 0 0 0 0 1 0 0 0 1 0 0 0 1 test2.dat", ['subparts' => ['test.ldr', 'test2.dat'], 'textures' => []]],
            ["0 Test\n 0 0 0 1 0 0 0 1 0 0 0 1 test.dat", false],
            ["0 !KEYWORDS", false],
            ["test.dat", false],
            ["", false],
        ];
    }
    /**
     * Test getDescription
     */
    #[DataProvider('subpartsProvider')]
    public function test_getSubparts(string $input, array|bool $expected): void
    {
        $this->assertSame($expected, FileUtils::getSubparts($input));
    }    

    public function test_formatText(): void
    {
        $this->assertSame(Storage::get('testfiles/cleanheader2.dat'), FileUtils::formatText(Storage::get('testfiles/cleanheader1.dat')));
    }    

    public function test_getHeader(): void
    {
        $this->assertSame(Storage::get('testfiles/getheader1.dat'), FileUtils::getHeader(Storage::get('testfiles/cleanheader2.dat')));
    }    

    public function test_setHeader(): void
    {
        $this->assertSame(Storage::get('testfiles/cleanheader2.dat'), FileUtils::setHeader(Storage::get('testfiles/cleanheader2.dat'), Storage::get('testfiles/getheader1.dat')));
    }    

    public function test_cleanFileText(): void
    {
        $this->assertSame(Storage::get('testfiles/cleanheader3.dat'), FileUtils::cleanFileText(Storage::get('testfiles/cleanheader1.dat'), true));
    }    

}
