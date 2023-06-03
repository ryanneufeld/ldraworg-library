<x-layout.tracker>
  <x-slot:title>Pattern/Composite/Sticker Shortcut Search</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Pattern Search" />
  </x-slot>    
  <h3 class="ui header">Pattern/Composite/Sticker Shortcut Search</h3> 
  <form class="ui form" name="summary" action="{{route('search.suffix')}}" method="get">
  <div class="field">
    <label>Base Part Number:</label>
    <div class="ui action input">
      <input type="text" name="s" value="{{request()->input('s')}}" placeholder="Part Number..." />
      <button class="ui button" type="submit">Go</button>
    </div>
  </div>
  </form>
  @if((isset($composites) && $composites->count()) || (isset($stickers) && $stickers->count()) || (isset($patterns) && $patterns->count()))
    <div class="ui large header">
      Pattern/Composite/Sticker Shortcut Reference for {{$fn}}
    </div>
    @if(!empty($basepart))
    <div @class(['ui', 'official' => !$basepart->isUnofficial(), 'unofficial' => $basepart->isUnofficial(), 'right floated center aligned compact segment'])>
      @if($basepart->isUnofficial())
        <a class="ui image" href="{{route('tracker.show', $basepart)}}">
        <img src="{{asset('images/library/unofficial/' . substr($basepart->filename, 0, -4) . '.png')}}" title='Base part image' alt='Base part image'></a>
      @else
        <a class="ui small image" href="{{route('official.show', $basepart)}}">
        <img src="{{asset('images/library/official/' . substr($basepart->filename, 0, -4) . '.png')}}" title='Base part image' alt='Base part image'></a>
      @endif  
    </div>
    @endif
    <p>
    The background colour indicates the part status :<br />
    <span class="official blank-box">&nbsp;</span> Official - included in the original LDraw package or an official LDraw parts update - follow link or click image for details<br />
    <span class="unofficial blank-box">&nbsp;</span> Unofficial - submitted to this Parts Tracker and under review - follow link or click image for details<br />
    <span class="obsolete blank-box">&nbsp;</span> Obsoleted - code not available for administrative reasons<br />
    <div class="ui clearing divider"></div>
    <div class="ui top attached tabular menu suffixmenu">
      <a class="item active" data-tab="patterns">Patterns <div class="ui label">{{$patterns->count()}}</div></a>
      <a class="item" data-tab="composites">Composites <div class="ui label">{{$composites->count()}}</div></a>
      <a class="item" data-tab="sticker-shortcuts">Sticker Shortcuts <div class="ui label">{{$stickers->count()}}</div></a>
    </div>
    <div class="ui bottom attached tab segment active" data-tab="patterns">
      <div class="ui eight column padded doubling grid">
        @forelse($patterns as $part)
          <div class="column"><x-part.suffixitem :part="$part" /></div>
        @empty
          <h4 class="ui header">No patterns found</h4>
        @endforelse
      </div>
      </div>
    <div class="ui bottom attached tab segment" data-tab="composites">
      <div class="ui eight column padded doubling grid">
        @forelse($composites as $part)
          <div class="column"><x-part.suffixitem :part="$part" /></div>
        @empty
          <h4 class="ui header">No composites found</h4>
        @endforelse
      </div>
      </div>
    <div class="ui bottom attached tab segment" data-tab="sticker-shortcuts">
      <div class="ui eight column padded doubling grid">
        @forelse($stickers as $part)
          <div class="column"><x-part.suffixitem :part="$part" /></div>
        @empty
          <h4 class="ui header">No sticker shortcuts found</h4>
        @endforelse
      </div>
      </div>
  @else
  <h4 class="ui header">Part not found</h4>
  @endif    
</x-layout.tracker>
