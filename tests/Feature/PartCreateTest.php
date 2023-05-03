<?php

namespace Tests\Feature;

use Illuminate\Http\Testing\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Storage;
use Tests\TestCase;

class PartCreateTest extends TestCase
{
    public function test_user_and_part_id(): void
    {
        $user = \App\Models\User::firstWhere('name', 'OrionP');

        $post_vals = [
            'part_type_id' => 'a',
            'user_id' => 'a',
            'replace' => false,
            'partfix' => false,
            'partfile' => null,
        ];

        // Check part_type_id
        $response = $this->actingAs($user)->post('tracker/submit', $post_vals);
        $response->assertInvalid(['part_type_id', 'user_id', 'partfile']); 
    }

    public function test_file_extension(): void
    {
        $file = UploadedFile::fake()->create('test.mpd', 'text/plain');
        $user = \App\Models\User::firstWhere('name', 'OrionP');

        $post_vals = [
            'part_type_id' => 1,
            'user_id' => 1,
            'replace' => false,
            'partfix' => false,
            'partfile' => [$file],
        ];

        // Check part_type_id
        $response = $this->actingAs($user)->post('tracker/submit', $post_vals);
        $response->assertInvalid(['partfile.0' => 'The file extension is invalid (mpd)']); 
    }

    public function test_file_mime(): void
    {
        $user = \App\Models\User::firstWhere('name', 'OrionP');

        $part_type_id = 1;
        $user_id = $user->id;
        $replace = false;
        $partfix = false;
        $partfile = [UploadedFile::fake()->create('test.dat', 'image/png')];

        // Check part_type_id
        $response = $this->actingAs($user)->post('tracker/submit', compact('part_type_id', 'user_id', 'replace', 'partfix', 'partfile'));
        $response->assertInvalid(['partfile.0' => 'The file format is invalid (dat)']); 

        $partfile = [UploadedFile::fake()->create('test.dat', 'image/jpeg')];
        $response = $this->actingAs($user)->post('tracker/submit', compact('part_type_id', 'user_id', 'replace', 'partfix', 'partfile'));
        $response->assertInvalid(['partfile.0' => 'The file format is invalid (dat)']); 
    }

    public static function fileProvider(): array
    {
        $values = str_replace("\r",'', file_get_contents(dirname(dirname(__DIR__)) . "/storage/app/testfiles/test_file.dat"));
        $values = explode("###\n", $values);
        $results = [];
        for($i = 0; $i < count($values); $i += 3) {
            $results[] = [trim($values[$i]), $values[$i+1], trim($values[$i+2])];
        }
        return $results;
    }
    
    #[DataProvider('fileProvider')]
    public function test_file(string $filename, string $text, string $expected): void
    {
        $user = \App\Models\User::firstWhere('name', 'OrionP');
        $part_type_id = 1;
        $user_id = -1;
        $replace = false;
        $partfix = false;

        Storage::fake('local')->put($filename, $text);
        $partfile = [new UploadedFile(Storage::disk('local')->path($filename), $filename, 'text/plain', null, true)];
        $response = $this->actingAs($user)->post('tracker/submit', compact('part_type_id', 'user_id', 'replace', 'partfix', 'partfile'));
        $response->assertInvalid(['partfile.0' => $expected]); 
    }

    public static function headerProvider(): array
    {
        $values = str_replace("\r",'', file_get_contents(dirname(dirname(__DIR__)) . "/storage/app/testfiles/test_header.dat"));
        $values = explode("###\n", $values);
        $results = [];
        for($i = 0; $i < count($values); $i += 3) {
            $results[] = [trim($values[$i]), $values[$i+1], trim($values[$i+2])];
        }
        return $results;
    }
    
    #[DataProvider('headerProvider')]
    public function test_header(string $filename, string $text, string $expected): void
    {
        $user = \App\Models\User::firstWhere('name', 'OrionP');
        $part_type_id = 1;
        $user_id = -1;
        $replace = false;
        $partfix = false;
        Storage::fake('local')->put($filename, $text);
        $partfile = [new UploadedFile(Storage::disk('local')->path($filename), $filename, 'text/plain', null, true)];
        $response = $this->actingAs($user)->post('tracker/submit', compact('part_type_id', 'user_id', 'replace', 'partfix', 'partfile'));
        $response->assertInvalid(['partfile.0' => $expected]); 
    }

    public static function validfileProvider(): array
    {
        $values = str_replace("\r",'', file_get_contents(dirname(dirname(__DIR__)) . "/storage/app/testfiles/test_valid_file.dat"));
        $values = explode("###\n", $values);
        $results = [];
        for($i = 0; $i < count($values); $i += 2) {
            $results[] = [trim($values[$i]), $values[$i+1]];
        }
        return $results;
    }
    
    #[DataProvider('validfileProvider')]
    public function test_valid_file(string $filename, string $text): void
    {
        $user = \App\Models\User::firstWhere('name', 'OrionP');
        $part_type_id = 1;
        $user_id = -1;
        $replace = false;
        $partfix = false;
        Storage::fake('local')->put($filename, $text);
        $partfile = [new UploadedFile(Storage::disk('local')->path($filename), $filename, 'text/plain', null, true)];
        $response = $this->actingAs($user)->post('tracker/submit', compact('part_type_id', 'user_id', 'replace', 'partfix', 'partfile'));
        $response->assertValid(['partfile.0']); 
    }

}
