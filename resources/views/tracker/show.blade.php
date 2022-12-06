<x-layout.main>
  <x-slot name="title">File Detail {{ $part->filename }}</x-slot>
  <div class="ui segment main-content">
  <x-part.unofficial-menu :part="$part" />

    <div class="ui large header"><span class="unofficial">Unofficial File <span id="filename">{{ $part->filename }}</span></span></div>
    <div>
      @isset ($part->official_part)
        <a href="{{ route('official.show', $part->official_part->id) }}">View official version of part</a>
      @endisset
    </div>      
    <div class="ui right floated center aligned compact unofficial detail-img segment">
      @if ($part->isTexmap())
      <a class="ui part-img image" href="{{asset('library/unofficial/' . $part->filename)}}">
        <img src="{{asset('asset('library/unofficial/' . $part->filename))}}" alt='part image' title="{{ $part->description }}" >
      </a>
      @else
      <a class="ui part-img image" href="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '.png')}}">
        <img src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '.png')}}" alt='part image' title="{{ $part->description }}" >
      </a>
      @endif  
    </div>
    <div class="ui medium header">File Header:</div>
    <pre class="part-header">
      <code>
{{ $part->header() }}
      </code>
    </pre>
    <div class="ui medium header">Status:</div>
    <x-part.status :part="$part" status_text="1" />
    <div class="ui medium header">Reviewers' certifications:</div>
    @if ($part->votes->count())
    <table class="ui collapsing compact celled striped small table">
      <thead>
        <tr>
          <th>User</th>
          <th>Vote</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($part->votes as $vote)
        <tr>
          <td>{{ $vote->user->name }}</td>
          <td>{{ $vote->type->name }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    None
    @endif
    <div class="ui clearing basic segment"></div>
    <x-part.table title="Required unofficial subparts" unofficial=1 :parts="$usubparts" />
    <x-part.table title="Unofficial parent parts" unofficial=1 :parts="$uparents" />
    <x-event.list title="File events" :events="$part->events" /> 
    <x-part.unofficial-menu :part="$part" />
    <x-part.attribution :part="$part" />
  </div>
  {{--  <x-3d-modal />--}}
    {{--  <x-slot name="additional_js"><x-3d-scripts /></x-slot>--}}
</x-layout.main>