<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Stroage;

use \ZipArchive;

use App\Models\PartRelease;

class PartReleaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $non_xml_releases = [
          ['name' => 'unofficial', 'short' => 'unof', 'notes' => ''],
          ['name' => 'original', 'short' => 'original', 'notes' => '', 'created_at' => '1996-01-01 00:00:00'],
          ['name' => '1997-15', 'short' => '9715', 'notes' => '', 'created_at' => '1997-10-29 00:00:00'],
          ['name' => '1997-16', 'short' => '9716', 'notes' => '', 'created_at' => '1997-10-29 00:00:00'],
          ['name' => '1997-17', 'short' => '9717', 'notes' => '', 'created_at' => '1997-12-05 00:00:00'],
          ['name' => '1998-01', 'short' => '9801', 'notes' => '', 'created_at' => '1998-01-12 00:00:00'],
          ['name' => '1998-02', 'short' => '9802', 'notes' => '', 'created_at' => '1998-02-12 00:00:00'],
          ['name' => '1998-03', 'short' => '9803', 'notes' => '', 'created_at' => '1998-03-23 00:00:00'],
          ['name' => '1998-04', 'short' => '9804', 'notes' => '', 'created_at' => '1998-04-11 00:00:00'],
          ['name' => '1998-05', 'short' => '9805', 'notes' => '', 'created_at' => '1998-05-21 00:00:00'],
          ['name' => '1998-06', 'short' => '9806', 'notes' => '', 'created_at' => '1998-06-20 00:00:00'],
          ['name' => '1998-07', 'short' => '9807', 'notes' => '', 'created_at' => '1998-07-15 00:00:00'],
          ['name' => '1998-08', 'short' => '9808', 'notes' => '', 'created_at' => '1998-09-15 00:00:00'],
          ['name' => '1998-09', 'short' => '9809', 'notes' => '', 'created_at' => '1998-10-15 00:00:00'],
          ['name' => '1998-10', 'short' => '9810', 'notes' => '', 'created_at' => '1998-12-15 00:00:00'],
          ['name' => '1999-01', 'short' => '9901', 'notes' => '', 'created_at' => '1999-02-01 00:00:00'],
          ['name' => '1999-02', 'short' => '9902', 'notes' => '', 'created_at' => '1999-03-26 00:00:00'],
          ['name' => '1999-03', 'short' => '9903', 'notes' => '', 'created_at' => '1999-05-17 00:00:00'],
          ['name' => '1999-04', 'short' => '9904', 'notes' => '', 'created_at' => '1999-06-00 00:00:00'],
          ['name' => '1999-05', 'short' => '9905', 'notes' => '', 'created_at' => '1999-07-05 00:00:00'],
          ['name' => '1999-06', 'short' => '9906', 'notes' => '', 'created_at' => '1999-12-31 00:00:00'],
          ['name' => '2000-01', 'short' => '0001', 'notes' => '', 'created_at' => '2000-05-07 00:00:00'],
        ];
        foreach($non_xml_releases as $release) {
          PartRelease::create($release);
        }

        $pt_updates_url = 'http://www.ldraw.org/cgi-bin/ptreleases.cgi?output=XML&type=ZIP&fields=type-release-date-url';
        $releases = simplexml_load_file($pt_updates_url);

        if ($releases !== false) {
          foreach ($releases->distribution as $update) {
            if ($update->release_type == 'UPDATE') {
              $release_short = str_replace('-','',substr($update->release_id, -5));
              
              $destination_dir = storage_path('app/library/tmp');
              $local_zip_file = basename(parse_url($update->url, PHP_URL_PATH));
              if (!file_exists($destination_dir . '/' . $local_zip_file)) copy($update->url, $destination_dir . '/' . $local_zip_file);
              
              $notes_path_CA = "ldraw/models/Note{$release_short}CA.txt";
              $notes_path_NonCA = "ldraw/models/note{$release_short}.txt";

              $zip = new ZipArchive;
              if ($zip->open($destination_dir . '/' . $local_zip_file) === true) {
                $note = $zip->getFromName($notes_path_CA, 0, ZipArchive::FL_NOCASE);
                if ($note === false) {
                  $note = $zip->getFromName($notes_path_NonCA, 0, ZipArchive::FL_NOCASE);
                  if ($note === false) $note = '';
                }
                $zip->close();
              }
              else {
                $note = '';
              }  
              $release = PartRelease::create([
                'name' => $update->release_id,
                'short' => $release_short,
                'notes' => $note,
              ]);
              $release->created_at = date_format(date_create($update->release_date), 'Y-m-d H:i:s');
              $release->save();
            }
          }
        }  
    }
}
