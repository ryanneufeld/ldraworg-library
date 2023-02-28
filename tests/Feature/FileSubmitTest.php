<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use App\Models\User;

class FileSubmitTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_part_submit_form()
    {

      Storage::fake('parts');
      $user = User::factory()->create();
      $user->assignRole('Part Author');

/*
      //Form data missing
      $response = $this->actingAs($user,'web')->withSession(['banned' => false])->post('/tracker/submit');
      $response->assertInvalid(['part_type_id', 'partfile', 'user_id']);

      $response = $this->actingAs($user,'web')->withSession(['banned' => false])->post('/tracker/submit', ['part_type_id' => '0', 'user_id' => '0'] );
      $response->assertInvalid(['part_type_id', 'user_id']);

      $response = $this->actingAs($user,'web')->withSession(['banned' => false])->post('/tracker/submit', ['part_type_id' => '1', 'user_id' => "$user->id"]);
      $response->assertValid(['part_type_id', 'user_id']);


      $badfile = UploadedFile::fake()->create('test.pdf',100,'application/pdf');
      $baddatfile = UploadedFile::fake()->create('test.dat',100,'application/pdf');
      $badpngfile = UploadedFile::fake()->create('test.png',100,'application/pdf');
      $goodpngfile = UploadedFile::fake()->image('test.png');
      $formdata = ['part_type_id' => '1', 'user_id' => "$user->id"];

      //Test bad file types
      $formdata['partfile'] = [$badfile, $baddatfile, $badpngfile];
      $response = $this->actingAs($user,'web')->withSession(['banned' => false])->post('/tracker/submit', $formdata);
      $response->assertInvalid(['partfile.0','partfile.1','partfile.2']);

      //Test Good Image file
      $formdata['partfile'] = [$goodpngfile];
      $response = $this->actingAs($user,'web')->withSession(['banned' => false])->post('/tracker/submit', $formdata);
      $response->assertValid(['partfile.0']);

      $testfiletext = Storage::disk('local')->get('test/good.dat');

      //Good Part File
      $datfile = UploadedFile::fake()->createWithContent('test.dat', $testfiletext);
      $datfile->mimeType('text/plain');
      $formdata['partfile'] = $datfile;
      $response = $this->actingAs($user,'web')->withSession(['banned' => false])->post('/tracker/submit', $formdata);
      $response->assertValid(['partfile.0']);
      
      //Invalid tests
      $invalidtests = [
        'missingdesc' => 
          [
            ['linenum' => '0', 'line' => '']
          ];
      ];

*/      
    }
}
