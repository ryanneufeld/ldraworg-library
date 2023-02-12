<x-layout.main ldbi="1">
  <x-slot name="title">File Detail {{ $part->filename }}</x-slot>
  <x-menu.part-detail :part="$part" />
  @if(session('status'))
  <div class="ui message success">
    <div class="header">{{session('status')}}</div>
  </div>
  @endif
  <div class="ui large header"><span class="unofficial">Unofficial File <span id="filename">{{ $part->filename }}</span></span></div>
  <div>
    @isset ($part->official_part_id)
      <a href="{{ route('official.show', $part->official_part_id) }}">View official version of part</a>
    @endisset
  </div>      
  <div class="ui right floated center aligned compact unofficial detail-img segment">
    @if ($part->isTexmap())
    <a class="ui part-img image" href="{{asset('library/unofficial/' . $part->filename)}}">
      <img src="{{asset('library/unofficial/' . $part->filename)}}" alt='part image' title="{{ $part->description }}" >
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
{{ $part->header }}
    </code>
  </pre>
  <div class="ui medium header">Status:</div>
  <x-part.status :vote="$part->vote_summary" status_text="1" />
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
        <td @class([
          'green' => $vote->vote_type_code == 'C',
          'red' => $vote->vote_type_code == 'H',
          'olive' => $vote->vote_type_code == 'A' || $vote->vote_type_code == 'T',
        ])>{{ $vote->type->name }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  None
  @endif
  <div class="ui clearing basic segment"></div>
  <x-part.table title="Required unofficial subparts" unofficial=1 :parts="$part->subparts()->withoutGlobalScope('missing')->unofficial()->get()" />
  <x-part.table title="Unofficial parent parts" unofficial=1 :parts="$part->parents()->withoutGlobalScope('missing')->unofficial()->get()" />
  <x-event.list title="File events" :events="$part->events" /> 
  <x-menu.part-detail :part="$part" />
  <x-part.attribution :copyuser="$part->user" :editusers="$part->editHistoryUsers()" />
  <x-part.3dmodal id="{{$part->id}}" />
</x-layout.main>