<x-layout.main ldbi="1">
  <x-slot name="title">File Detail {{ $part->filename }}</x-slot>
  <div class="ui segment main-content">
    <x-menu.part-detail :part="$part" />
    <div class="ui large header"><span class="official">Official File <span id="filename">{{ $part->filename }}</span></span></div>
    <div>
      @isset ($part->unofficial_part_id)
        <a href="{{ route('tracker.show', $part->unofficial_part_id) }}">View unofficial version of part</a>
      @endisset
    </div>      
    <div class="ui right floated center aligned compact official detail-img segment">
      @if ($part->isTexmap())
      <a class="ui part-img image" href="{{asset('library/official/' . $part->filename)}}">
        <img src="{{asset('library/official/' . $part->filename)}}" alt='part image' title="{{ $part->description }}" >
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
{{ $part->header }}
      </code>
    </pre>
    <div class="ui clearing basic segment"></div>
    <x-part.table title="Official parent parts" unofficial=0 :parts="$part->parents()->whereRelation('release','short','<>','unof')->get()" />
    <x-part.table title="Official subparts" unofficial=0 :parts="$part->subparts()->whereRelation('release','short','<>','unof')->get()" />
    <x-part.attribution :copyuser="$part->user" :editusers="$part->editHistoryUsers()" />
    <x-part.3dmodal id="{{$part->id}}" />
  </div>
</x-layout.main>