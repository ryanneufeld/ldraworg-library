@props(['release' => \App\Models\PartRelease::current(), 'current' => true])
<div class="ui part-update compact segments">
  <div class="ui center aligned segment">
    @if (\Storage::disk('images')->exists('updates/' . $release->short . '.png'))
      <img class="ui right floated image" src="{{asset('images/updates/' .  $release->short . '.png')}}"/>
    @else
      <img class="ui right floated image" src="{{asset('images/updates/default.png')}}"/>
    @endif
    <h4 class="ui header">LDraw.org Parts Update {{$release->name}}<h4>
  </div>
  <div class="ui horizontal segments">
    <div class="ui segment">
      <h5 class="ui header">Release Notes</h5>
      <div class="ui list">
      </div>
    </div>
    <div class="ui segment">
       <h5 class="ui header">Download Links</h5>
       <div class="ui list">
       <div class="item">
         <div class="header">
           <a href="{{route('release.view', $release)}}">Preview Parts in Update</a>
         </div>
         <div class="description">
           (graphics-intensive page)
         </div>
       </div>
       @if ($current)
       <div class="item">
         <div class="header">
           <a href="{{asset('library/updates/LDrawParts.exe')}}">Download .exe</a>
         </div>
         <div class="description">
           Use this link to download the Windows installer for the complete library.
         </div>
       </div>
       @endif
       <div class="item">
         <div class="header">
           <a href="{{asset('library/updates/lcad' . $release->short . '.zip')}}">Download lcad{{$release->short}}.zip</a>
         </div>
         <div class="description">
           Use this link if you just want a zip version of the update.
         </div>
       </div>
       @if ($current)
       <div class="item">
         <div class="header">
           <a href="{{asset('library/updates/complete.zip')}}">Download complete.zip</a>
         </div>
         <div class="description">
           Use this link if you want a zip version of the entire parts library.
         </div>
       </div>
       @endif
    </div>
    </div>
  </div>
</div>