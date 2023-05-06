<x-layout.tracker>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Part Detail" />
  </x-slot>    
  @php($lib = str_replace('/', '', $part->libFolder()))
  @push('meta')
  <meta name="description" content="{{$part->description}}">

  <!-- Facebook Meta Tags -->
  <meta property="og:url" content="{{Request::url()}}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="File Detail {{ $part->filename }}">
  <meta property="og:description" content="{{$part->description}}">
  <meta property="og:image" content="{{$part->isTexmap() ? route($lib . '.download', $part->filename) : asset('images/library/' . $lib . '/' . substr($part->filename, 0, -4) . '.png')}}">

  <!-- Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta property="twitter:domain" content="library.ldraw.org">
  <meta property="twitter:url" content="{{Request::url()}}">
  <meta name="twitter:title" content="File Detail {{ $part->filename }}">
  <meta name="twitter:description" content="{{$part->description}}">
  <meta name="twitter:image" content="{{$part->isTexmap() ? route($lib . '.download', $part->filename) : asset('images/library/' . $lib . '/' . substr($part->filename, 0, -4) . '.png')}}">
  @endpush
  @push('css')
  <link rel="stylesheet" type="text/css" href="/assets/css/ldbi.css">
  @endpush
  <x-slot:title>File Detail {{ $part->filename }}</x-slot>
  <x-menu.part-detail :part="$part" />
  @if(session('status'))
  <div class="ui message success">
    <div class="header">{{session('status')}}</div>
  </div>
  @endif
  <div class="ui segment main-content">
    <div class="ui large header">
      <span class="{{$lib}}">
        {{ucfirst($lib)}} File <span id="filename">{{ $part->filename }}</span>
      </span>
    </div>
    <div>
      @if ($part->isUnofficial() && Auth::check())
          <livewire:part.track-button :part="$part" />
        @can('part.flag.delete')
          <livewire:part.delete-flag-button :part="$part" />
        @else
          @if($part->delete_flag)
          <div class="ui red labeled icon button">
            <i class="flag icon"></i>
              Flagged for Deletion
          </div>
          @endif       
        @endcan    
      @endif
    </div>
    <div>
      @isset ($part->unofficial_part_id)
        <a href="{{ route('tracker.show', $part->unofficial_part_id) }}">View unofficial version of part</a>
      @endisset
      @isset ($part->official_part_id)
        <a href="{{ route('official.show', $part->official_part_id) }}">View official version of part</a>
      @endisset
    </div>      
    <div class="ui right floated center aligned compact {{$lib}} detail-img segment">
      @if ($part->isTexmap())
      <a class="ui image" href="{{route("$lib.download", $part->filename)}}">
        <img src="{{route("$lib.download", $part->filename)}}" alt='part image' title="{{ $part->description }}" >
      </a>
      @else
      <a class="ui part-img image" href="{{asset("images/library/$lib/" . substr($part->filename, 0, -4) . '.png')}}">
        <img src="{{asset("images/library/$lib/" . substr($part->filename, 0, -4) . '.png')}}" alt='part image' title="{{ $part->description }}" >
      </a>
      @endif  
    </div>
    <div class="ui medium header">File Header:</div>
    <pre class="part-header"><code>
{{ $part->header }}
</code></pre>
  @if($part->isUnofficial())
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
  @endif
    <div class="ui clearing basic segment"></div>
  @if($part->isUnofficial())
    <x-part.table title="{{ucfirst($lib)}} parent parts" :parts="$part->parents()->unofficial()->get()" />
  <x-part.table title="{{ucfirst($lib)}} subparts" :parts="$part->subparts()->unofficial()->get()" :missing="$part->missing_parts" />
  @else
  <x-part.table title="{{ucfirst($lib)}} parent parts" :parts="$part->parents()->official()->get()" />
    <x-part.table title="{{ucfirst($lib)}} subparts" :parts="$part->subparts()->official()->get()" />
  @endif    
  @if($part->isUnofficial())
    <x-event.list title="File events" :events="$part->events" />
  @endif
    <x-menu.part-detail :part="$part" />
    <x-part.attribution :copyuser="$part->user" :editusers="$part->editHistoryUsers()" />
    <x-part.3dmodal id="{{$part->id}}" />
  </div>
 
  @push('scripts')
    <x-layout.ldbi-scripts />
  @endpush

</x-layout.tracker>
