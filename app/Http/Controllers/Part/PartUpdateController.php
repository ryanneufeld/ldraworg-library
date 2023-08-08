<?php

namespace App\Http\Controllers\Part;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\PartRelease;

class PartUpdateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('output') && in_array(strtolower($request->query('output')), ['xml', 'tab'])) {
            return $this->ptreleases($request);
        }
        if ($request->has('latest')) {
            $releases = PartRelease::current();
        }
        else {
            $releases = PartRelease::latest()->get();
        }
        return view('tracker.release.index', ['releases' => $releases , 'latest' => $request->has('latest')]);
    }

    public function view(PartRelease $release, Request $request)
    {
        return view('tracker.release.view', ['release' => $release]);
    }

  public function ptreleases(Request $request) {
    $output = strtolower($request->query('output'));
    $releases = PartRelease::where('short', '!=', 'original')->oldest()->get();
    if ($output == 'tab') {
      $response = '';
      foreach ($releases as $release) {
        if (Storage::disk('library')->exists("updates/lcad{$release->short}.exe")) {
          $response .= "UPDATE\t{$release->name}\t" . 
            date_format($release->created_at, 'Y-m-d') . 
            "\tARJ\t" . 
            Storage::disk('library')->url("updates/lcad{$release->short}.exe") . "\t" . 
            Storage::disk('library')->size("updates/lcad{$release->short}.exe") . "\t" .
            Storage::disk('library')->checksum("updates/lcad{$release->short}.exe") . "\n";
        }
        if (Storage::disk('library')->exists("updates/lcad{$release->short}.zip")) {
          $response .= "UPDATE\t{$release->name}\t" . 
            date_format($release->created_at, 'Y-m-d') . 
            "\tZIP\t" . 
            Storage::disk('library')->url("updates/lcad{$release->short}.zip") . "\t" . 
            Storage::disk('library')->size("updates/lcad{$release->short}.zip") . "\t" .
            Storage::disk('library')->checksum("updates/lcad{$release->short}.zip") . "\n";
        }
      }
      if (Storage::disk('library')->exists("updates/complete.exe")) {
        $response .= "COMPLETE\t". PartRelease::current()->name ."\t" . 
          date_format(PartRelease::current()->created_at, 'Y-m-d') . 
          "\tARJ\t" . 
          Storage::disk('library')->url("updates/complete.exe") . "\t" . 
          Storage::disk('library')->size("updates/complete.exe") . "\t" .
          Storage::disk('library')->checksum("updates/complete.exe") . "\n";
      }
      if (Storage::disk('library')->exists("updates/complete.zip")) {
        $response .= "COMPLETE\t" . PartRelease::current()->name. "\t" . 
          date_format(PartRelease::current()->created_at, 'Y-m-d') . 
          "\tZIP\t" . 
          Storage::disk('library')->url("updates/complete.zip") . "\t" . 
          Storage::disk('library')->size("updates/complete.zip") . "\t" .
          Storage::disk('library')->checksum("updates/complete.zip") . "\n";
      }
      if (Storage::disk('library')->exists("updates/ldraw027.exe")) {
        $response .= "BASE\t0.27\t" . 
          date('Y-m-d', Storage::disk('library')->lastModified("updates/ldraw027.exe")) . 
          "\tARJ\t" . 
          Storage::disk('library')->url("updates/ldraw027.exe") . "\t" . 
          Storage::disk('library')->size("updates/ldraw027.exe") . "\t" .
          Storage::disk('library')->checksum("updates/ldraw027.exe") . "\n";
      }
      if (Storage::disk('library')->exists("updates/ldraw027.zip")) {
        $response .= "BASE\t0.27\t" . 
          date('Y-m-d', Storage::disk('library')->lastModified("updates/ldraw027.zip")) . 
          "\tZIP\t" . 
          Storage::disk('library')->url("updates/ldraw027.zip") . "\t" . 
          Storage::disk('library')->size("updates/ldraw027.zip") . "\t" .
          Storage::disk('library')->checksum("updates/ldraw027.zip") . "\n";
      }

      return response($response)->header('Content-Type','text/plain');
    }
    else {
      $response = '<releases>';
      foreach ($releases as $release) {
        if (Storage::disk('library')->exists("updates/lcad{$release->short}.exe")) {
          $response .= "<distribution><release_type>UPDATE</release_type><release_id>{$release->name}</release_id>" . 
            "<release_date>" . date_format($release->created_at, 'Y-m-d') . "</release_date>" .
            "<file_format>ARJ</file_format>" . 
            "<url>" . Storage::disk('library')->url("updates/lcad{$release->short}.exe") . "</url>\t" . 
            "<size>" . Storage::disk('library')->size("updates/lcad{$release->short}.exe") . "</size>\t" .
            "<md5_fingerprint>" . Storage::disk('library')->checksum("updates/lcad{$release->short}.exe") . "</md5_fingerprint></distribution>\n";
        }
        if (Storage::disk('library')->exists("updates/lcad{$release->short}.zip")) {
          $response .= "<distribution><release_type>UPDATE</release_type><release_id>{$release->name}</release_id>" . 
            "<release_date>" . date_format($release->created_at, 'Y-m-d') . "</release_date>" .
            "<file_format>ZIP</file_format>" . 
            "<url>" . Storage::disk('library')->url("updates/lcad{$release->short}.zip") . "</url>\t" . 
            "<size>" . Storage::disk('library')->size("updates/lcad{$release->short}.zip") . "</size>\t" .
            "<md5_fingerprint>" . Storage::disk('library')->checksum("updates/lcad{$release->short}.zip") . "</md5_fingerprint></distribution>\n";
        }
      }
      if (Storage::disk('library')->exists("updates/complete.exe")) {
        $response .= "<distribution><release_type>COMPLETE</release_type><release_id>". PartRelease::current()->name . "</release_id>" . 
        "<release_date>" . date_format(PartRelease::current()->created_at, 'Y-m-d') . "</release_date>" .
        "<file_format>ARJ</file_format>" . 
        "<url>" . Storage::disk('library')->url("updates/complete.exe") . "</url>\t" . 
        "<size>" . Storage::disk('library')->size("updates/complete.exe") . "</size>\t" .
        "<md5_fingerprint>" . Storage::disk('library')->checksum("updates/complete.exe") . "</md5_fingerprint></distribution>\n";
      }
      if (Storage::disk('library')->exists("updates/complete.zip")) {
        $response .= "<distribution><release_type>COMPLETE</release_type><release_id>". PartRelease::current()->name . "</release_id>" . 
        "<release_date>" . date_format(PartRelease::current()->created_at, 'Y-m-d') . "</release_date>" .
        "<file_format>ZIP</file_format>" . 
        "<url>" . Storage::disk('library')->url("updates/complete.zip") . "</url>\t" . 
        "<size>" . Storage::disk('library')->size("updates/complete.zip") . "</size>\t" .
        "<md5_fingerprint>" . Storage::disk('library')->checksum("updates/complete.zip") . "</md5_fingerprint></distribution>\n";
      }
      if (Storage::disk('library')->exists("updates/ldraw027.exe")) {
        $response .= "<distribution><release_type>BASE</release_type><release_id>0.27</release_id>" . 
        "<release_date>" . date('Y-m-d', Storage::disk('library')->lastModified("updates/ldraw027.exe")) . "</release_date>" .
        "<file_format>ARJ</file_format>" . 
        "<url>" . Storage::disk('library')->url("updates/ldraw027.exe") . "</url>\t" . 
        "<size>" . Storage::disk('library')->size("updates/ldraw027.exe") . "</size>\t" .
        "<md5_fingerprint>" . Storage::disk('library')->checksum("updates/ldraw027.exe") . "</md5_fingerprint></distribution>\n";
      }
      if (Storage::disk('library')->exists("updates/ldraw027.zip")) {
        $response .= "<distribution><release_type>BASE</release_type><release_id>0.27</release_id>" . 
        "<release_date>" . date('Y-m-d', Storage::disk('library')->lastModified("updates/ldraw027.zip")) . "</release_date>" .
        "<file_format>ZIP</file_format>" . 
        "<url>" . Storage::disk('library')->url("updates/ldraw027.zip") . "</url>\t" . 
        "<size>" . Storage::disk('library')->size("updates/ldraw027.zip") . "</size>\t" .
        "<md5_fingerprint>" . Storage::disk('library')->checksum("updates/ldraw027.zip") . "</md5_fingerprint></distribution>\n";
      }
      $response .= "</releases>";
      return response($response)->header('Content-Type','application/xml');    
    }    
  }

}
