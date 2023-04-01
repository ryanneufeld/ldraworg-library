<x-layout.main>
  <h3 class="ui header">Pattern/Composite/Sticker Shortcut Search</h3> 
  <form class="ui form" name="summary" action="{{route('search.suffix')}}" method="get">
  <div class="field">
    <label>Search Scope:</label>
    <select class="ui compact dropdown" name="scope">
      <option value="p" @selected(!request()->has('scope') || request()->input('scope') == 'p')>Pattern Parts</option>
      <option value="c" @selected(request()->input('scope') == 'c')>Composite Parts</option>
      <option value="d" @selected(request()->input('scope') == 'd')>Stickered Shortcut</option>
    </select>
  </div>
  <div class="field">
    <label>Base Part Number:</label>
    <div class="ui action input">
      <input type="text" name="s" value="{{request()->input('s')}}" placeholder="Part Number..." />
      <button class="ui button" type="submit">Go</button>
    </div>
  </div>
  </form>
  @isset($basepart)
    <div class="ui large header">
      {{$scope}} Reference for {{basename($basepart->filename)}}
    </div>

    <div @class(['ui', 'official' => !$basepart->isUnofficial(), 'unofficial' => $basepart->isUnofficial(), 'right floated center aligned compact segment'])>
      @if($basepart->isUnofficial())
        <a class="ui image" href="{{route('tracker.show', $basepart)}}">
        <img src="{{asset('images/library/unofficial/' . substr($basepart->filename, 0, -4) . '.png')}}" title='Base part image' alt='Base part image'></a>
      @else
        <a class="ui small image" href="{{route('official.show', $basepart)}}">
        <img src="{{asset('images/library/official/' . substr($basepart->filename, 0, -4) . '.png')}}" title='Base part image' alt='Base part image'></a>
      @endif  
    </div>

    <p>
    This page is a summary of all the {{strtolower($scope)}} versions of part {{basename($basepart->filename)}}, ({{$basepart->description}})<br />
    The background colour indicates the part status :<br />
    <span class="official blank-box">&nbsp;</span> Official - included in the original LDraw package or an official LDraw parts update - follow link or click image for details<br />
    <span class="unofficial blank-box">&nbsp;</span> Unofficial - submitted to this Parts Tracker and under review - follow link or click image for details<br />
    <span class="obsolete blank-box">&nbsp;</span> Obsoleted - code not available for administrative reasons<br />
    The range assignments, shown as subheadings, are designed to group together related {{strtolower($scope)}}s for
    parts with many {{strtolower($scope)}} versions. The rigour with which these have been applied has increased as
    more {{strtolower($scope)}} parts have been authored. Code assignment in the early days of LDraw was more haphazard.
    For backward compatibility, older {{strtolower($scope)}} parts with codes that do not match the current usage have not
    been re-numbered.</p>
    <div class="ui clearing divider"></div>
    <div class="ui eight column padded doubling grid">
      @forelse($parts as $part)
        <div class="column"><x-part.suffixitem :part="$part" /></div>
      @empty
        <h4 class="ui header">No {{strtolower($scope)}}s found</h4>
      @endforelse
    </div>  
{{--    
    @forelse($results['parts'] as $code)
    <h4 class="ui header">{{$code['description']}}</h3>
    <div class="ui eight column padded doubling grid">
      @foreach($code['parts'] as $part)
      @empty($part)
      <div class="column"></div>
      @else
      <div class="column">
        <div @class(['ui',
          'obsolete' => stripos($part->description, "obsolete") !== false,      
          'official' => !$part->isUnofficial() && stripos($part->description, "obsolete") === false, 
          'unofficial' => $part->isUnofficial() && stripos($part->description, "obsolete") === false, 
          'pattern center aligned segment'])>
        @if(stripos($part->description, "obsolete") !== false)
          Obsolete file<br/><br/>
          {{basename($part->filename, '.dat')}}
        @elseif($part->isUnofficial())
          <a class="ui image" href="{{route('tracker.show', $part)}}">
            <img src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '.png')}}" title="{{$part->description}}" alt="{{$part->description}}" border="0" />
          </a><br />
          <a href="{{route('tracker.show', $part)}}">{{basename($part->filename, '.dat')}}</a><br/>
          <x-part.status :vote="$part->vote_summary" status_text="0" />
        @else
          <a class="ui image" href="{{route('official.show', $part)}}">
            <img src="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '.png')}}" title="{{$part->description}}" alt="{{$part->description}}" border="0" />
          </a><br />
          <a href="{{route('official.show', $part)}}">{{basename($part->filename, '.dat')}}</a>
        @endif  
        </div>
      </div>
      @endempty
      @endforeach
    </div>
    @empty
      <h4 class="ui header">No {{strtolower($type[request()->input('scope')])}}s found</h4>
    @endforelse
  @endisset
--}}
  @endisset    
</x-layout.main>