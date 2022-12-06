<x-layout.main>
  <x-slot name="title">File Detail {{ $part->filename }}</x-slot>
  <div class="ui segment main-content">
    <div class="ui large header"><span class="official">Official File <span id="filename">{{ $part->filename }}</span></span></div>
    <div>
      @isset ($part->unofficial_part)
        <a href="{{ route('tracker.show', $part->unofficial_part->id) }}">View unofficial version of part</a>
      @endisset
    </div>      
    <div class="ui right floated center aligned compact official detail-img segment">
      @if ($part->isTexmap())
      <a class="ui part-img image" href="{{asset('library/official/' . $part->filename)}}">
        <img src="{{asset('asset('library/official/' . $part->filename))}}" alt='part image' title="{{ $part->description }}" >
      </a>
      @else
      <a class="ui part-img image" href="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '.png')}}">
        <img src="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '.png')}}" alt='part image' title="{{ $part->description }}" >
      </a>
      @endif  
    </div>
    <div class="ui medium header">File Header:</div>
    <pre class="part-header">
      <code>
{{ $part->header() }}
      </code>
    </pre>
    <div class="ui clearing basic segment"></div>
    <x-part.table title="Official parent parts" unofficial=0 :parts="$oparents" />
    <x-part.table title="Official subparts" unofficial=0 :parts="$osubparts" />
    <x-event.list title="File events" :events="$part->events" /> 
    <x-part.attribution :part="$part" />
  </div>
  {{--  <x-3d-modal />--}}
    {{--  <x-slot name="additional_js"><x-3d-scripts /></x-slot>--}}
</x-layout.main>