<div>
    <x-slot:title>
        File Detail {{ $part->filename }}
    </x-slot>
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
{{--      <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/ldbi.css') }}"> --}}
    @endpush
    
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
            @isset ($part->unofficial_part_id)
                <a class="ui labeled icon button" href="{{ route('tracker.show', $part->unofficial_part_id) }}"><i class="ui copy outline icon"></i>View unofficial version of part</a>
            @endisset
            @isset ($part->official_part_id)
                <a class="ui labeled icon button" href="{{ route('official.show', $part->official_part_id) }}"><i class="ui copy outline icon"></i>View official version of part</a>
            @endisset
            @if ($part->isUnofficial() && Auth::check())
                <button wire:click="toggleTracked" @class(['ui', 'yellow' => Auth::user()->notification_parts->contains($part->id), 'labeled icon button'])>
                    <i class="bell icon"></i>
                    {{Auth::user()->notification_parts->contains($part->id) ? 'Tracking' : 'Track'}}
                </button>
                @can('part.flag.delete')
                    <button wire:click="toggleDeleteFlag" @class(['ui', 'red' => $part->delete_flag, 'labeled icon button'])>
                        <i class="flag icon"></i>
                        {{$part->delete_flag ? 'Flagged' : 'Flag'}} for Deletion
                    </button>
                @else
                    @if($part->delete_flag)
                        <div class="ui red labeled icon button">
                            <i class="flag icon"></i>
                            Flagged for Deletion
                        </div>
                    @endif       
                @endcan    
                @can('part.flag.manual-hold')
                    <button wire:click="toggleManualHold" @class(['ui', 'red' => $part->manual_hold_flag, 'labeled icon button'])>
                        <i class="flag icon"></i>
                        {{$part->manual_hold_flag ? 'On' : 'Place on'}} Administrative Hold
                    </button>
                @endcan    
            @endif
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
        <pre class="part-header"><code>{{ $part->header }}</code></pre>
        @if($part->isUnofficial())
            <div class="ui medium header">Status:</div>
            <x-part.status :$part show-status /><br>
            <x-part.part-check-message :$part />
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
            <livewire:part.table title="Unofficial parent parts" :parts="$part->parents()->unofficial()->get()" />
            <livewire:part.table title="Unofficial subparts" :parts="$part->subparts()->unofficial()->get()" :missing="$part->missing_parts" />
            <div class="ui accordion">
                <div class="title">
                    <i class="dropdown icon"></i>
                    Official parents and subparts
                </div>
                <div class="content">
                    <livewire:part.table title="Official parent parts" :parts="$part->parents()->official()->get()" />
                    <livewire:part.table title="Official subparts" :parts="$part->subparts()->official()->get()" />
                </div>
            </div>
        @else
            <livewire:part.table title="Official parent parts" :parts="$part->parents()->official()->get()" />
            <livewire:part.table title="Official subparts" :parts="$part->subparts()->official()->get()" />
        @endif    
        @if($part->isUnofficial())
            <x-event.list title="File events" :events="$part->events" />
        @endif
        <x-menu.part-detail :part="$part" />
        <x-part.attribution :part="$part" />
        <x-part.3dmodal id="{{$part->id}}" />
    </div>
     
    @push('scripts')
        <x-layout.ldbi-scripts />
    @endpush
</div>
